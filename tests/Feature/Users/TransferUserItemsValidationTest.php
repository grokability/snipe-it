<?php

namespace Tests\Feature\Users;

use App\Models\Asset;
use App\Models\User;
use Tests\TestCase;

class TransferUserItemsValidationTest extends TestCase
{
    public function test_transfer_rejects_same_source_and_target(): void
    {
        $source = User::factory()->create();
        $asset = Asset::factory()->create([
            'assigned_to' => $source->id,
            'assigned_type' => User::class,
        ]);

        $this->actingAs($this->transferActor())
            ->from(route('users.transfer.show', $source))
            ->post(route('users.transfer.store', $source), [
                'target_user_id' => $source->id,
                'asset_ids' => [$asset->id],
                'note' => 'accidental self-target',
            ])
            ->assertRedirect(route('users.transfer.show', $source))
            ->assertSessionHasErrors('target_user_id');

        $asset->refresh();
        $this->assertSame($source->id, $asset->assigned_to);
    }

    public function test_transfer_rejects_empty_selection(): void
    {
        $source = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($this->transferActor())
            ->from(route('users.transfer.show', $source))
            ->post(route('users.transfer.store', $source), [
                'target_user_id' => $target->id,
                'note' => 'nothing selected',
            ])
            ->assertRedirect(route('users.transfer.show', $source))
            ->assertSessionHasErrors('asset_ids');
    }

    public function test_transfer_rejects_empty_note(): void
    {
        $source = User::factory()->create();
        $target = User::factory()->create();
        $asset = Asset::factory()->create([
            'assigned_to' => $source->id,
            'assigned_type' => User::class,
        ]);

        $this->actingAs($this->transferActor())
            ->from(route('users.transfer.show', $source))
            ->post(route('users.transfer.store', $source), [
                'target_user_id' => $target->id,
                'asset_ids' => [$asset->id],
                // note deliberately omitted
            ])
            ->assertRedirect(route('users.transfer.show', $source))
            ->assertSessionHasErrors('note');

        $asset->refresh();
        $this->assertSame($source->id, $asset->assigned_to);
    }

    public function test_transfer_ignores_asset_not_actually_assigned_to_source(): void
    {
        // Someone tampering with the form to pass an asset ID that
        // doesn't belong to the source user should not be able to
        // hijack the transfer flow to reassign arbitrary assets.
        $source = User::factory()->create();
        $target = User::factory()->create();
        $otherOwner = User::factory()->create();

        $sourceAsset = Asset::factory()->create([
            'assigned_to' => $source->id,
            'assigned_type' => User::class,
        ]);
        $foreignAsset = Asset::factory()->create([
            'assigned_to' => $otherOwner->id,
            'assigned_type' => User::class,
        ]);

        $this->actingAs($this->transferActor())
            ->post(route('users.transfer.store', $source), [
                'target_user_id' => $target->id,
                'asset_ids' => [$sourceAsset->id, $foreignAsset->id],
                'note' => 'attempting cross-user transfer',
            ]);

        $sourceAsset->refresh();
        $foreignAsset->refresh();

        $this->assertSame($target->id, $sourceAsset->assigned_to);
        $this->assertSame($otherOwner->id, $foreignAsset->assigned_to);
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
