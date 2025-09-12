<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\PredefinedFilter;
use App\Models\PermissionGroup;
use Illuminate\Support\Facades\DB;

class PredefinedFilterControllerTest extends TestCase
{
    use RefreshDatabase;

    private function grant(User $user, array $perms): PermissionGroup
    {
        $g = PermissionGroup::factory()->create([
            'permissions' => json_encode($perms),
        ]);
        $user->groups()->attach($g->id);
        return $g;
    }

    /** vormals von Jonas: test_api_request_with_headers_returns_403_response */
    public function test_index_denies_without_view_permission(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/predefinedFilters')
            ->assertStatus(403);
    }

    /** vormals von Jonas: test_api_request_with_headers_returns_200_response */
    public function test_index_ok_with_view_permission(): void
    {
        $user = User::factory()->create();
        $this->grant($user, ['predefinedFilter.view' => '1']);

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/predefinedFilters')
            ->assertOk();
    }

    /** vormals von Jonas: test_user_can_view_predefined_filter_if_permission_exists */
    public function test_show_ok_when_user_has_view_permission(): void
    {
        $user = User::factory()->create();
        $group = $this->grant($user, ['predefinedFilter.view' => '1']);

        $filter = PredefinedFilter::factory()->create(['created_by' => $user->id]);

        DB::table('predefined_filter_permissions')->insert([
            'predefined_filter_id' => $filter->id,
            'permission_group_id'  => $group->id,
            'can_view'             => 1,
            'created_by'           => $user->id,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        $this->actingAs($user, 'api')
            ->getJson("/api/v1/predefinedFilters/{$filter->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $filter->id, 'name' => $filter->name]);
    }
    
    /** vormals von Jonas: test_user_without_view_permission_cannot_see_predefined_filter */
    public function test_show_forbidden_without_view_permission(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();

        $filter = PredefinedFilter::factory()->create([
            'created_by' => $owner->id,
            'filter_data' => ['status_id' => [1]],
        ]);

        $this->actingAs($user, 'api')
            ->getJson("/api/v1/predefinedFilters/{$filter->id}")
            ->assertStatus(403);
    }
     /** vormals von Jonas: test_user_sees_only_filters_they_have_permission_for */
    public function test_index_lists_only_viewable_or_owned(): void
    {
        $owner = User::factory()->create();
        $user  = User::factory()->create();
        $group = $this->grant($user, ['predefinedFilter.view' => '1']);

        $viewable = PredefinedFilter::factory()->create([
            'name' => 'Viewable Filter',
            'created_by' => $owner->id,
            'filter_data' => ['status_id' => [1]],
        ]);

        DB::table('predefined_filter_permissions')->insert([
            'predefined_filter_id' => $viewable->id,
            'permission_group_id'  => $group->id,
            'can_view'             => 1,
            'created_by'           => $user->id,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        $hidden = PredefinedFilter::factory()->create([
            'name' => 'Hidden Filter',
            'created_by' => $owner->id,
            'filter_data' => ['status_id' => [1]],
        ]);

        $mine = PredefinedFilter::factory()->create([
            'name' => 'My Own',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/predefinedFilters')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Viewable Filter'])
            ->assertJsonFragment(['name' => 'My Own'])
            ->assertJsonMissing(['name' => 'Hidden Filter']
        );
    }
    public function test_index_unauthenticated_gets_401()
    {
        //TODO API-Aufruf auf /api/v1/predefinedFilters OHNE Login

    }

    public function test_index_empty_return_empty_array()
    {
        //TODO: Eingeloggter User MIT view-Recht

    }

    public function test_show_404_when_missing()
    {
        //TODO Eingeloggter User MIT view-Recht Abruf einer nicht existierenden ID
    }

    public function test_store_validates_payload()
    {
        //TODO Eingeloggter User OHNE spezielle Rechte POST mit leerem Payload
    }

    public function test_store_creates_and_sets_owner()
    {
        //TODO Eingeloggter User POST mit gültigem Payload (name + filter_data) und manipuliertem created_by
    }

    public function test_update_forbidden_without_edit()
    {
        //TODO Eingeloggter User OHNE edit-Recht PUT auf bestehenden Filter
    }

    public function test_update_404_when_missing()
    {
        //TODO Eingeloggter User MIT edit-Recht PUT auf nicht existierende ID
    }

    public function test_update_validates_payload()
    {
        //TODO Eingeloggter User MIT edit-Recht PUT mit leerem Payload auf bestehenden Filter
    }

    public function test_update_persists_changes_and_keeps_owner()
    {
        //TODO Eingeloggter User MIT edit-Recht PUT mit neuem Namen + neuem filter_data + manipuliertem created_by
    }

    public function destroy_forbidden_whitout_delete()
    {
        //TODO Eingeloggter User OHNE delete-Recht DELETE auf bestehenden Filter
    }

    public function destroy_404_when_messing()
    {
        //TODO Eingeloggter User MIT delete-Recht DELETE auf nicht existierende ID
    }

    public function destroy_no_content_and_removed()
    {
        //TODO Eingeloggter User MIT delete-Recht DELETE auf bestehenden Filter
    }
}