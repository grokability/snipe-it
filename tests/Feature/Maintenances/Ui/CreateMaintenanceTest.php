<?php

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\MaintenanceType;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('page requires permission', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('maintenances.create'))
        ->assertForbidden();
});

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('maintenances.create'))
        ->assertOk();
});

test('can create maintenance', function () {
    Storage::fake('public');
    Storage::fake('local');
    $actor = User::factory()->superuser()->create();
    $asset = Asset::factory()->create();
    $supplier = Supplier::factory()->create();
    $type = MaintenanceType::factory()->create();

    $this->actingAs($actor)
        ->post(route('maintenances.store'), [
            'name' => 'Test Maintenance',
            'selected_assets' => [$asset->id],
            'supplier_id' => $supplier->id,
            'maintenance_type_id' => $type->id,
            'start_date' => '2021-01-01 00:00:00',
            'completion_date' => '2021-01-10 00:00:00',
            'is_warranty' => '1',
            'cost' => '100.00',
            'image' => UploadedFile::fake()->image('test_image.png'),
            'file' => [UploadedFile::fake()->create('maintenance.pdf', 64, 'application/pdf')],
            'notes' => 'A note',
            'url' => 'https://snipeitapp.com',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('maintenances.index'));

    // Since we rename the file in the ImageUploadRequest, we have to fetch the record from the database
    $maintenance = Maintenance::where('name', 'Test Maintenance')->first();

    // Assert file was stored...
    Storage::disk('public')->assertExists(app('maintenances_path').$maintenance->image);

    $uploadedLog = Actionlog::query()
        ->where('action_type', 'uploaded')
        ->where('item_type', Maintenance::class)
        ->where('item_id', $maintenance->id)
        ->latest('id')
        ->first();

    expect($uploadedLog)->not->toBeNull();
    Storage::disk('local')->assertExists('private_uploads/maintenances/'.$uploadedLog->filename);

    $this->assertDatabaseHas('maintenances', [
        'item_id' => $asset->id,
        'item_type' => \App\Models\Asset::class,
        'supplier_id' => $supplier->id,
        'maintenance_type_id' => $type->id,
        'asset_maintenance_type' => $type->name,
        'name' => 'Test Maintenance',
        'is_warranty' => 1,
        'start_date' => '2021-01-01 00:00:00',
        'expected_completion_date' => '2021-01-10 00:00:00',
        'notes' => 'A note',
        'url' => 'https://snipeitapp.com',
        'cost' => '100.00',
        'image' => $maintenance->image,
        'created_by' => $actor->id,
    ]);

    $this->assertHasTheseActionLogs($maintenance, ['create', 'uploaded']);
});

test('cleared responsible party persists as null', function () {
    // Regression for issue #19452: the create form pre-populates
    // responsible_party_id with the acting user as a UX default,
    // but if the user clears the picker before submitting, the
    // server used to fall back to the acting user via a `?:`
    // shortcut, nullifying the user's clear. The submission is a
    // deliberate deselection and must persist as null, matching
    // update()'s behavior.
    $actor = User::factory()->superuser()->create();
    $asset = Asset::factory()->create();
    $type = MaintenanceType::factory()->create();

    $this->actingAs($actor)
        ->post(route('maintenances.store'), [
            'name' => 'Cleared Party Maintenance',
            'selected_assets' => [$asset->id],
            'maintenance_type_id' => $type->id,
            'start_date' => '2021-01-01 00:00:00',
            'is_warranty' => '0',
            'responsible_party_id' => '',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('maintenances.index'));

    $this->assertDatabaseHas('maintenances', [
        'name' => 'Cleared Party Maintenance',
        'responsible_party_id' => null,
    ]);
});

test('explicitly picked responsible party persists', function () {
    $actor = User::factory()->superuser()->create();
    $party = User::factory()->create();
    $asset = Asset::factory()->create();
    $type = MaintenanceType::factory()->create();

    $this->actingAs($actor)
        ->post(route('maintenances.store'), [
            'name' => 'Picked Party Maintenance',
            'selected_assets' => [$asset->id],
            'maintenance_type_id' => $type->id,
            'start_date' => '2021-01-01 00:00:00',
            'is_warranty' => '0',
            'responsible_party_id' => $party->id,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('maintenances.index'));

    $this->assertDatabaseHas('maintenances', [
        'name' => 'Picked Party Maintenance',
        'responsible_party_id' => $party->id,
    ]);
});

test('can backfill a completed maintenance via create', function () {
    // Users record maintenances that were already finished (backfill).
    // Setting completed_at on create should: (a) persist the value,
    // (b) stamp completed_by, (c) compute asset_maintenance_time from
    // start_date → completed_at, and (d) fire the MaintenanceComplete
    // action log alongside the create log.
    $actor = User::factory()->superuser()->create();
    $asset = Asset::factory()->create();
    $type = MaintenanceType::factory()->create();

    $this->actingAs($actor)
        ->post(route('maintenances.store'), [
            'name' => 'Backfilled Maintenance',
            'selected_assets' => [$asset->id],
            'maintenance_type_id' => $type->id,
            'start_date' => '2021-01-01 00:00:00',
            'completed_at' => '2021-01-08 00:00:00',
            'cost' => '0.00',
            'notes' => 'Backfilled after the fact',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('maintenances.index'));

    $maintenance = Maintenance::where('name', 'Backfilled Maintenance')->firstOrFail();

    expect($maintenance->completed_at->format('Y-m-d H:i:s'))->toEqual('2021-01-08 00:00:00');
    expect($maintenance->completed_by)->toEqual($actor->id);
    expect($maintenance->asset_maintenance_time)->toEqual(7, 'Duration should be start_date → completed_at, not created_at → completed_at (created_at is now, not the historical timeline)');

    $this->assertDatabaseHas('action_logs', [
        'item_type' => Maintenance::class,
        'item_id' => $maintenance->id,
        'action_type' => 'completed',
        'created_by' => $actor->id,
    ]);
});

test('creating without completed at leaves maintenance active', function () {
    // Sanity guard: the default create flow (no completed_at) must
    // not accidentally stamp anything. Snipe called this out — a
    // datetimepicker that defaults to now() would silently mark
    // freshly-created maintenances complete.
    $actor = User::factory()->superuser()->create();
    $asset = Asset::factory()->create();
    $type = MaintenanceType::factory()->create();

    $this->actingAs($actor)
        ->post(route('maintenances.store'), [
            'name' => 'Active Maintenance',
            'selected_assets' => [$asset->id],
            'maintenance_type_id' => $type->id,
            'start_date' => '2021-01-01 00:00:00',
        ])
        ->assertSessionHasNoErrors();

    $maintenance = Maintenance::where('name', 'Active Maintenance')->firstOrFail();

    expect($maintenance->completed_at)->toBeNull();
    expect($maintenance->completed_by)->toBeNull();
    expect($maintenance->asset_maintenance_time)->toBeNull();
    $this->assertDatabaseMissing('action_logs', [
        'item_type' => Maintenance::class,
        'item_id' => $maintenance->id,
        'action_type' => 'completed',
    ]);
});
