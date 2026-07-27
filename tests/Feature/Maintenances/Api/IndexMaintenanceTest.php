<?php

namespace Tests\Feature\Maintenances\Api;

use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\MaintenanceType;
use App\Models\User;
use Tests\TestCase;

class IndexMaintenanceTest extends TestCase
{
    public function test_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.maintenances.index'))
            ->assertForbidden();
    }

    public function test_checked_out_to_id_filter_returns_only_matching_polymorphic_target()
    {
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
        $this->assertContains($aliceM->id, $ids);
        $this->assertNotContains($bobM->id, $ids, 'Different-user maintenances must be filtered out');
        $this->assertNotContains($unassigned->id, $ids, 'Unassigned maintenances must be filtered out');
    }

    public function test_completed_filter_returns_only_completed_maintenances()
    {
        $actor = User::factory()->superuser()->create();
        $active = Maintenance::factory()->create(['completed_at' => null]);
        $done = Maintenance::factory()->create(['completed_at' => now()]);

        $response = $this->actingAsForApi($actor)
            ->getJson(route('api.maintenances.index', ['completed' => 'true']))
            ->assertOk();

        $ids = collect($response->json('rows'))->pluck('id');
        $this->assertContains($done->id, $ids);
        $this->assertNotContains($active->id, $ids);
    }

    public function test_completed_false_filter_returns_only_active_maintenances()
    {
        $actor = User::factory()->superuser()->create();
        $active = Maintenance::factory()->create(['completed_at' => null]);
        $done = Maintenance::factory()->create(['completed_at' => now()]);

        $response = $this->actingAsForApi($actor)
            ->getJson(route('api.maintenances.index', ['completed' => 'false']))
            ->assertOk();

        $ids = collect($response->json('rows'))->pluck('id');
        $this->assertContains($active->id, $ids);
        $this->assertNotContains($done->id, $ids);
    }

    public function test_upcoming_status_overdue_returns_only_overdue()
    {
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
        $this->assertContains($overdue->id, $ids);
        $this->assertNotContains($fine->id, $ids);
    }

    public function test_upcoming_status_due_respects_warning_window()
    {
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
        $this->assertContains($due->id, $ids);
        $this->assertNotContains($notDueYet->id, $ids);
    }

    public function test_maintenance_type_is_returned_as_flat_string()
    {
        $actor = User::factory()->superuser()->create();
        $type = MaintenanceType::factory()->create(['name' => 'Annual Checkup']);
        $maintenance = Maintenance::factory()->create(['maintenance_type_id' => $type->id]);

        $response = $this->actingAsForApi($actor)
            ->getJson(route('api.maintenances.show', $maintenance))
            ->assertOk();

        $this->assertEquals('Annual Checkup', $response->json('maintenance_type'));
    }

    public function test_sort_by_maintenance_type_does_not_error()
    {
        $actor = User::factory()->superuser()->create();
        Maintenance::factory()->count(3)->create();

        $this->actingAsForApi($actor)
            ->getJson(route('api.maintenances.index', ['sort' => 'maintenance_type', 'order' => 'asc']))
            ->assertOk();
    }

    public function test_sort_by_completed_at_does_not_error()
    {
        $actor = User::factory()->superuser()->create();
        Maintenance::factory()->count(2)->create(['completed_at' => null]);
        Maintenance::factory()->create(['completed_at' => now()]);

        $this->actingAsForApi($actor)
            ->getJson(route('api.maintenances.index', ['sort' => 'completed_at', 'order' => 'desc']))
            ->assertOk();
    }

    public function test_search_matches_asset_company_name()
    {
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

        $this->assertContains($matchingMaintenance->id, $ids);
        $this->assertNotContains($otherMaintenance->id, $ids);
    }

    public function test_search_matches_asset_location_name()
    {
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

        $this->assertContains($matchingMaintenance->id, $ids);
        $this->assertNotContains($otherMaintenance->id, $ids);
    }

    public function test_search_matches_asset_default_location_name()
    {
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

        $this->assertContains($matchingMaintenance->id, $ids);
        $this->assertNotContains($otherMaintenance->id, $ids);
    }

    public function test_response_emits_both_completion_date_and_expected_completion_date()
    {
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

        $this->assertArrayHasKey('completion_date', $row, 'Legacy key completion_date must still appear in the response');
        $this->assertArrayHasKey('expected_completion_date', $row, 'New key expected_completion_date must also appear');
        $this->assertNotNull($row['completion_date']);
        $this->assertSame($row['expected_completion_date'], $row['completion_date'], 'Both keys must point at the same value');
    }

    public function test_legacy_sort_by_completion_date_still_works()
    {
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
        $this->assertLessThan($lateIndex, $earlyIndex, 'sort=completion_date must order the same as expected_completion_date');
    }
}
