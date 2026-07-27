<?php

namespace Tests\Feature\Users;

use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Asset;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Tests\TestCase;

class TransferUserItemsBehaviorTest extends TestCase
{
    public function test_transfer_moves_asset_from_source_to_target(): void
    {
        $source = User::factory()->create();
        $target = User::factory()->create();
        $asset = Asset::factory()->create([
            'assigned_to' => $source->id,
            'assigned_type' => User::class,
        ]);

        $response = $this->actingAs($this->transferActor())
            ->post(route('users.transfer.store', $source), [
                'target_user_id' => $target->id,
                'asset_ids' => [$asset->id],
                'note' => 'employee offboarding',
            ]);

        $response->assertRedirect(route('users.show', $target));

        $asset->refresh();
        $this->assertSame($target->id, $asset->assigned_to);
        $this->assertSame(User::class, $asset->assigned_type);

        // Both a checkin and a checkout should be logged for this transfer
        // so the audit trail shows the full move rather than a single event.
        $this->assertDatabaseHas('action_logs', [
            'action_type' => 'checkin from',
            'target_id' => $source->id,
            'target_type' => User::class,
            'item_id' => $asset->id,
            'item_type' => Asset::class,
        ]);
        $this->assertDatabaseHas('action_logs', [
            'action_type' => 'checkout',
            'target_id' => $target->id,
            'target_type' => User::class,
            'item_id' => $asset->id,
            'item_type' => Asset::class,
        ]);
    }

    public function test_transfer_moves_accessory_from_source_to_target(): void
    {
        $source = User::factory()->create();
        $target = User::factory()->create();
        $accessory = Accessory::factory()->create();
        $checkout = AccessoryCheckout::create([
            'accessory_id' => $accessory->id,
            'assigned_to' => $source->id,
            'assigned_type' => User::class,
            'created_by' => User::factory()->create()->id,
        ]);

        $this->actingAs($this->transferActor())
            ->post(route('users.transfer.store', $source), [
                'target_user_id' => $target->id,
                'accessory_checkout_ids' => [$checkout->id],
                'note' => 'employee offboarding',
            ])
            ->assertRedirect(route('users.show', $target));

        $this->assertDatabaseMissing('accessories_checkout', ['id' => $checkout->id]);
        $this->assertDatabaseHas('accessories_checkout', [
            'accessory_id' => $accessory->id,
            'assigned_to' => $target->id,
            'assigned_type' => User::class,
        ]);
    }

    public function test_transfer_moves_reassignable_license_seat_from_source_to_target(): void
    {
        $source = User::factory()->create();
        $target = User::factory()->create();
        $license = License::factory()->create(['reassignable' => 1]);
        $seat = LicenseSeat::factory()->assignedToUser($source)->create(['license_id' => $license->id]);

        $this->actingAs($this->transferActor())
            ->post(route('users.transfer.store', $source), [
                'target_user_id' => $target->id,
                'license_seat_ids' => [$seat->id],
                'note' => 'transferring license seat',
            ])
            ->assertRedirect(route('users.show', $target));

        $seat->refresh();
        $this->assertSame($target->id, $seat->assigned_to);
    }

    public function test_transfer_skips_non_reassignable_license_seat(): void
    {
        $source = User::factory()->create();
        $target = User::factory()->create();
        $license = License::factory()->create(['reassignable' => 0]);
        $seat = LicenseSeat::factory()->assignedToUser($source)->create(['license_id' => $license->id]);

        // Non-reassignable licenses stay put by design. The seat must NOT
        // move even if the client somehow submits its id.
        $this->actingAs($this->transferActor())
            ->post(route('users.transfer.store', $source), [
                'target_user_id' => $target->id,
                'license_seat_ids' => [$seat->id],
                'note' => 'attempt to move non-reassignable license',
            ])
            ->assertRedirect(route('users.show', $target));

        $seat->refresh();
        $this->assertSame($source->id, $seat->assigned_to);
    }

    public function test_transfer_leaves_unselected_items_alone(): void
    {
        $source = User::factory()->create();
        $target = User::factory()->create();

        $selectedAsset = Asset::factory()->create([
            'assigned_to' => $source->id,
            'assigned_type' => User::class,
        ]);
        $unselectedAsset = Asset::factory()->create([
            'assigned_to' => $source->id,
            'assigned_type' => User::class,
        ]);

        $this->actingAs($this->transferActor())
            ->post(route('users.transfer.store', $source), [
                'target_user_id' => $target->id,
                'asset_ids' => [$selectedAsset->id],
                'note' => 'transferring one item',
            ]);

        $selectedAsset->refresh();
        $unselectedAsset->refresh();

        $this->assertSame($target->id, $selectedAsset->assigned_to);
        $this->assertSame($source->id, $unselectedAsset->assigned_to);
    }

    private function transferActor(): User
    {
        return User::factory()
            ->viewUsers()
            ->checkinAssets()
            ->checkoutAssets()
            ->create();
    }
}
