<?php

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Maintenance;
use App\Models\MaintenanceType;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('maintenances.edit', Maintenance::factory()->create()->id))
        ->assertOk();
});

test('can update maintenance', function () {
    Storage::fake('public');
    Storage::fake('local');

    $actor = User::factory()->superuser()->create();
    $asset = Asset::factory()->create();
    $maintenance = Maintenance::factory()->create(['asset_id' => $asset]);
    $supplier = Supplier::factory()->create();
    $type = MaintenanceType::factory()->create();

    $this->actingAs($actor)
        ->put(route('maintenances.update', $maintenance), [
            'name' => 'Test Maintenance',
            'asset_id' => $asset->id,
            'supplier_id' => $supplier->id,
            'maintenance_type_id' => $type->id,
            'start_date' => '2021-01-01 00:00:00',
            'completion_date' => '2021-01-10 00:00:00',
            'is_warranty' => 1,
            'image' => UploadedFile::fake()->image('test_image.png'),
            'file' => [UploadedFile::fake()->create('maintenance-update.pdf', 64, 'application/pdf')],
            'cost' => '100.99',
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
        'cost' => '100.99',
    ]);

    $this->assertHasTheseActionLogs($maintenance, ['create', 'update', 'uploaded']);

    $updateLog = Actionlog::query()
        ->where('item_type', Maintenance::class)
        ->where('item_id', $maintenance->id)
        ->where('action_type', 'update')
        ->latest('id')
        ->first();

    expect($updateLog)->not->toBeNull();
    expect($updateLog->log_meta)->not->toBeNull();
    expect(json_decode($updateLog->log_meta, true))->toHaveKey('name');
});

test('setting completed at on edit marks maintenance complete', function () {
    // Editing a maintenance from null → a completed_at date is
    // equivalent to clicking Mark Complete: stamps completed_by,
    // computes asset_maintenance_time from created_at → the entered
    // date, and fires the MaintenanceComplete action log. This lets
    // users backfill completion for maintenances someone forgot to
    // close out at the time.
    $actor = User::factory()->superuser()->create();

    // Pin created_at well in the past so a completion date 5 days
    // later is still safely <= now(). The before_or_equal:now rule
    // would otherwise reject anything that lands in the future.
    $maintenance = Maintenance::factory()->create([
        'start_date' => '2021-01-01 00:00:00',
        'completed_at' => null,
        'created_at' => '2021-01-01 00:00:00',
    ]);
    $completionDate = '2021-01-06 00:00:00';

    $this->actingAs($actor)
        ->put(route('maintenances.update', $maintenance), [
            'name' => $maintenance->name,
            'asset_id' => $maintenance->asset_id,
            'maintenance_type_id' => $maintenance->maintenance_type_id,
            'start_date' => '2021-01-01 00:00:00',
            'completed_at' => $completionDate,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('maintenances.index'));

    $maintenance->refresh();

    expect($maintenance->completed_at)->not->toBeNull();
    expect($maintenance->completed_at->format('Y-m-d H:i:s'))->toEqual($completionDate);
    expect($maintenance->completed_by)->toEqual($actor->id);
    expect($maintenance->asset_maintenance_time)->toEqual(5);

    $this->assertDatabaseHas('action_logs', [
        'item_type' => Maintenance::class,
        'item_id' => $maintenance->id,
        'action_type' => 'completed',
        'created_by' => $actor->id,
    ]);
});

test('clearing completed at on edit uncompletes the maintenance', function () {
    // Value → null on edit reverses the completion: clears
    // completed_at, completed_by, and asset_maintenance_time so the
    // row goes back to the active list. No new log fires.
    $actor = User::factory()->superuser()->create();
    $originalCompleter = User::factory()->create();
    $maintenance = Maintenance::factory()->create([
        'start_date' => '2021-01-01 00:00:00',
        'completed_at' => '2021-01-10 00:00:00',
        'completed_by' => $originalCompleter->id,
        'asset_maintenance_time' => 9,
    ]);

    $this->actingAs($actor)
        ->put(route('maintenances.update', $maintenance), [
            'name' => $maintenance->name,
            'asset_id' => $maintenance->asset_id,
            'maintenance_type_id' => $maintenance->maintenance_type_id,
            'start_date' => '2021-01-01 00:00:00',
            'completed_at' => '',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('maintenances.index'));

    $maintenance->refresh();
    expect($maintenance->completed_at)->toBeNull();
    expect($maintenance->completed_by)->toBeNull();
    expect($maintenance->asset_maintenance_time)->toBeNull();
});

test('editing completed at on already completed maintenance preserves completed by and does not relog', function () {
    // date → different-date is a corrective edit, not a fresh
    // completion. Original completer stays put, no second
    // MaintenanceComplete log entry gets fired.
    $actor = User::factory()->superuser()->create();
    $originalCompleter = User::factory()->create();
    $maintenance = Maintenance::factory()->create([
        'start_date' => '2021-01-01 00:00:00',
        'completed_at' => '2021-01-10 00:00:00',
        'completed_by' => $originalCompleter->id,
    ]);

    $priorLogCount = Actionlog::query()
        ->where('item_type', Maintenance::class)
        ->where('item_id', $maintenance->id)
        ->where('action_type', 'completed')
        ->count();

    $this->actingAs($actor)
        ->put(route('maintenances.update', $maintenance), [
            'name' => $maintenance->name,
            'asset_id' => $maintenance->asset_id,
            'maintenance_type_id' => $maintenance->maintenance_type_id,
            'start_date' => '2021-01-01 00:00:00',
            'completed_at' => '2021-01-15 00:00:00',
        ])
        ->assertSessionHasNoErrors();

    $maintenance->refresh();
    expect($maintenance->completed_at->format('Y-m-d H:i:s'))->toEqual('2021-01-15 00:00:00');
    expect($maintenance->completed_by)->toEqual($originalCompleter->id, 'Original completer should not be overwritten by a corrective edit');

    $newLogCount = Actionlog::query()
        ->where('item_type', Maintenance::class)
        ->where('item_id', $maintenance->id)
        ->where('action_type', 'completed')
        ->count();
    expect($newLogCount)->toEqual($priorLogCount, 'Corrective edit should not fire a fresh MaintenanceComplete action log');
});

test('completed at rejects date before start date', function () {
    // Model-level rule: completed_at must be >= start_date. Prevents
    // "completed before it began" nonsense.
    $actor = User::factory()->superuser()->create();
    $maintenance = Maintenance::factory()->create([
        'start_date' => '2021-06-01 00:00:00',
        'completed_at' => null,
    ]);

    $this->actingAs($actor)
        ->from(route('maintenances.edit', $maintenance))
        ->put(route('maintenances.update', $maintenance), [
            'name' => $maintenance->name,
            'asset_id' => $maintenance->asset_id,
            'maintenance_type_id' => $maintenance->maintenance_type_id,
            'start_date' => '2021-06-01 00:00:00',
            'completed_at' => '2021-05-15 00:00:00',
        ])
        ->assertSessionHasErrors('completed_at')
        ->assertRedirect(route('maintenances.edit', $maintenance));

    expect($maintenance->fresh()->completed_at)->toBeNull();
});

test('completed at rejects future date', function () {
    // Model-level rule: completed_at must be <= now. You can't have
    // completed a maintenance in the future.
    $actor = User::factory()->superuser()->create();
    $maintenance = Maintenance::factory()->create([
        'start_date' => '2021-01-01 00:00:00',
        'completed_at' => null,
    ]);

    $this->actingAs($actor)
        ->from(route('maintenances.edit', $maintenance))
        ->put(route('maintenances.update', $maintenance), [
            'name' => $maintenance->name,
            'asset_id' => $maintenance->asset_id,
            'maintenance_type_id' => $maintenance->maintenance_type_id,
            'start_date' => '2021-01-01 00:00:00',
            'completed_at' => now()->addYear()->format('Y-m-d H:i:s'),
        ])
        ->assertSessionHasErrors('completed_at')
        ->assertRedirect(route('maintenances.edit', $maintenance));

    expect($maintenance->fresh()->completed_at)->toBeNull();
});

test('edit form seeds url intended from referer and update redirects there', function () {
    $maintenance = Maintenance::factory()->create();
    $type = MaintenanceType::factory()->create();
    $filteredIndex = config('app.url').'/maintenances?completed=true';

    $actor = User::factory()->superuser()->create();

    // GET the edit form with a referer set (as the browser would)
    // to seed session()['url.intended'].
    $this->actingAs($actor)
        ->from($filteredIndex)
        ->get(route('maintenances.edit', $maintenance->id))
        ->assertOk();

    // The subsequent PUT should honor the seeded intended URL.
    $this->actingAs($actor)
        ->put(route('maintenances.update', $maintenance), [
            'name' => 'Post-edit',
            'asset_id' => $maintenance->asset_id,
            'maintenance_type_id' => $type->id,
            'start_date' => '2021-01-01 00:00:00',
            'completion_date' => '2021-01-10 00:00:00',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect($filteredIndex);
});

test('edit update falls back to index when url intended is offsite', function () {
    $maintenance = Maintenance::factory()->create();
    $type = MaintenanceType::factory()->create();

    // Seed url.intended with an attacker-controlled URL directly, as
    // if SamlController RelayState or a stale session value slipped
    // it in. Helper::sameOriginUrl must reject it.
    $this->actingAs(User::factory()->superuser()->create())
        ->withSession(['url.intended' => 'https://evil.example.com/steal-session'])
        ->put(route('maintenances.update', $maintenance), [
            'name' => 'Post-edit',
            'asset_id' => $maintenance->asset_id,
            'maintenance_type_id' => $type->id,
            'start_date' => '2021-01-01 00:00:00',
            'completion_date' => '2021-01-10 00:00:00',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('maintenances.index'));
});

test('user cannot edit maintenance for another company when fmcs enabled', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $userInCompanyA = $companyA->users()->save(User::factory()->editAssets()->make());
    $maintenanceForCompanyB = Maintenance::factory()->create();
    $maintenanceForCompanyB->asset->update(['company_id' => $companyB->id]);

    $this->actingAs($userInCompanyA)
        ->get(route('maintenances.edit', $maintenanceForCompanyB))
        ->assertRedirectToRoute('maintenances.index');

    $this->actingAs($userInCompanyA)
        ->put(route('maintenances.update', $maintenanceForCompanyB), [
            'name' => 'Should Not Update',
            'asset_id' => $maintenanceForCompanyB->asset_id,
            'maintenance_type_id' => $maintenanceForCompanyB->maintenance_type_id,
            'start_date' => $maintenanceForCompanyB->start_date,
        ])
        ->assertRedirectToRoute('maintenances.index');

    $this->assertDatabaseMissing('maintenances', [
        'id' => $maintenanceForCompanyB->id,
        'name' => 'Should Not Update',
    ]);
});
