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
}
