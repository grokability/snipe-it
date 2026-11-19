<?php

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

test('soft deleted asset and its children are purged', function () {
    $trashed = Asset::factory()->create();
    $trashed->delete();
    $trashedLog = Actionlog::factory()->create([
        'item_type' => Asset::class,
        'item_id' => $trashed->id,
        'action_type' => 'checkout',
    ]);
    $trashedMaintenance = Maintenance::factory()->create(['asset_id' => $trashed->id]);

    // Sync-adapter identity row that would otherwise become an
    // orphan and cause a unique-constraint violation on the next
    // sync when the vendor re-reports the same host.
    DB::table('asset_external_sources')->insert([
        'asset_id' => $trashed->id,
        'source' => 'fleet',
        'external_id' => 'ext-99',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->artisan('snipeit:purge', ['--force' => 'true'])->assertExitCode(0);

    $this->assertDatabaseMissing('assets', ['id' => $trashed->id]);
    $this->assertDatabaseMissing('action_logs', ['id' => $trashedLog->id]);
    $this->assertDatabaseMissing('maintenances', ['id' => $trashedMaintenance->id]);
    $this->assertDatabaseMissing('asset_external_sources', ['asset_id' => $trashed->id]);
});

test('live asset and its children are not purged', function () {
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
});

test('action logs for a different item type at same id are not deleted', function () {
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
});

test('soft deleted license and its seats are purged', function () {
    $license = License::factory()->create(['seats' => 3]);

    // License::factory()->create() auto-generates the seats via observer;
    // confirm they exist before delete.
    $this->assertDatabaseHas('license_seats', ['license_id' => $license->id]);
    $license->delete();

    $this->artisan('snipeit:purge', ['--force' => 'true'])->assertExitCode(0);

    $this->assertDatabaseMissing('licenses', ['id' => $license->id]);
    $this->assertDatabaseMissing('license_seats', ['license_id' => $license->id]);
});

test('user action logs use target type target id not item type item id', function () {
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
});

test('soft deleted user with show in list zero is preserved', function () {
    // show_in_list=0 excludes a user from checkout-target dropdowns
    // in the UI. Purge preserves these users so they stick around
    // even when soft-deleted (matches the pre-refactor behavior).
    $nonCheckoutUser = User::factory()->create(['show_in_list' => 0]);
    $nonCheckoutUser->delete();

    $this->artisan('snipeit:purge', ['--force' => 'true'])->assertExitCode(0);

    // Row is gone-from-index (soft-deleted) but still in the table.
    $this->assertDatabaseHas('users', ['id' => $nonCheckoutUser->id]);
});

test('purge removes uploaded files for soft deleted users', function () {
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
});

test('dry run does not delete user files', function () {
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
});

test('purge removes image files for soft deleted assets', function () {
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
});

test('purge removes avatar files for soft deleted users', function () {
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
});

test('purge removes eula pdfs when action log parent is purged', function () {
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
});

test('purge removes signature files from action logs', function () {
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
});

test('purge removes audit files from action logs', function () {
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
});

test('purge matches action log files via target columns too', function () {
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
});

test('purge removes checkout acceptance signature and eula files', function () {
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
});

test('soft deleted location is purged', function () {
    $location = Location::factory()->create();
    $location->delete();

    $this->artisan('snipeit:purge', ['--force' => 'true'])->assertExitCode(0);

    $this->assertDatabaseMissing('locations', ['id' => $location->id]);
});

test('confirmation prompt declined purges nothing', function () {
    $trashed = Asset::factory()->create();
    $trashed->delete();

    $this->artisan('snipeit:purge')
        ->expectsConfirmation('Continue with the purge?', 'no')
        ->assertExitCode(0);

    // Row is still soft-deleted, not force-deleted.
    $this->assertDatabaseHas('assets', ['id' => $trashed->id]);
});

test('confirmation prompt confirmed purges records', function () {
    $trashed = Asset::factory()->create();
    $trashed->delete();

    $this->artisan('snipeit:purge')
        ->expectsConfirmation('Continue with the purge?', 'yes')
        ->assertExitCode(0);

    $this->assertDatabaseMissing('assets', ['id' => $trashed->id]);
});

test('dry run does not delete anything', function () {
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
    expect(DB::table('assets')->where('id', $trashedAsset->id)->value('deleted_at'))->not->toBeNull('Trashed asset should still exist (soft-deleted) after dry-run');
    $this->assertDatabaseHas('action_logs', ['id' => $trashedLog->id]);
});
