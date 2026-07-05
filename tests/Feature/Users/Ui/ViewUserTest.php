<?php

namespace Tests\Feature\Users\Ui;

use App\Models\Asset;
use App\Models\Company;
use App\Models\Group;
use App\Models\Maintenance;
use App\Models\User;
use Tests\TestCase;

class ViewUserTest extends TestCase
{
    public function test_requires_permission_to_view_user()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('users.show', User::factory()->create()))
            ->assertStatus(403);
    }

    public function test_can_view_user()
    {
        $actor = User::factory()->viewUsers()->create();

        $this->actingAs($actor)
            ->get(route('users.show', User::factory()->create()))
            ->assertOk()
            ->assertStatus(200);
    }

    public function test_cannot_view_user_from_another_company()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $actor = User::factory()->for($companyA)->viewUsers()->create();
        $user = User::factory()->for($companyB)->create();

        $this->actingAs($actor)
            ->get(route('users.show', $user))
            ->assertStatus(302);
    }

    public function test_shows_effective_permissions_from_groups_and_individual_permissions()
    {
        $actor = User::factory()->viewUsers()->create();

        $group = Group::factory()->create([
            'permissions' => json_encode([
                'assets.view' => 1,
            ]),
        ]);

        $user = User::factory()->create([
            'permissions' => json_encode([
                'reports.view' => 1,
            ]),
        ]);
        $user->groups()->attach($group->id);

        $this->actingAs($actor)
            ->get(route('users.show', $user))
            ->assertOk()
            ->assertSee('assets.view')
            ->assertSee('reports.view');
    }

    public function test_shows_explicitly_denied_permissions()
    {
        $actor = User::factory()->viewUsers()->create();

        $user = User::factory()->create([
            'permissions' => json_encode([
                'reports.view' => -1,
            ]),
        ]);

        $this->actingAs($actor)
            ->get(route('users.show', $user))
            ->assertOk()
            ->assertSee('reports.view')
            ->assertSee('label-danger', false);
    }

    public function test_user_detail_renders_maintenances_tab_with_count()
    {
        // Tab pane is fed by api.maintenances.index with checked_out_to_id
        // set to this user. Badge count includes open + closed so the
        // number matches what shows inside the pane (which is also
        // unfiltered by completion status — that's the whole point of the
        // tab: tracking who causes the most maintenance events over time).
        //
        // checked_out_to_* is populated from the asset's assigned_to by
        // MaintenanceObserver::creating(), so seed via assets here. Actor
        // needs view-asset perm too because the maintenance-tab nav-item
        // gates on Asset::class (maintenances are asset-anchored data).
        $target = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($target)->create();
        Maintenance::factory()->count(2)->create([
            'asset_id' => $asset->id,
            'completed_at' => null,
        ]);
        Maintenance::factory()->create([
            'asset_id' => $asset->id,
            'completed_at' => now(),
        ]);
        // Unrelated maintenance — must not show up via this user's filter.
        $other = Asset::factory()->assignedToUser(User::factory()->create())->create();
        Maintenance::factory()->create(['asset_id' => $other->id]);

        $this->actingAs(User::factory()->viewUsers()->viewAssets()->create())
            ->get(route('users.show', $target))
            ->assertOk()
            // Tab anchor uses #maintenances; pane wires the maintenances API
            // with the right polymorphic filter for this user.
            ->assertSee('#maintenances', false)
            ->assertSee('checked_out_to_id='.$target->id, false)
            ->assertSee('checked_out_to_type=App%5CModels%5CUser', false);
    }
}
