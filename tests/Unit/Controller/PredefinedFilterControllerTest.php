<?php

namespace Tests\Unit\Controller;

use Tests\TestCase;
use App\Models\User;
use App\Models\PredefinedFilter;
use App\Models\Company;
use App\Policies\PredefinedFilterPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use App\Models\PermissionGroup;
use Illuminate\Support\Facades\DB;
use function PHPUnit\Framework\assertEquals;
use Illuminate\Foundation\Testing\RefreshDatabase;




class PredefinedFilterControllerTest extends TestCase
{

    use RefreshDatabase;
    /**
     * Test that unauthenticated user is denied access to predefined filters
     */

    public function test_api_request_with_headers_returns_200_response()
    {
        $user = User::factory()->create([
            'email' => 'predefined@filter.com'
        ]);

        $group = PermissionGroup::factory()->create([
        'name' => 'Test',
            'permissions' => json_encode([
                'predefinedFilter.view' => '1',
                'predefinedFilter.create' => '1',
                'predefinedFilter.edit' => '1',
                'predefinedFilter.delete' => '1',
            ]),
            'created_by' => $user->id,
        ]);

        $user->permissions = json_encode([
            'predefinedFilter.view' => '1',]);
        $user->save();

        DB::table('users_groups')->insert([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'created_by' => $user->id,
        ]);


        $response = $this->actingAs($user, 'api')

            ->json('GET', '/api/v1/predefinedFilters');

            
        $response->assertStatus(200);
    }

    public function test_user_can_view_predefined_filter_if_permission_exists()
    {
        $user = User::factory()->create();

        $group = PermissionGroup::factory()->create([
            'permissions' => json_encode(['predefinedFilter.view' => '1']),
        ]);

        $user->groups()->attach($group->id);

        $filter = PredefinedFilter::factory()->create([
            'created_by' => $user->id,
        ]);


        DB::table('predefined_filter_permissions')->insert([
            'predefined_filter_id' => $filter->id,
            'permission_group_id'  => $group->id,
            'created_by'           => $user->id,
        ]);

        $response = $this->actingAs($user, 'api')->getJson("/api/v1/predefinedFilters/{$filter->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $filter->id,
                'name' => $filter->name,
            ]);
    }

    public function test_user_without_view_permission_cannot_see_predefined_filter()
    {

        $userWithoutPermission = User::factory()->create();

        $owner = User::factory()->create();

        $groupWithView = PermissionGroup::factory()->create([
            'permissions' => json_encode(['predefinedFilter.view' => '1']),
        ]);

        $owner->groups()->attach($groupWithView->id);

        $filter = PredefinedFilter::factory()->create([
            'created_by' => $owner->id,
            'filter_data' => ['status_id' => [1]],
        ]);

        $this->assertNotNull($filter->id);

        $response = $this->actingAs($userWithoutPermission, 'api')
            ->getJson("/api/v1/predefinedFilters/{$filter->id}");

        $response->assertStatus(403);
        $response->assertJsonFragment(['message' => "You don't have the permissions to see this filter"]);
    }

    public function test_user_sees_only_filters_they_have_permission_for()
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();

        // Permission group that allows view
        $group = PermissionGroup::factory()->create([
            'permissions' => json_encode(['predefinedFilter.view' => '1']),
        ]);

        $user->groups()->attach($group->id);

        // Filter 1: User has view permission
        $filterWithAccess = PredefinedFilter::factory()->create([
            'name' => 'Viewable Filter',
            'created_by' => $owner->id,
            'filter_data' => ['status_id' => [1]],
            'is_public' => 1,
        ]);

        // Grant permission to view Filter 1
        DB::table('predefined_filter_permissions')->insert([
            'predefined_filter_id' => $filterWithAccess->id,
            'permission_group_id'  => $group->id,
            'created_by'           => $user->id,
        ]);

        // Filter 2: No permission to view
        $filterWithoutAccess = PredefinedFilter::factory()->create([
            'name' => 'Hidden Filter',
            'created_by' => $owner->id, 
            'filter_data' => ['status_id' => [1]],
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/predefinedFilters');

        $response->assertStatus(200);
    
        $response->assertJsonFragment(['name' => 'Viewable Filter']);
        $response->assertJsonMissing(['name' => 'Hidden Filter']);
    }

    public function test_user_can_view_public_filter_with_permission()
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();

        // Create permission group that allows view
        $group = PermissionGroup::factory()->create([
            'permissions' => json_encode(['predefinedFilter.view' => '1']),
        ]);

        // Attach group to user
        $user->groups()->attach($group->id);

        // Create a public filter owned by someone else
        $filter = PredefinedFilter::factory()->create([
            'name' => 'Allowed Public Filter',
            'created_by' => $owner->id,
            'filter_data' => ['status_id' => [1]],
            'is_public' => 1,
        ]);

        // Grant permission via predefined_filter_permissions pivot table
        DB::table('predefined_filter_permissions')->insert([
            'predefined_filter_id' => $filter->id,
            'permission_group_id' => $group->id,
            'created_by' => $user->id,
        ]);

        // Make the request as the user
        $response = $this->actingAs($user, 'api')
            ->getJson("/api/v1/predefinedFilters/{$filter->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Allowed Public Filter']);
    }

    public function test_user_cannot_view_private_filter_they_do_not_own()
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();

        // Create a private filter (not public, no permissions)
        $filter = PredefinedFilter::factory()->create([
            'name' => 'Private Filter',
            'created_by' => $owner->id,
            'filter_data' => ['status_id' => [2]],
            'is_public' => 0,
        ]);

        // No permissions are granted to user

        $response = $this->actingAs($user, 'api')
            ->getJson("/api/v1/predefinedFilters/{$filter->id}");

        $response->assertStatus(403);
        $response->assertJsonFragment([
            'message' => "You don't have the permissions to see this filter"
        ]);
    }

    public function test_show_returns_404_if_filter_not_found()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->getJson("/api/v1/predefinedFilters/404"); // Nonexistent ID

        $response->assertStatus(404);
                $response->assertJsonFragment([
            'message' => "Filter does not exist."
        ]);
    }

    public function test_user_with_permission_can_create_public_filter()
    {
        $user = User::factory()->create();

        // Attach permission group with predefinedFilter.create permission
        $group = PermissionGroup::factory()->create([
            'permissions' => json_encode(['predefinedFilter.create' => '1']),
        ]);
        $user->groups()->attach($group->id);

        $payload = [
            'name' => 'Test Public Filter',
            'filter_data' => ['status_id' => [1]],
            'is_public' => true,
        ];

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/predefinedFilters', $payload);

        \Log::error('Response: ' . $response->getContent()); 

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Test Public Filter',
                'is_public' => true,
        ]);
    }

    public function test_user_without_permission_cannot_create_public_filter()
    {
        $user = User::factory()->create();

        // No permissions attached to user here

        $payload = [
            'name' => 'Unauthorized Public Filter',
            'filter_data' => ['status_id' => [1]],
            'is_public' => true,
        ];

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/predefinedFilters', $payload);

        \Log::error('Response: ' . $response->getContent()); 

        $response->assertStatus(403)
            ->assertJsonFragment([
                'message' => "You don't have the permissions to create this public filter",
        ]);
    }

    public function test_sync_permission_groups_returns_200_response()
    {
        // Create user
        $user = User::factory()->create([
            'email' => 'sync@test.com'
        ]);

        // Give user update permission
        $user->permissions = json_encode([
            'predefinedFilter.update' => '1',
        ]);
        $user->save();

        // Create filter with user as creator
        $filter = PredefinedFilter::factory()->create([
            'created_by' => $user->id,
        ]);

        // Create groups to sync
        $group1 = PermissionGroup::factory()->create([
            'name' => 'Group 1',
            'created_by' => $user->id,
        ]);

        $group2 = PermissionGroup::factory()->create([
            'name' => 'Group 2',
            'created_by' => $user->id,
        ]);

        // Prepare payload
        $payload = [
            'group_ids' => [$group1->id, $group2->id],
        ];

        // Call the endpoint
        $response = $this->actingAs($user, 'api')
            ->json('Put', "/api/v1/predefinedFilters/{$filter->id}/sync-permissions", $payload);

        // Assert success
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Permission groups synced successfully.',
        ]);

        // Optional: Assert the sync actually occurred
        $this->assertDatabaseHas('predefined_filter_permissions', [
            'predefined_filter_id' => $filter->id,
            'permission_group_id' => $group1->id,
        ]);

        $this->assertDatabaseHas('predefined_filter_permissions', [
            'predefined_filter_id' => $filter->id,
            'permission_group_id' => $group2->id,
        ]);
    }
}

