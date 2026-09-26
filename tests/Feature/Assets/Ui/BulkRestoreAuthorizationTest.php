<?php

namespace Tests\Feature\Assets\Ui;

use App\Models\Asset;
use App\Models\User;
use Tests\TestCase;

/**
 * Regression tests for a broken-access-control bug where
 * BulkAssetsController::restore() (POST /hardware/bulkrestore) used
 * $this->authorize('update', Asset::class), letting any user with
 * assets.edit undo an admin's soft-delete by sending the ID directly.
 * Every other restore code path in the codebase gated on
 * authorize('delete', ...):
 *
 *   - AssetsController::getRestore (single-asset web)
 *   - Api\AssetsController::restore (single-asset API)
 *   - BulkAssetsController::edit() 'restore' preview branch
 *
 * The fix aligns the bulk POST handler with the rest by gating on the
 * `delete` ability at both the class level and per-instance level.
 */
class BulkRestoreAuthorizationTest extends TestCase
{
    public function test_user_with_edit_but_no_delete_cannot_bulk_restore()
    {
        $asset = Asset::factory()->deleted()->create();

        $user = User::factory()->viewAssets()->editAssets()->create();

        $this->actingAs($user)
            ->post(route('hardware/bulkrestore'), [
                'ids' => [$asset->id],
            ])
            ->assertForbidden();

        $asset->refresh();
        $this->assertNotNull($asset->deleted_at, 'Asset must remain soft-deleted after a forbidden restore attempt.');
    }

    public function test_user_with_only_view_cannot_bulk_restore()
    {
        $asset = Asset::factory()->deleted()->create();

        $user = User::factory()->viewAssets()->create();

        $this->actingAs($user)
            ->post(route('hardware/bulkrestore'), [
                'ids' => [$asset->id],
            ])
            ->assertForbidden();

        $asset->refresh();
        $this->assertNotNull($asset->deleted_at);
    }

    public function test_user_with_delete_permission_can_bulk_restore()
    {
        $asset = Asset::factory()->deleted()->create();

        $user = User::factory()->viewAssets()->deleteAssets()->create();

        $this->actingAs($user)
            ->post(route('hardware/bulkrestore'), [
                'ids' => [$asset->id],
            ])
            ->assertRedirect(route('hardware.index'));

        $asset->refresh();
        $this->assertNull($asset->deleted_at, 'Asset should be restored (deleted_at cleared) after an authorized bulk restore.');
    }

    public function test_bulk_restore_skips_nonexistent_ids_instead_of_500()
    {
        // Prior code did Asset::withTrashed()->find($id)->restore() with no
        // null guard, so any invalid or forged ID crashed the whole request.
        $realAsset = Asset::factory()->deleted()->create();

        $user = User::factory()->viewAssets()->deleteAssets()->create();

        $this->actingAs($user)
            ->post(route('hardware/bulkrestore'), [
                'ids' => [$realAsset->id, 99_999_999],
            ])
            ->assertRedirect(route('hardware.index'));

        $realAsset->refresh();
        $this->assertNull($realAsset->deleted_at);
    }
}
