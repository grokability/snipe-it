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




class PredefinedFilterControllerTest extends TestCase
{
    /**
     * Test that unauthenticated user is denied access to predefined filters
     */

    public function test_api_request_with_headers_returns_403_response()
    {  
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0',
                'Accept' => 'application/json, text/javascript, */*; q=0.01',
                'Accept-Language' => 'de-DE,de;q=0.8,en-US;q=0.5,en;q=0.3',
                'Content-Type' => 'application/json',
                'X-CSRF-TOKEN' => csrf_token(),
                'X-Requested-With' => 'XMLHttpRequest',
                'Sec-Fetch-Dest' => 'empty',
                'Sec-Fetch-Mode' => 'cors',
                'Sec-Fetch-Site' => 'same-origin',
            ])
            ->json('GET', '/api/v1/predefinedFilters');

        $response->assertStatus(403);
    }

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
            'can_view'             => 1,
            'created_by'           => $user->id,
            'created_at'           => now(),
            'updated_at'           => now(),
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
            'created_by' => $owner->id, // Not the user, so only permission matters
            'filter_data' => ['status_id' => [1]],
        ]);

        // Grant permission to view Filter 1
        DB::table('predefined_filter_permissions')->insert([
            'predefined_filter_id' => $filterWithAccess->id,
            'permission_group_id'  => $group->id,
            'can_view'             => 1,
            'created_by'           => $user->id,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        // Filter 2: No permission to view
        $filterWithoutAccess = PredefinedFilter::factory()->create([
            'name' => 'Hidden Filter',
            'created_by' => $owner->id, // Again, not owned by the user
            'filter_data' => ['status_id' => [1]],
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/predefinedFilters');

        $response->assertStatus(200);
    
        $response->assertJsonFragment(['name' => 'Viewable Filter']);
        $response->assertJsonMissing(['name' => 'Hidden Filter']);
    }
}

