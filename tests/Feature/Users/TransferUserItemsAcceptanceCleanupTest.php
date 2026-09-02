<?php

namespace Tests\Feature\Users;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\CheckoutAcceptance;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TransferUserItemsAcceptanceCleanupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Every channel off: clearing the acceptance request is part of
        // checking the item in, not of notifying anyone, so it must still
        // happen on an install that notifies nobody. That is the bug pinned here.
        $this->settings->disableSlackWebhook()
            ->disableAdminCC()
            ->disableAdminCCAlways();
    }

    #[Test]
    public function transferring_license_seat_clears_source_users_pending_acceptance_for_that_seat(): void
    {
        $source = User::factory()->create();
        $target = User::factory()->create();

        $license = License::factory()->create([
            'reassignable' => 1,
            'category_id' => Category::factory()->forLicenses()->requiresAcceptance()->doesNotSendCheckinEmail(),
        ]);

        $seat = LicenseSeat::factory()->assignedToUser($source)->create(['license_id' => $license->id]);

        $this->assertFalse(
            (bool) $license->checkin_email(),
            'Checkin email must be off, or this stops proving cleanup happens with notifications disabled.'
        );

        $acceptance = CheckoutAcceptance::factory()->forLicenseSeat()->pending()->create([
            'checkoutable_id' => $seat->id,
            'assigned_to_id' => $source->id,
        ]);

        $this->transfer($source, $target, ['license_seat_ids' => [$seat->id]]);

        $this->assertSame(
            $target->id,
            $seat->refresh()->assigned_to,
            'The seat was skipped, so the cleanup never ran.'
        );

        $this->assertAcceptanceWasSoftDeleted($acceptance);
    }

    #[Test]
    public function transferring_license_seat_leaves_pending_acceptance_for_another_seat_of_the_same_license_alone(): void
    {
        $source = User::factory()->create();
        $target = User::factory()->create();

        $license = License::factory()->create([
            'reassignable' => 1,
            'category_id' => Category::factory()->forLicenses()->requiresAcceptance()->doesNotSendCheckinEmail(),
        ]);

        $transferredSeat = LicenseSeat::factory()->assignedToUser($source)->create(['license_id' => $license->id]);
        $retainedSeat = LicenseSeat::factory()->assignedToUser($source)->create(['license_id' => $license->id]);

        $this->assertFalse(
            (bool) $license->checkin_email(),
            'Checkin email must be off, or this stops proving cleanup happens with notifications disabled.'
        );

        $retainedAcceptance = CheckoutAcceptance::factory()->forLicenseSeat()->pending()->create([
            'checkoutable_id' => $retainedSeat->id,
            'assigned_to_id' => $source->id,
        ]);

        $this->transfer($source, $target, ['license_seat_ids' => [$transferredSeat->id]]);

        $this->assertSame($target->id, $transferredSeat->refresh()->assigned_to, 'The seat was skipped, so the cleanup never ran.');
        $this->assertSame($source->id, $retainedSeat->refresh()->assigned_to);

        $this->assertAcceptanceSurvived($retainedAcceptance);
    }

    #[Test]
    public function transferring_accessory_clears_only_the_source_users_pending_acceptance(): void
    {
        $source = User::factory()->create();
        $target = User::factory()->create();
        $otherHolder = User::factory()->create();

        $accessory = Accessory::factory()
            ->checkedOutToUsers([$source, $otherHolder])
            ->create([
                'category_id' => Category::factory()->forAccessories()->requiresAcceptance()->doesNotSendCheckinEmail(),
                'qty' => 5,
            ]);

        $this->assertFalse(
            (bool) $accessory->checkin_email(),
            'Checkin email must be off, or this stops proving cleanup happens with notifications disabled.'
        );

        $sourceCheckout = $accessory->checkouts()->where('assigned_to', $source->id)->firstOrFail();

        $sourceAcceptance = CheckoutAcceptance::factory()->forAccessory()->pending()->create([
            'checkoutable_id' => $accessory->id,
            'assigned_to_id' => $source->id,
        ]);

        $otherHolderAcceptance = CheckoutAcceptance::factory()->forAccessory()->pending()->create([
            'checkoutable_id' => $accessory->id,
            'assigned_to_id' => $otherHolder->id,
        ]);

        $this->transfer($source, $target, ['accessory_checkout_ids' => [$sourceCheckout->id]]);

        // A skipped checkout never reaches the cleanup, so confirm the move.
        $this->assertDatabaseHas('accessories_checkout', [
            'accessory_id' => $accessory->id,
            'assigned_to' => $target->id,
            'assigned_type' => User::class,
        ]);

        $this->assertAcceptanceWasSoftDeleted($sourceAcceptance);
        $this->assertAcceptanceSurvived($otherHolderAcceptance);
    }

    #[Test]
    public function transferring_asset_clears_source_users_pending_acceptance(): void
    {
        $source = User::factory()->create();
        $target = User::factory()->create();

        $asset = Asset::factory()->assignedToUser($source)->create([
            'model_id' => AssetModel::factory()->create([
                'category_id' => Category::factory()->forAssets()->requiresAcceptance()->doesNotSendCheckinEmail(),
            ]),
        ]);

        $this->assertFalse(
            (bool) $asset->fresh()->checkin_email(),
            'Checkin email must be off, or this stops proving cleanup happens with notifications disabled.'
        );

        $acceptance = CheckoutAcceptance::factory()->pending()->create([
            'checkoutable_id' => $asset->id,
            'assigned_to_id' => $source->id,
        ]);
        $this->assertSame(Asset::class, $acceptance->checkoutable_type);

        $this->transfer($source, $target, ['asset_ids' => [$asset->id]]);

        // A skipped asset never reaches the cleanup, so confirm the move.
        $this->assertSame($target->id, $asset->refresh()->assigned_to, 'The asset was skipped, so the cleanup never ran.');
        $this->assertSame(User::class, $asset->assigned_type);

        $this->assertAcceptanceWasSoftDeleted($acceptance);
    }

    private function transfer(User $source, User $target, array $items): void
    {
        $actor = User::factory()
            ->viewUsers()
            ->checkinAccessories()
            ->checkoutAccessories()
            ->checkinAssets()
            ->checkoutAssets()
            ->checkinLicenses()
            ->checkoutLicenses()
            ->create();

        $this->actingAs($actor)
            ->post(route('users.transfer.store', $source), array_merge([
                'target_user_id' => $target->id,
                'note' => 'employee offboarding',
            ], $items))
            ->assertRedirect(route('users.show', $target));
    }

    /**
     * Assert against a specific row: acceptance is required on these categories,
     * so the transfer's re-checkout creates a fresh pending for the target.
     */
    private function assertAcceptanceWasSoftDeleted(CheckoutAcceptance $acceptance): void
    {
        $row = CheckoutAcceptance::withTrashed()->find($acceptance->id);

        $this->assertNotNull($row, 'The acceptance row was hard-deleted; the cleanup should soft-delete it.');
        $this->assertNotNull($row->deleted_at, 'Expected the transfer to soft-delete the source user\'s pending acceptance.');
    }

    private function assertAcceptanceSurvived(CheckoutAcceptance $acceptance): void
    {
        $row = CheckoutAcceptance::withTrashed()->find($acceptance->id);

        $this->assertNotNull($row, 'The acceptance row was hard-deleted; it should have been left alone entirely.');
        $this->assertNull($row->deleted_at, 'Expected the transfer to leave this unrelated pending acceptance alone.');
    }
}
