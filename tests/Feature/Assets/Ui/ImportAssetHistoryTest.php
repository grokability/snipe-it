<?php

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Import;
use App\Models\Location;
use App\Models\User;
use Tests\Support\Importing\AssetHistoryImportFileBuilder;
use Tests\Support\Importing\CleansUpImportFiles;

uses(CleansUpImportFiles::class);

test('legacy get history route redirects to importer', function () {
    $this->actingAs(User::factory()->create())
        ->get('/hardware/history')
        ->assertRedirect(route('imports.index'));
});

test('legacy post history endpoint is gone', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->post('/hardware/history')
        ->assertStatus(405);
});

test('process endpoint blocked in demo mode for non superadmin', function () {
    // Uploads are blocked at Api\ImportController::store, and non-
    // superadmins are also blocked from processing so they can't
    // mutate the demo DB via any leftover Import row. Superadmins
    // are allowed through so they can exercise the seeded demo
    // samples end to end (see companion test below).
    config(['app.lock_passwords' => true]);

    $actor = User::factory()->canImport()->create();
    $import = Import::factory()->assetHistory()->create(['created_by' => $actor->id]);

    $this->actingAsForApi($actor);
    $this->postJson(
        route('api.imports.importFile', ['import' => $import->id]),
        ['import-type' => 'assetHistory', 'import' => $import->id],
    )->assertStatus(422);
});

test('process endpoint allowed in demo mode for superadmin', function () {
    // Superadmins bypass the demo-mode gate on process() so the
    // seeded sample CSVs (populated by snipeit:demo-settings) can
    // actually be run against the demo DB. Without a real CSV on
    // disk the import will error out below the gate, so this test
    // just proves the gate itself lets the superadmin through
    // (any status other than 422 "feature disabled" is fine).
    config(['app.lock_passwords' => true]);

    $actor = User::factory()->canImport()->superuser()->create();
    $import = Import::factory()->assetHistory()->create(['created_by' => $actor->id]);

    $this->actingAsForApi($actor);
    $response = $this->postJson(
        route('api.imports.importFile', ['import' => $import->id]),
        ['import-type' => 'assetHistory', 'import' => $import->id],
    );

    $this->assertNotEquals(
        trans('general.feature_disabled'),
        $response->json('messages'),
        'Superadmin should not hit the demo-mode gate on process().',
    );
});

test('asset history import requires import permission', function () {
    $actor = User::factory()->create();
    $import = Import::factory()->assetHistory()->create(['created_by' => $actor->id]);

    $this->actingAsForApi($actor);
    $this->postJson(
        route('api.imports.importFile', ['import' => $import->id]),
        ['import-type' => 'assetHistory', 'import' => $import->id],
    )->assertForbidden();
});

test('asset history import creates actionlogs and assigns user', function () {
    $actor = User::factory()->canImport()->create();
    $target = User::factory()->create(['username' => 'target.user']);
    $asset = Asset::factory()->create([
        'asset_tag' => 'AHIST-1',
        'assigned_to' => null,
        'assigned_type' => null,
    ]);

    $checkoutDate = now()->subDay()->format('Y-m-d H:i:s');
    $checkinDate = now()->addDays(30)->format('Y-m-d H:i:s');

    $file = AssetHistoryImportFileBuilder::new([
        'assetTag' => $asset->asset_tag,
        'name' => $target->username,
        'email' => '',
        'checkoutDate' => $checkoutDate,
        'checkinDate' => $checkinDate,
    ]);

    $import = Import::factory()->assetHistory()->create([
        'created_by' => $actor->id,
        'file_path' => $file->saveToImportsDirectory(),
    ]);

    $this->actingAsForApi($actor);
    $this->postJson(
        route('api.imports.importFile', ['import' => $import->id]),
        [
            'import-type' => 'assetHistory',
            'import' => $import->id,
            'match_username' => true,
        ],
    )->assertOk();

    $asset->refresh();
    expect($asset->assigned_to)->toEqual($target->id);
    expect($asset->assigned_type)->toEqual(User::class);

    $this->assertDatabaseHas('action_logs', [
        'item_id' => $asset->id,
        'item_type' => Asset::class,
        'target_id' => $target->id,
        'target_type' => User::class,
        'action_type' => 'checkout',
    ]);

    $this->assertDatabaseHas('action_logs', [
        'item_id' => $asset->id,
        'item_type' => Asset::class,
        'target_id' => null,
        'action_type' => 'checkin',
    ]);
});

test('asset history import does not reassign when checkin is past', function () {
    $actor = User::factory()->canImport()->create();
    $target = User::factory()->create(['username' => 'past.user']);
    $asset = Asset::factory()->create([
        'asset_tag' => 'AHIST-2',
        'assigned_to' => null,
        'assigned_type' => null,
    ]);

    $file = AssetHistoryImportFileBuilder::new([
        'assetTag' => $asset->asset_tag,
        'name' => $target->username,
        'email' => '',
        'checkoutDate' => now()->subDays(30)->format('Y-m-d H:i:s'),
        'checkinDate' => now()->subDays(15)->format('Y-m-d H:i:s'),
    ]);

    $import = Import::factory()->assetHistory()->create([
        'created_by' => $actor->id,
        'file_path' => $file->saveToImportsDirectory(),
    ]);

    $this->actingAsForApi($actor);
    $this->postJson(
        route('api.imports.importFile', ['import' => $import->id]),
        [
            'import-type' => 'assetHistory',
            'import' => $import->id,
            'match_username' => true,
        ],
    )->assertOk();

    $asset->refresh();
    expect($asset->assigned_to)->toBeNull();
    expect($asset->assigned_type)->toBeNull();

    // Historical checkout + checkin actionlogs both got written even
    // though the asset ends up in a checked-in state.
    $this->assertDatabaseHas('action_logs', [
        'item_id' => $asset->id,
        'action_type' => 'checkout',
    ]);
    $this->assertDatabaseHas('action_logs', [
        'item_id' => $asset->id,
        'action_type' => 'checkin',
    ]);
});

test('asset history import skips unknown asset tag', function () {
    $actor = User::factory()->canImport()->create();
    User::factory()->create(['username' => 'someone']);

    $file = AssetHistoryImportFileBuilder::new([
        'assetTag' => 'DOES-NOT-EXIST',
        'name' => 'someone',
        'email' => '',
        'checkoutDate' => now()->format('Y-m-d H:i:s'),
        'checkinDate' => '',
    ]);

    $import = Import::factory()->assetHistory()->create([
        'created_by' => $actor->id,
        'file_path' => $file->saveToImportsDirectory(),
    ]);

    $this->actingAsForApi($actor);

    // Skipped rows now surface as import-errors so the wizard can
    // display the reason (Asset does not exist) instead of hiding it
    // in laravel.log. The 500 status is the shared API contract for
    // an errors-non-empty return.
    $response = $this->postJson(
        route('api.imports.importFile', ['import' => $import->id]),
        ['import-type' => 'assetHistory', 'import' => $import->id, 'match_username' => true],
    );
    $response->assertStatus(500);
    expect($response->json('status'))->toEqual('import-errors');
    expect($response->json('messages'))->toHaveKey('Asset DOES-NOT-EXIST');

    expect(Actionlog::where('note', 'like', '%history importer%')->count())->toBe(0);
});

test('asset history import skips row when user not matched', function () {
    $actor = User::factory()->canImport()->create();
    $asset = Asset::factory()->create([
        'asset_tag' => 'AHIST-3',
        'assigned_to' => null,
        'assigned_type' => null,
    ]);

    $file = AssetHistoryImportFileBuilder::new([
        'assetTag' => $asset->asset_tag,
        'name' => 'no.such.user',
        'email' => '',
        'checkoutDate' => now()->format('Y-m-d H:i:s'),
        'checkinDate' => '',
    ]);

    $import = Import::factory()->assetHistory()->create([
        'created_by' => $actor->id,
        'file_path' => $file->saveToImportsDirectory(),
    ]);

    $this->actingAsForApi($actor);
    $response = $this->postJson(
        route('api.imports.importFile', ['import' => $import->id]),
        ['import-type' => 'assetHistory', 'import' => $import->id, 'match_username' => true],
    );
    $response->assertStatus(500);
    expect($response->json('status'))->toEqual('import-errors');
    expect($response->json('messages'))->toHaveKey('Asset '.$asset->asset_tag);

    $asset->refresh();
    expect($asset->assigned_to)->toBeNull();
    $this->assertDatabaseMissing('action_logs', [
        'item_id' => $asset->id,
        'action_type' => 'checkout',
    ]);
});

test('asset history import checks out to location when target type is location', function () {
    // Ensures the resulting state is
    // the same shape Snipe-IT itself would produce via a UI checkout.
    $actor = User::factory()->canImport()->create();
    $location = Location::factory()->create(['name' => 'Fundo Chao']);
    $asset = Asset::factory()->create([
        'asset_tag' => 'AHIST-LOC-1',
        'assigned_to' => null,
        'assigned_type' => null,
    ]);

    $file = AssetHistoryImportFileBuilder::new([
        'assetTag' => $asset->asset_tag,
        'name' => $location->name,
        'email' => '',
        'checkoutDate' => now()->subDay()->format('Y-m-d H:i:s'),
        'checkinDate' => now()->addDays(30)->format('Y-m-d H:i:s'),
        'targetType' => 'location',
    ]);

    $import = Import::factory()->assetHistory()->create([
        'created_by' => $actor->id,
        'file_path' => $file->saveToImportsDirectory(),
    ]);

    $this->actingAsForApi($actor);
    $this->postJson(
        route('api.imports.importFile', ['import' => $import->id]),
        ['import-type' => 'assetHistory', 'import' => $import->id],
    )->assertOk();

    $asset->refresh();
    expect($asset->assigned_to)->toEqual($location->id);
    expect($asset->assigned_type)->toEqual(Location::class);
    expect($asset->location_id)->toEqual($location->id, 'When checked out to a Location, the asset\'s own location_id must mirror the target so the hardware list places it correctly.');

    $this->assertDatabaseHas('action_logs', [
        'item_id' => $asset->id,
        'item_type' => Asset::class,
        'target_id' => $location->id,
        'target_type' => Location::class,
        'action_type' => 'checkout',
    ]);
});

test('asset history import falls back to user lookup when target type absent', function () {
    // Backwards-compat with pre-target-type CSVs: Name should still
    // resolve as a user when the CSV has no target_type column.
    $actor = User::factory()->canImport()->create();
    $target = User::factory()->create(['username' => 'legacy.compat.user']);
    $asset = Asset::factory()->create([
        'asset_tag' => 'AHIST-LEGACY-1',
        'assigned_to' => null,
        'assigned_type' => null,
    ]);

    $file = AssetHistoryImportFileBuilder::new([
        'assetTag' => $asset->asset_tag,
        'name' => $target->username,
        'email' => '',
        'checkoutDate' => now()->format('Y-m-d H:i:s'),
        'checkinDate' => now()->addDays(7)->format('Y-m-d H:i:s'),
        // targetType intentionally omitted from the CSV.
    ]);
    $file->forget('targetType');

    $import = Import::factory()->assetHistory()->create([
        'created_by' => $actor->id,
        'file_path' => $file->saveToImportsDirectory(),
    ]);

    $this->actingAsForApi($actor);
    $this->postJson(
        route('api.imports.importFile', ['import' => $import->id]),
        [
            'import-type' => 'assetHistory',
            'import' => $import->id,
            'match_username' => true,
        ],
    )->assertOk();

    $asset->refresh();
    expect($asset->assigned_to)->toEqual($target->id);
    expect($asset->assigned_type)->toEqual(User::class);
});

test('asset history import stamps csv note onto checkout actionlog', function () {
    // Legacy systems commonly carry a per-checkout narrative that
    // needs to migrate through ("student damaged screen",
    // "shipped to remote site"). CSV Notes column value takes over
    // the actionlog note field so that context lands with the row.
    $actor = User::factory()->canImport()->create();
    $target = User::factory()->create(['username' => 'note.receiver']);
    $asset = Asset::factory()->create([
        'asset_tag' => 'AHIST-NOTE-1',
        'assigned_to' => null,
        'assigned_type' => null,
    ]);

    $file = AssetHistoryImportFileBuilder::new([
        'assetTag' => $asset->asset_tag,
        'name' => $target->username,
        'email' => '',
        'checkoutDate' => now()->subDay()->format('Y-m-d H:i:s'),
        'checkinDate' => now()->addDays(7)->format('Y-m-d H:i:s'),
        'notes' => 'Damaged screen on return',
    ]);

    $import = Import::factory()->assetHistory()->create([
        'created_by' => $actor->id,
        'file_path' => $file->saveToImportsDirectory(),
    ]);

    $this->actingAsForApi($actor);
    $this->postJson(
        route('api.imports.importFile', ['import' => $import->id]),
        ['import-type' => 'assetHistory', 'import' => $import->id, 'match_username' => true],
    )->assertOk();

    $this->assertDatabaseHas('action_logs', [
        'item_id' => $asset->id,
        'target_id' => $target->id,
        'action_type' => 'checkout',
        'note' => 'Damaged screen on return',
    ]);
});

test('asset history import keeps default note when csv note is absent', function () {
    // Regression guard for the "blank / absent falls back" branch:
    // when the CSV doesn't include a Notes column at all, the
    // actionlog still gets the auto-generated "imported by X from
    // history importer" line so the audit trail records how the
    // row landed.
    $actor = User::factory()->canImport()->create();
    $target = User::factory()->create(['username' => 'nonote.user']);
    $asset = Asset::factory()->create([
        'asset_tag' => 'AHIST-NOTE-2',
        'assigned_to' => null,
        'assigned_type' => null,
    ]);

    $file = AssetHistoryImportFileBuilder::new([
        'assetTag' => $asset->asset_tag,
        'name' => $target->username,
        'email' => '',
        'checkoutDate' => now()->format('Y-m-d H:i:s'),
        'checkinDate' => now()->addDays(7)->format('Y-m-d H:i:s'),
    ]);
    $file->forget('notes');

    $import = Import::factory()->assetHistory()->create([
        'created_by' => $actor->id,
        'file_path' => $file->saveToImportsDirectory(),
    ]);

    $this->actingAsForApi($actor);
    $this->postJson(
        route('api.imports.importFile', ['import' => $import->id]),
        ['import-type' => 'assetHistory', 'import' => $import->id, 'match_username' => true],
    )->assertOk();

    $checkoutLog = Actionlog::where('item_id', $asset->id)
        ->where('action_type', 'checkout')
        ->first();
    expect($checkoutLog)->not->toBeNull();
    $this->assertStringContainsString(
        'from history importer',
        $checkoutLog->note,
        'Actionlog note should carry the auto-generated importer marker when the CSV omitted the Notes column.'
    );
});

test('asset history import rejects row with unrecognized target type', function () {
    // Typo scenario ("Locaiton", "gerbil"). Silently degrading to
    // user would produce a "user not matched" error for every row
    // and hide the actual cause. Reject with the actual bad value
    // in the message so the operator can fix their CSV.
    $actor = User::factory()->canImport()->create();
    $asset = Asset::factory()->create([
        'asset_tag' => 'AHIST-TT-BAD',
        'assigned_to' => null,
        'assigned_type' => null,
    ]);

    $file = AssetHistoryImportFileBuilder::new([
        'assetTag' => $asset->asset_tag,
        'name' => 'any.value',
        'email' => '',
        'checkoutDate' => now()->format('Y-m-d H:i:s'),
        'checkinDate' => '',
        'targetType' => 'Locaiton',
    ]);

    $import = Import::factory()->assetHistory()->create([
        'created_by' => $actor->id,
        'file_path' => $file->saveToImportsDirectory(),
    ]);

    $this->actingAsForApi($actor);
    $response = $this->postJson(
        route('api.imports.importFile', ['import' => $import->id]),
        ['import-type' => 'assetHistory', 'import' => $import->id],
    );
    $response->assertStatus(500);
    expect($response->json('status'))->toEqual('import-errors');
    expect($response->json('messages'))->toHaveKey('Asset '.$asset->asset_tag);

    $asset->refresh();
    expect($asset->assigned_to)->toBeNull();
    $this->assertDatabaseMissing('action_logs', [
        'item_id' => $asset->id,
        'action_type' => 'checkout',
    ]);
});

test('asset history import skips row when location target not matched', function () {
    // Location-target row where the CSV location name doesn't match
    // any existing location should surface a per-row error and NOT
    // touch the asset's assignment. Same shape as the user
    // not-matched case, distinct message.
    $actor = User::factory()->canImport()->create();
    $asset = Asset::factory()->create([
        'asset_tag' => 'AHIST-LOC-MISS',
        'assigned_to' => null,
        'assigned_type' => null,
    ]);

    $file = AssetHistoryImportFileBuilder::new([
        'assetTag' => $asset->asset_tag,
        'name' => 'no.such.location',
        'email' => '',
        'checkoutDate' => now()->format('Y-m-d H:i:s'),
        'checkinDate' => '',
        'targetType' => 'location',
    ]);

    $import = Import::factory()->assetHistory()->create([
        'created_by' => $actor->id,
        'file_path' => $file->saveToImportsDirectory(),
    ]);

    $this->actingAsForApi($actor);
    $response = $this->postJson(
        route('api.imports.importFile', ['import' => $import->id]),
        ['import-type' => 'assetHistory', 'import' => $import->id],
    );
    $response->assertStatus(500);
    expect($response->json('status'))->toEqual('import-errors');
    expect($response->json('messages'))->toHaveKey('Asset '.$asset->asset_tag);

    $asset->refresh();
    expect($asset->assigned_to)->toBeNull();
    $this->assertDatabaseMissing('action_logs', [
        'item_id' => $asset->id,
        'action_type' => 'checkout',
    ]);
});
