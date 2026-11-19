<?php

use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\MaintenanceType;
use App\Models\User;

test('requires permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.maintenances.index'))
        ->assertForbidden();
});

test('checked out to id filter returns only matching polymorphic target', function () {
    // Used by the user detail Maintenances tab — pulls every maintenance
    // whose underlying asset was checked out to a specific user. Type
    // defaults to App\Models\User when only the id is supplied.
    //
    // MaintenanceObserver::creating() copies checked_out_to_* from the
    // asset's assigned_to/_type at insert time (so the maintenance pins
    // *who had it when the maintenance was opened*, not whoever has it
    // now). Tests therefore must check the asset out to the target user
    // first — passing checked_out_to_* directly to the factory would be
    // silently overwritten.
    $actor = User::factory()->superuser()->create();
    $alice = User::factory()->create();
    $bob = User::factory()->create();

    $aliceAsset = Asset::factory()->assignedToUser($alice)->create();
    $bobAsset = Asset::factory()->assignedToUser($bob)->create();
    $unassignedAsset = Asset::factory()->create();

    $aliceM = Maintenance::factory()->create(['asset_id' => $aliceAsset->id]);
    $bobM = Maintenance::factory()->create(['asset_id' => $bobAsset->id]);
    $unassigned = Maintenance::factory()->create(['asset_id' => $unassignedAsset->id]);

    $response = $this->actingAsForApi($actor)
        ->getJson(route('api.maintenances.index', [
            'checked_out_to_id' => $alice->id,
            'checked_out_to_type' => User::class,
        ]))
        ->assertOk();

    $ids = collect($response->json('rows'))->pluck('id');
    expect($ids)->toContain($aliceM->id);
    expect($ids)->not->toContain($bobM->id); // Different-user maintenances must be filtered out
    expect($ids)->not->toContain($unassigned->id); // Unassigned maintenances must be filtered out
});

test('completed filter returns only completed maintenances', function () {
    $actor = User::factory()->superuser()->create();
    $active = Maintenance::factory()->create(['completed_at' => null]);
    $done = Maintenance::factory()->create(['completed_at' => now()]);

    $response = $this->actingAsForApi($actor)
        ->getJson(route('api.maintenances.index', ['completed' => 'true']))
        ->assertOk();

    $ids = collect($response->json('rows'))->pluck('id');
    expect($ids)->toContain($done->id);
    expect($ids)->not->toContain($active->id);
});

test('completed false filter returns only active maintenances', function () {
    $actor = User::factory()->superuser()->create();
    $active = Maintenance::factory()->create(['completed_at' => null]);
    $done = Maintenance::factory()->create(['completed_at' => now()]);

    $response = $this->actingAsForApi($actor)
        ->getJson(route('api.maintenances.index', ['completed' => 'false']))
        ->assertOk();

    $ids = collect($response->json('rows'))->pluck('id');
    expect($ids)->toContain($active->id);
    expect($ids)->not->toContain($done->id);
});

test('upcoming status overdue returns only overdue', function () {
    $actor = User::factory()->superuser()->create();

    $overdue = Maintenance::factory()->create([
        'completion_date' => now()->subDay()->format('Y-m-d'),
        'completed_at' => null,
    ]);

    $fine = Maintenance::factory()->create([
        'completion_date' => now()->addDays(30)->format('Y-m-d'),
        'completed_at' => null,
    ]);

    $response = $this->actingAsForApi($actor)
        ->getJson(route('api.maintenances.index', ['upcoming_status' => 'overdue']))
        ->assertOk();

    $ids = collect($response->json('rows'))->pluck('id');
    expect($ids)->toContain($overdue->id);
    expect($ids)->not->toContain($fine->id);
});

test('upcoming status due respects warning window', function () {
    $this->settings->setAuditWarningDays(7);
    $actor = User::factory()->superuser()->create();

    $due = Maintenance::factory()->create([
        'completion_date' => now()->addDays(3)->format('Y-m-d'),
        'completed_at' => null,
    ]);

    $notDueYet = Maintenance::factory()->create([
        'completion_date' => now()->addDays(30)->format('Y-m-d'),
        'completed_at' => null,
    ]);

    $response = $this->actingAsForApi($actor)
        ->getJson(route('api.maintenances.index', ['upcoming_status' => 'due']))
        ->assertOk();

    $ids = collect($response->json('rows'))->pluck('id');
    expect($ids)->toContain($due->id);
    expect($ids)->not->toContain($notDueYet->id);
});

test('maintenance type is returned as flat string', function () {
    $actor = User::factory()->superuser()->create();
    $type = MaintenanceType::factory()->create(['name' => 'Annual Checkup']);
    $maintenance = Maintenance::factory()->create(['maintenance_type_id' => $type->id]);

    $response = $this->actingAsForApi($actor)
        ->getJson(route('api.maintenances.show', $maintenance))
        ->assertOk();

    expect($response->json('maintenance_type'))->toEqual('Annual Checkup');
    expect($response->json('maintenance_type_details.name'))->toEqual('Annual Checkup');
});

test('sort by maintenance type does not error', function () {
    $actor = User::factory()->superuser()->create();
    Maintenance::factory()->count(3)->create();

    $this->actingAsForApi($actor)
        ->getJson(route('api.maintenances.index', ['sort' => 'maintenance_type', 'order' => 'asc']))
        ->assertOk();
});

test('sort by completed at does not error', function () {
    $actor = User::factory()->superuser()->create();
    Maintenance::factory()->count(2)->create(['completed_at' => null]);
    Maintenance::factory()->create(['completed_at' => now()]);

    $this->actingAsForApi($actor)
        ->getJson(route('api.maintenances.index', ['sort' => 'completed_at', 'order' => 'desc']))
        ->assertOk();
});

test('search matches asset company name', function () {
    // The maintenances table shows the asset's company, so searching
    // for a company name from the search box should return matching
    // rows. Before this fix, asset.company wasn't in the Maintenance
    // model's $searchableRelations, so the search silently matched
    // nothing.
    $actor = User::factory()->superuser()->create();

    $company = \App\Models\Company::factory()->create(['name' => 'Acme Widgets Ltd']);
    $matchingAsset = Asset::factory()->create(['company_id' => $company->id]);
    $matchingMaintenance = Maintenance::factory()->create(['asset_id' => $matchingAsset->id]);

    $otherAsset = Asset::factory()->create();
    $otherMaintenance = Maintenance::factory()->create(['asset_id' => $otherAsset->id]);

    $ids = collect($this->actingAsForApi($actor)
        ->getJson(route('api.maintenances.index', ['search' => 'Acme Widgets']))
        ->assertOk()
        ->json('rows'))
        ->pluck('id')
        ->all();

    expect($ids)->toContain($matchingMaintenance->id);
    expect($ids)->not->toContain($otherMaintenance->id);
});

test('search matches asset location name', function () {
    $actor = User::factory()->superuser()->create();

    $location = \App\Models\Location::factory()->create(['name' => 'Zanzibar HQ']);
    $matchingAsset = Asset::factory()->create(['location_id' => $location->id]);
    $matchingMaintenance = Maintenance::factory()->create(['asset_id' => $matchingAsset->id]);

    $otherAsset = Asset::factory()->create();
    $otherMaintenance = Maintenance::factory()->create(['asset_id' => $otherAsset->id]);

    $ids = collect($this->actingAsForApi($actor)
        ->getJson(route('api.maintenances.index', ['search' => 'Zanzibar']))
        ->assertOk()
        ->json('rows'))
        ->pluck('id')
        ->all();

    expect($ids)->toContain($matchingMaintenance->id);
    expect($ids)->not->toContain($otherMaintenance->id);
});

test('search matches asset default location name', function () {
    // defaultLoc is the RTD (return-to) location on the asset. When an
    // asset is unassigned, the maintenance table shows the RTD
    // location as its location column; searching that value should
    // still find the row.
    $actor = User::factory()->superuser()->create();

    $rtd = \App\Models\Location::factory()->create(['name' => 'Reykjavik Warehouse']);
    $matchingAsset = Asset::factory()->create(['rtd_location_id' => $rtd->id, 'location_id' => null]);
    $matchingMaintenance = Maintenance::factory()->create(['asset_id' => $matchingAsset->id]);

    $otherAsset = Asset::factory()->create();
    $otherMaintenance = Maintenance::factory()->create(['asset_id' => $otherAsset->id]);

    $ids = collect($this->actingAsForApi($actor)
        ->getJson(route('api.maintenances.index', ['search' => 'Reykjavik']))
        ->assertOk()
        ->json('rows'))
        ->pluck('id')
        ->all();

    expect($ids)->toContain($matchingMaintenance->id);
    expect($ids)->not->toContain($otherMaintenance->id);
});

test('response emits both completion date and expected completion date', function () {
    // API v1 back-compat: consumers reading rows[].completion_date
    // must not break when the underlying DB column was renamed to
    // expected_completion_date. The transformer emits the same value
    // under both keys so old and new consumers can coexist.
    $actor = User::factory()->superuser()->create();
    Maintenance::factory()->create([
        'start_date' => '2021-01-01 00:00:00',
        'expected_completion_date' => '2021-02-01',
    ]);

    $row = $this->actingAsForApi($actor)
        ->getJson(route('api.maintenances.index'))
        ->assertOk()
        ->json('rows.0');

    expect($row)->toHaveKey('completion_date'); // Legacy key completion_date must still appear in the response
    expect($row)->toHaveKey('expected_completion_date'); // New key expected_completion_date must also appear
    expect($row['completion_date'])->not->toBeNull();
    expect($row['completion_date'])->toBe($row['expected_completion_date']); // Both keys must point at the same value
});

test('legacy sort by completion date still works', function () {
    // API v1 back-compat: consumers passing sort=completion_date
    // (the pre-rename name) get the same ordering as
    // sort=expected_completion_date.
    $actor = User::factory()->superuser()->create();
    $early = Maintenance::factory()->create([
        'start_date' => '2021-01-01',
        'expected_completion_date' => '2021-01-05',
    ]);
    $late = Maintenance::factory()->create([
        'start_date' => '2021-01-01',
        'expected_completion_date' => '2021-06-05',
    ]);

    $ids = collect($this->actingAsForApi($actor)
        ->getJson(route('api.maintenances.index', ['sort' => 'completion_date', 'order' => 'asc']))
        ->assertOk()
        ->json('rows'))
        ->pluck('id')
        ->all();

    $earlyIndex = array_search($early->id, $ids);
    $lateIndex = array_search($late->id, $ids);
    $this->assertNotFalse($earlyIndex);
    $this->assertNotFalse($lateIndex);
    expect($earlyIndex)->toBeLessThan($lateIndex, 'sort=completion_date must order the same as expected_completion_date');
});
