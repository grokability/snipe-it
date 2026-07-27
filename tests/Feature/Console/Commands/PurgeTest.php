<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\License;
use App\Models\Location;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Broad coverage for snipeit:purge, split by resource. PurgeCompaniesTest
 * already covers Company. This file also locks in the "trashed-only,
 * non-trashed untouched" contract for the resources that carry child
 * action_log rows and other FK-linked children (asset maintenances,
 * license seats), plus the per-target-type action_log filtering that a
 * refactor could easily get wrong (target_id/target_type for users vs
 * item_id/item_type for everything else).
 */
class PurgeTest extends TestCase
{
    public function test_soft_deleted_asset_and_its_children_are_purged(): void
    {
        $trashed = Asset::factory()->create();
        $trashed->delete();
        $trashedLog = Actionlog::factory()->create([
            'item_type' => Asset::class,
            'item_id' => $trashed->id,
            'action_type' => 'checkout',
        ]);
        $trashedMaintenance = Maintenance::factory()->create(['asset_id' => $trashed->id]);

        $this->artisan('snipeit:purge', ['--force' => 'true'])->assertExitCode(0);

        $this->assertDatabaseMissing('assets', ['id' => $trashed->id]);
        $this->assertDatabaseMissing('action_logs', ['id' => $trashedLog->id]);
        $this->assertDatabaseMissing('maintenances', ['id' => $trashedMaintenance->id]);
    }

    public function test_live_asset_and_its_children_are_not_purged(): void
    {
        $live = Asset::factory()->create();
        $liveLog = Actionlog::factory()->create([
            'item_type' => Asset::class,
            'item_id' => $live->id,
            'action_type' => 'checkout',
        ]);
        $liveMaintenance = Maintenance::factory()->create(['asset_id' => $live->id]);

        $this->artisan('snipeit:purge', ['--force' => 'true'])->assertExitCode(0);

        $this->assertDatabaseHas('assets', ['id' => $live->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('action_logs', ['id' => $liveLog->id]);
        $this->assertDatabaseHas('maintenances', ['id' => $liveMaintenance->id]);
    }

    public function test_action_logs_for_a_different_item_type_at_same_id_are_not_deleted(): void
    {
        // This is the polymorphic-collision guard: a trashed Asset with
        // id=7 must not cause deletion of an Accessory action_log whose
        // item_id also happens to be 7.
        $trashedAsset = Asset::factory()->create();
        $trashedAsset->delete();
        $liveAccessory = Accessory::factory()->create();

        $assetLog = Actionlog::factory()->create([
            'item_type' => Asset::class,
            'item_id' => $trashedAsset->id,
            'action_type' => 'checkout',
        ]);
        $accessoryLogWithColliding_id = Actionlog::factory()->create([
            'item_type' => Accessory::class,
            'item_id' => $trashedAsset->id, // deliberately collides
            'action_type' => 'checkout',
        ]);

        $this->artisan('snipeit:purge', ['--force' => 'true'])->assertExitCode(0);

        $this->assertDatabaseMissing('action_logs', ['id' => $assetLog->id]);
        $this->assertDatabaseHas('action_logs', ['id' => $accessoryLogWithColliding_id->id]);
        $this->assertDatabaseHas('accessories', ['id' => $liveAccessory->id]);
    }

    public function test_soft_deleted_license_and_its_seats_are_purged(): void
    {
        $license = License::factory()->create(['seats' => 3]);
        // License::factory()->create() auto-generates the seats via observer;
        // confirm they exist before delete.
        $this->assertDatabaseHas('license_seats', ['license_id' => $license->id]);
        $license->delete();

        $this->artisan('snipeit:purge', ['--force' => 'true'])->assertExitCode(0);

        $this->assertDatabaseMissing('licenses', ['id' => $license->id]);
        $this->assertDatabaseMissing('license_seats', ['license_id' => $license->id]);
    }

    public function test_user_action_logs_use_target_type_target_id_not_item_type_item_id(): void
    {
        // User::userlog() reads action_logs.target_id/target_type — the
        // rest of the polymorphic children read item_id/item_type. A
        // refactor that only handled item_id/item_type would silently
        // leave user action_logs behind.
        $user = User::factory()->create();
        $user->delete();

        $userTargetLog = Actionlog::factory()->create([
            'target_type' => User::class,
            'target_id' => $user->id,
            'action_type' => 'update',
        ]);

        $this->artisan('snipeit:purge', ['--force' => 'true'])->assertExitCode(0);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('action_logs', ['id' => $userTargetLog->id]);
    }

    public function test_soft_deleted_user_with_show_in_list_zero_is_preserved(): void
    {
        // show_in_list=0 excludes a user from checkout-target dropdowns
        // in the UI. Purge preserves these users so they stick around
        // even when soft-deleted (matches the pre-refactor behavior).
        $nonCheckoutUser = User::factory()->create(['show_in_list' => 0]);
        $nonCheckoutUser->delete();

        $this->artisan('snipeit:purge', ['--force' => 'true'])->assertExitCode(0);

        // Row is gone-from-index (soft-deleted) but still in the table.
        $this->assertDatabaseHas('users', ['id' => $nonCheckoutUser->id]);
    }

    public function test_purge_removes_uploaded_files_for_soft_deleted_users(): void
    {
        // Regression guard: an intermediate refactor of Purge dropped the
        // Storage::delete() step that removes uploaded avatars/documents
        // under private_uploads/users/ when a user is purged. Without
        // this test, that call could silently disappear again and leave
        // orphan files on disk. The old inline code lived in a per-user
        // loop; the current implementation batches via a single
        // action_logs query, and either shape needs to actually unlink
        // the file for the corresponding trashed user.
        Storage::fake();
        $user = User::factory()->create();
        $filename = "u{$user->id}-avatar.png";
        Storage::put("private_uploads/users/{$filename}", 'fake image bytes');

        Actionlog::factory()->create([
            'item_type' => User::class,
            'item_id' => $user->id,
            'action_type' => 'uploaded',
            'filename' => $filename,
        ]);

        $user->delete();
        Storage::assertExists("private_uploads/users/{$filename}");

        $this->artisan('snipeit:purge', ['--force' => 'true'])->assertExitCode(0);

        Storage::assertMissing("private_uploads/users/{$filename}");
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_dry_run_does_not_delete_user_files(): void
    {
        // Companion guard: --dry-run must be side-effect-free on disk.
        Storage::fake();
        $user = User::factory()->create();
        $filename = "u{$user->id}-avatar.png";
        Storage::put("private_uploads/users/{$filename}", 'fake image bytes');

        Actionlog::factory()->create([
            'item_type' => User::class,
            'item_id' => $user->id,
            'action_type' => 'uploaded',
            'filename' => $filename,
        ]);

        $user->delete();

        $this->artisan('snipeit:purge', ['--force' => 'true', '--dry-run' => true])->assertExitCode(0);

        Storage::assertExists("private_uploads/users/{$filename}");
    }

    public function test_purge_removes_image_files_for_soft_deleted_assets(): void
    {
        // Image column on the parent row itself. Snipe-IT stores these
        // on the public disk under `{plural-type}/{filename}`. Removing
        // them at purge time (rather than at soft-delete) means a
        // restored soft-deleted asset still has its image intact.
        Storage::fake('public');
        $asset = Asset::factory()->create(['image' => 'asset-42.jpg']);
        Storage::disk('public')->put('assets/asset-42.jpg', 'fake image bytes');

        $asset->delete();

        $this->artisan('snipeit:purge', ['--force' => 'true'])->assertExitCode(0);

        Storage::disk('public')->assertMissing('assets/asset-42.jpg');
        $this->assertDatabaseMissing('assets', ['id' => $asset->id]);
    }

    public function test_purge_removes_avatar_files_for_soft_deleted_users(): void
    {
        // Users' avatar column has its own public-disk subpath (`avatars`)
        // distinct from every other model's `image` column. Covered
        // separately because `UsersController::destroy` used to NOT
        // delete the avatar and now (correctly) still doesn't; purge
        // is the sole avatar-unlink path.
        Storage::fake('public');
        $user = User::factory()->create(['avatar' => 'user-7.jpg']);
        Storage::disk('public')->put('avatars/user-7.jpg', 'fake avatar bytes');

        $user->delete();

        $this->artisan('snipeit:purge', ['--force' => 'true'])->assertExitCode(0);

        Storage::disk('public')->assertMissing('avatars/user-7.jpg');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_purge_removes_eula_pdfs_when_action_log_parent_is_purged(): void
    {
        // Signed-EULA PDFs live under `private_uploads/eula-pdfs/`.
        // They're identified by an action_log with action_type of
        // `accepted` or `declined`, not by item_type, so the routing
        // logic in Purge has to key off action_type first.
        Storage::fake();
        $asset = Asset::factory()->create();
        $eula = "eula-{$asset->id}.pdf";
        Storage::put("private_uploads/eula-pdfs/{$eula}", 'fake pdf bytes');

        Actionlog::factory()->create([
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_type' => 'accepted',
            'filename' => $eula,
        ]);

        $asset->delete();

        $this->artisan('snipeit:purge', ['--force' => 'true'])->assertExitCode(0);

        Storage::assertMissing("private_uploads/eula-pdfs/{$eula}");
    }

    public function test_purge_removes_signature_files_from_action_logs(): void
    {
        // Signatures live under `private_uploads/signatures/` and are
        // referenced by the `accept_signature` column on action_logs
        // (not by the `filename` column and not by any specific
        // action_type). Purge must read that column separately.
        Storage::fake();
        $asset = Asset::factory()->create();
        $sig = "sig-{$asset->id}.png";
        Storage::put("private_uploads/signatures/{$sig}", 'fake signature bytes');

        Actionlog::factory()->create([
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_type' => 'checkout',
            'accept_signature' => $sig,
        ]);

        $asset->delete();

        $this->artisan('snipeit:purge', ['--force' => 'true'])->assertExitCode(0);

        Storage::assertMissing("private_uploads/signatures/{$sig}");
    }

    public function test_purge_removes_audit_files_from_action_logs(): void
    {
        // Audit files (photos, notes attached during an audit) live
        // under `private_uploads/audits/` and are keyed on
        // `action_type = 'audit'` in the action_log.
        Storage::fake();
        $asset = Asset::factory()->create();
        $auditFile = "audit-{$asset->id}.jpg";
        Storage::put("private_uploads/audits/{$auditFile}", 'fake audit photo');

        Actionlog::factory()->create([
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_type' => 'audit',
            'filename' => $auditFile,
        ]);

        $asset->delete();

        $this->artisan('snipeit:purge', ['--force' => 'true'])->assertExitCode(0);

        Storage::assertMissing("private_uploads/audits/{$auditFile}");
    }

    public function test_purge_matches_action_log_files_via_target_columns_too(): void
    {
        // Signatures/EULAs for checkouts are recorded with the
        // checkoutable item under `item_*` and the recipient user under
        // `target_*`. Purging the recipient user must clean up their
        // signature file, even though the action_log's `item_type`
        // points at Asset (not User).
        Storage::fake();
        $user = User::factory()->create();
        $sig = "user-{$user->id}-sig.png";
        Storage::put("private_uploads/signatures/{$sig}", 'fake signature bytes');

        Actionlog::factory()->create([
            'item_type' => Asset::class,
            'item_id' => Asset::factory()->create()->id,
            'target_type' => User::class,
            'target_id' => $user->id,
            'action_type' => 'checkout',
            'accept_signature' => $sig,
        ]);

        $user->delete();

        $this->artisan('snipeit:purge', ['--force' => 'true'])->assertExitCode(0);

        Storage::assertMissing("private_uploads/signatures/{$sig}");
    }

    public function test_purge_removes_checkout_acceptance_signature_and_eula_files(): void
    {
        // CheckoutAcceptance stores its signature filename and the
        // rendered EULA PDF inline on the row (not via a related
        // action_log). Both need to be unlinked when the acceptance is
        // itself purged.
        Storage::fake();
        $acceptance = CheckoutAcceptance::factory()
            ->withoutActionLog()
            ->accepted()
            ->create([
                'signature_filename' => 'acceptance-sig.png',
                'stored_eula_file' => 'acceptance-eula.pdf',
            ]);
        Storage::put('private_uploads/signatures/acceptance-sig.png', 'sig bytes');
        Storage::put('private_uploads/eula-pdfs/acceptance-eula.pdf', 'pdf bytes');

        $acceptance->delete();

        $this->artisan('snipeit:purge', ['--force' => 'true'])->assertExitCode(0);

        Storage::assertMissing('private_uploads/signatures/acceptance-sig.png');
        Storage::assertMissing('private_uploads/eula-pdfs/acceptance-eula.pdf');
    }

    public function test_soft_deleted_location_is_purged(): void
    {
        $location = Location::factory()->create();
        $location->delete();

        $this->artisan('snipeit:purge', ['--force' => 'true'])->assertExitCode(0);

        $this->assertDatabaseMissing('locations', ['id' => $location->id]);
    }

    public function test_confirmation_prompt_declined_purges_nothing(): void
    {
        $trashed = Asset::factory()->create();
        $trashed->delete();

        $this->artisan('snipeit:purge')
            ->expectsConfirmation('Continue with the purge?', 'no')
            ->assertExitCode(0);

        // Row is still soft-deleted, not force-deleted.
        $this->assertDatabaseHas('assets', ['id' => $trashed->id]);
    }

    public function test_confirmation_prompt_confirmed_purges_records(): void
    {
        $trashed = Asset::factory()->create();
        $trashed->delete();

        $this->artisan('snipeit:purge')
            ->expectsConfirmation('Continue with the purge?', 'yes')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('assets', ['id' => $trashed->id]);
    }

    public function test_dry_run_does_not_delete_anything(): void
    {
        $trashedAsset = Asset::factory()->create();
        $trashedAsset->delete();
        $trashedLog = Actionlog::factory()->create([
            'item_type' => Asset::class,
            'item_id' => $trashedAsset->id,
            'action_type' => 'checkout',
        ]);

        $this->artisan('snipeit:purge', ['--force' => 'true', '--dry-run' => true])
            ->assertExitCode(0);

        // Everything is still in place.
        $this->assertNotNull(
            DB::table('assets')->where('id', $trashedAsset->id)->value('deleted_at'),
            'Trashed asset should still exist (soft-deleted) after dry-run'
        );
        $this->assertDatabaseHas('action_logs', ['id' => $trashedLog->id]);
    }
}
