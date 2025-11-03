<?php

namespace Tests\Feature\PredefinedFilter\Api;

use App\Http\Transformers\PredefinedFiltersTransformer;
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

    private function linkGroupFilter(PredefinedFilter $f, PermissionGroup $g)
    {
        DB::table('predefined_filter_permissions')->insert([
            'predefined_filter_id' => $f->id,
            'permission_group_id' => $g->id,
            'created_by' => $f->created_by,
        ]);
    }

    //------INDEX TESTS------

    public function test_index_ok_with_public_and_view_permission(): void
    {
        $u = User::factory()->create();
        $g = $this->grant($u, ['predefinedFilter.view' => '1']);

        $f = PredefinedFilter::factory()->create(['is_public' => 1]);
        $this->linkGroupFilter($f, $g);

        $this->actingAs($u, 'api')
            ->getJson('/api/v1/predefinedFilters')
            ->assertOk();
    }

    public function test_index_unauthenticated_gets_302()
    {
        $this->getJson('api/v1/predefinedFilters')->assertStatus(302);

    }

    public function test_index_empty_returns_empty_array()
    {
        $u = User::factory()->create();

        $this->actingAs($u, 'api')
            ->getJson('/api/v1/predefinedFilters')
            ->assertOk()
            ->assertExactJson(['rows' => [], 'total' => 0,]);
    }

    public function test_index_lists_only_viewable_or_owned(): void
    {
        $owner = User::factory()->create();
        $u = User::factory()->create();
        $g = $this->grant($u, ['predefinedFilter.view' => '1']);

        $viewable = PredefinedFilter::factory()->create([
            'name' => 'A Viewable',
            'created_by' => $owner->id,
            'is_public' => 1,
        ]);
        $this->linkGroupFilter($viewable, $g);

        $hidden = PredefinedFilter::factory()->create([
            'name' => 'Z Hidden',
            'created_by' => $owner->id,
            'is_public' => 0,
        ]);

        $mine = PredefinedFilter::factory()->create([
            'name' => 'M My Own',
            'created_by' => $u->id,
            'is_public' => 0,
        ]);

        $response = $this->actingAs($u, 'api')
            ->getJson('/api/v1/predefinedFilters')
            ->assertOk()
            ->assertJsonFragment(['id' => $viewable->id, 'name' => 'A Viewable'])
            ->assertJsonFragment(['id' => $mine->id, 'name' => 'M My Own']);
        $this->assertCount(2, $response->json('rows'));
    }

    public function test_index_lists_only_public_linked_or_owned(): void
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $g = $this->grant($user, ['predefinedFilter.view' => '1']);

        $viewable = PredefinedFilter::factory()->create([
            'name' => 'Viewable Filter',
            'created_by' => $owner->id,
            'is_public' => 1,
        ]);
        $this->linkGroupFilter($viewable, $g);

        $hidden = PredefinedFilter::factory()->create([
            'name' => 'Hidden Filter',
            'created_by' => $owner->id,
            'is_public' => 0,
        ]);

        $mine = PredefinedFilter::factory()->create([
            'name' => 'My Own',
            'created_by' => $user->id,
            'is_public' => 0,
        ]);

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/predefinedFilters')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Viewable Filter'])
            ->assertJsonFragment(['name' => 'My Own'])
            ->assertJsonMissing(['name' => 'Hidden Filter']);
    }

    //------SHOW TESTS------

    public function test_show_404_when_missing(): void
    {
        $u = User::factory()->create();
        $this->grant($u, ['predefinedFilter.view' => '1']);

        $this->actingAs($u, 'api')
            ->getJson('/api/v1/predefinedFilters/999999')
            ->assertStatus(404)
            ->assertJson(['message' => 'Filter does not exist.']);
    }

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

    public function test_show_ok_as_owner_without_public_or_view(): void
    {
        $u = User::factory()->create();
        $f = PredefinedFilter::factory()->create([
            'created_by' => $u->id,
            'is_public' => 0,
        ]);

        $this->actingAs($u, 'api')
            ->getJson("/api/v1/predefinedFilters/{$f->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $f->id, 'name' => $f->name]);
    }

    public function test_show_forbidden_without_view_or_not_public()
    {
        $owner = User::factory()->create();
        $u = User::factory()->create();

        $f = PredefinedFilter::factory()->create([
            'created_by' => $owner->id,
            'is_public' => 0,
        ]);

        $this->actingAs($u, 'api')
            ->getJson("/api/v1/predefinedFilters/{$f->id}")
            ->assertStatus(403)
            ->assertJson(['message' => trans('admin/predefinedFilters/message.show.not_allowed')]);
    }

    public function test_show_ok_as_non_owner_when_public_and_view()
    {
        $owner = User::factory()->create();
        $u = User::factory()->create();
        $g = $this->grant($u, ['predefinedFilter.view' => '1']);

        $f = PredefinedFilter::factory()->create([
            'created_by' => $owner->id,
            'is_public' => 1,
        ]);
        $this->linkGroupFilter($f, $g);

        $this->actingAs($u, 'api')
            ->getJson("/api/v1/predefinedFilters/{$f->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $f->id, 'name' => $f->name]);
    }

    public function test_show_forbidden_when_private_and_not_owner(): void
    {
        $userWithout = User::factory()->create();
        $owner = User::factory()->create();

        $filter = PredefinedFilter::factory()->create([
            'created_by' => $owner->id,
            'is_public' => 0,
        ]);

        $this->actingAs($userWithout, 'api')
            ->getJson("/api/v1/predefinedFilters/{$filter->id}")
            ->assertStatus(403)
            ->assertJson(['message' => trans('admin/predefinedFilters/message.show.not_allowed')]);
    }

    public function test_show_non_owner_public_with_view_is_ok(): void
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $g = $this->grant($user, ['predefinedFilter.view' => '1']);

        $filter = PredefinedFilter::factory()->create([
            'name' => 'Allowed Public Filter',
            'created_by' => $owner->id,
            'is_public' => 1,
        ]);
        $this->linkGroupFilter($filter, $g);

        $this->actingAs($user, 'api')
            ->getJson("/api/v1/predefinedFilters/{$filter->id}")
            ->assertOk()
            ->assertJsonFragment(['name' => 'Allowed Public Filter']);
    }

    public function test_show_forbidden_for_private_non_owner(): void
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();

        $filter = PredefinedFilter::factory()->create([
            'name' => 'Private Filter',
            'created_by' => $owner->id,
            'is_public' => 0,
        ]);

        $this->actingAs($user, 'api')
            ->getJson("/api/v1/predefinedFilters/{$filter->id}")
            ->assertStatus(403)
            ->assertJson(['message' => trans('admin/predefinedFilters/message.show.not_allowed')]);
    }

    public function test_show_returns_404_if_filter_not_found(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/predefinedFilters/404')
            ->assertStatus(404)
            ->assertJson(['message' => trans('admin/predefinedFilters/message.does_not_exist')]);
    }


    //------STORE TESTS------

    public function test_store_validates_payload()
    {
        $u = User::factory()->create();

        $this->actingAs($u, 'api')
            ->postJson('/api/v1/predefinedFilters', [])
            ->assertStatus(422)
            ->assertJsonPath('messages.name.0', 'The name field is required.')
            ->assertJsonPath('messages.filter_data.0', 'The filter data field is required.');
    }


    public function test_store_creates_and_sets_owner()
    {
        $u = User::factory()->create();
        $this->grant($u, ['predefinedFilter.create' => '1']);

        $this->actingAs($u, 'api')
            ->postJson(route('api.predefined-filters.store'), [
                'name' => 'Neu',
                'filter_data' => ['status_id' => [1, 2]],
                'is_public' => 1,
                'created_by' => 999,
            ])
            ->assertCreated()
            ->assertJsonPath('filter_data.name', 'Neu')
            ->assertJsonPath('filter_data.is_public', true)
            ->assertJsonPath('filter_data.created_by', $u->id);
    }

    public function test_store_public_requires_create_permission()
    {
        $u = User::factory()->create();

        $this->actingAs($u, 'api')
            ->postJson(route('api.predefined-filters.store'), [
                'name' => 'X',
                'filter_data' => ['a' => 1],
                'is_public' => 1,
            ])
            ->assertStatus(403)
            ->assertJson(['message' => trans('admin/predefinedFilters/message.create.not_allowed')]);
    }
    public function test_store_public_with_create_permission_returns_201(): void
    {
        $user = User::factory()->create();
        $this->grant($user, ['predefinedFilter.create' => '1']);

        $payload = [
            'name' => 'Test Public Filter',
            'filter_data' => ['status_id' => [1]],
            'is_public' => true,
        ];

        $this->actingAs($user, 'api')
            ->postJson('/api/v1/predefinedFilters', $payload)
            ->assertCreated()
            ->assertJsonPath('filter_data.name', 'Test Public Filter')
            ->assertJsonPath('filter_data.is_public', true)
            ->assertJsonPath('filter_data.created_by', $user->id);
    }

    public function test_store_public_without_create_permission_returns_403(): void
    {
        $user = User::factory()->create();

        $payload = [
            'name' => 'Unauthorized Public Filter',
            'filter_data' => ['status_id' => [1]],
            'is_public' => true,
        ];

        $this->actingAs($user, 'api')
            ->postJson('/api/v1/predefinedFilters', $payload)
            ->assertStatus(403)
            ->assertJson(['message' => trans('admin/predefinedFilters/message.create.not_allowed')]);
    }

    //------UPDATE TESTS------

    public function test_update_owner_private_to_public_requires_create(): void
    {
        $u = User::factory()->create();
        $f = PredefinedFilter::factory()->create([
            'created_by' => $u->id,
            'is_public' => 0,
            'name' => 'Old',
            'filter_data' => ['a' => 1],
        ]);

        $this->actingAs($u, 'api')
            ->putJson("/api/v1/predefinedFilters/{$f->id}", [
                'name' => 'New',
                'filter_data' => ['a' => 2],
                'is_public' => 1
            ])
            ->assertStatus(403);

        $this->grant($u, ['predefinedFilter.create' => '1']);
        $u->refresh();

        $this->actingAs($u->fresh(), 'api')
            ->putJson("/api/v1/predefinedFilters/{$f->id}", [
                'name' => 'New',
                'filter_data' => ['a' => 2],
                'is_public' => 1
            ])
            ->assertOk()
            ->assertJsonPath('filter_data.is_public', true)
            ->assertJsonPath('filter_data.name', 'New');
    }

    public function test_update_non_owner_public_requires_update_permission(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $f = PredefinedFilter::factory()->create([
            'created_by' => $owner->id,
            'is_public' => 1,
            'name' => 'Old',
            'filter_data' => ['a' => 1],
        ]);

        $this->actingAs($other, 'api')
            ->putJson("/api/v1/predefinedFilters/{$f->id}", [
                'name' => 'X',
                'filter_data' => ['a' => 3],
                'is_public' => 1
            ])
            ->assertStatus(403);

        $g = $this->grant($other, ['predefinedFilter.edit' => '1']);
        $this->linkGroupFilter($f, $g);

        $this->actingAs($other, 'api')
            ->putJson("/api/v1/predefinedFilters/{$f->id}", [
                'name' => 'X',
                'filter_data' => ['a' => 3],
                'is_public' => 1
            ])
            ->assertOk()
            ->assertJsonPath('filter_data.name', 'X');
    }
    public function test_update_404_when_missing(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u, 'api')
            ->putJson('/api/v1/predefinedFilters/999999', [
                'name' => 'X',
                'filter_data' => [],
                'is_public' => 0
            ])
            ->assertStatus(404)
            ->assertJson(['message' => 'Filter does not exist.']);
    }

    public function test_update_validates_payload(): void
    {
        $u = User::factory()->create();
        $f = PredefinedFilter::factory()->create();

        $this->actingAs($u, 'api')
            ->putJson(route('api.predefined-filters.update', ['id' => $f->id]), []);

        $this->actingAs($u, 'api')
            ->putJson(route('api.predefined-filters.update', ['id' => $f->id]), [])
            ->assertStatus(422)
            ->assertJsonPath('messages.name.0', 'The name field is required.')
            ->assertJsonPath('messages.filter_data.0', 'The filter data field is required.');
    }

    //------DESTROY TESTS------
    public function test_destroy_non_owner_public_requires_destroy_permission()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $f = PredefinedFilter::factory()->create([
            'created_by' => $owner->id,
            'is_public' => 1,
        ]);

        $this->actingAs($other, 'api')
            ->deleteJson("/api/v1/predefinedFilters/{$f->id}")
            ->assertStatus(403);

        $g = $this->grant($other, ['predefinedFilter.delete' => '1']);
        $this->linkGroupFilter($f, $g);

        $this->actingAs($other, 'api')
            ->deleteJson("/api/v1/predefinedFilters/{$f->id}")
            ->assertOk()
            ->assertJson([
                'message' => trans('admin/predefinedFilters/message.delete.success'),
            ]);

        $this->assertSoftDeleted('predefined_filters', ['id' => $f->id]);
    }

    public function test_destroy_404_when_missing()
    {
        $u = User::factory()->create();

        $this->actingAs($u, 'api')
            ->deleteJson('/api/v1/predefinedFilters/999999')
            ->assertStatus(404)
            ->assertJson(['message' => trans('admin/predefinedFilters/message.does_not_exist')]);
    }

    public function test_destroy_owner_private_ok_200()
    {
        $u = User::factory()->create();
        $f = PredefinedFilter::factory()->create(['created_by' => $u->id, 'is_public' => 0, 'filter_data' => [['a' => 'a']]]);

        $this->actingAs($u, 'api')
            ->deleteJson("/api/v1/predefinedFilters/{$f->id}")
            ->assertOk()
            ->assertJson(['message' => trans('admin/predefinedFilters/message.delete.success')]);

        $this->assertSoftDeleted('predefined_filters', ['id' => $f->id]);
    }

    // PermissionStructureTests
    public function test_transform_with_loaded_permission_groups_structure()
    {
        $this->transformer = new PredefinedFiltersTransformer();

        $user = User::factory()->create();
        $this->actingAs($user);

        // Create creator user (could be same as current user or different)
        $creator = User::factory()->create();

        // Create some permission groups (assuming you have a factory)
        $permissionGroup1 = PermissionGroup::factory()->create(['name' => 'Group 1']);
        $permissionGroup2 = PermissionGroup::factory()->create(['name' => 'Group 2']);

        // Create the filter with JSON-encoded filter_data
        $filter = PredefinedFilter::factory()->create([
            'created_by' => $creator->id,
            'filter_data' => json_encode(['foo' => 'bar']),
            'is_public' => true,
            'object_type' => 'test_type',
        ]);

        // Manually set relations
        $filter->setRelation('createdBy', $creator);
        $filter->setRelation('permissionGroups', collect([$permissionGroup1, $permissionGroup2]));

        // Partial mock to stub userHasPermission as false to focus on structure
        $filter = \Mockery::mock($filter)->makePartial();
        $filter->shouldReceive('userHasPermission')->with($user, 'edit')->andReturn(false);
        $filter->shouldReceive('userHasPermission')->with($user, 'delete')->andReturn(false);

        $result = $this->transformer->transformPredefinedFilter($filter);

        // Check main keys exist and types
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('filter_data', $result);
        $this->assertArrayHasKey('is_public', $result);
        $this->assertArrayHasKey('object_type', $result);
        $this->assertArrayHasKey('created_by', $result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('updated_at', $result);
        $this->assertArrayHasKey('deleted_at', $result);
        $this->assertArrayHasKey('groups', $result);
        $this->assertArrayHasKey('available_actions', $result);

        // Validate groups structure
        $this->assertIsArray($result['groups']);
        $this->assertEquals(2, $result['groups']['total']);
        $this->assertCount(2, $result['groups']['rows']);

        $this->assertEquals($permissionGroup1->id, $result['groups']['rows'][0]['id']);
        $this->assertEquals($permissionGroup1->name, $result['groups']['rows'][0]['name']);

        $this->assertEquals($permissionGroup2->id, $result['groups']['rows'][1]['id']);
        $this->assertEquals($permissionGroup2->name, $result['groups']['rows'][1]['name']);

        // Confirm available actions are false (since userHasPermission mocked false and not owner)
        $this->assertFalse($result['available_actions']['update']);
        $this->assertFalse($result['available_actions']['delete']);
    }

    public function test_transform_without_permission_groups_loaded_sets_groups_null()
    {
        $this->transformer = new PredefinedFiltersTransformer();

        $user = User::factory()->create();
        $this->actingAs($user);

        $filter = PredefinedFilter::factory()->create([
            'created_by' => User::factory()->create()->id, // different user
            'filter_data' => json_encode([['foo' => 'bar']]),
        ]);

        $filter->setRelation('createdBy', $filter->created_by ? User::find($filter->created_by) : null);

        $filter = \Mockery::mock($filter)->makePartial();
        $filter->shouldReceive('userHasPermission')->andReturn(false);

        // Intentionally do NOT load permissionGroups relationship

        $result = $this->transformer->transformPredefinedFilter($filter);

        $this->assertNull($result['groups']);
        $this->assertArrayHasKey('available_actions', $result);
        $this->assertFalse($result['available_actions']['update']);
        $this->assertFalse($result['available_actions']['delete']);
    }

    public function test_transform_sets_available_actions_true_for_owner()
    {
        $this->transformer = new PredefinedFiltersTransformer();

        $user = User::factory()->create();
        $this->actingAs($user);

        $filter = PredefinedFilter::factory()->create(['created_by' => $user->id, 'filter_data' => json_encode([['foo' => 'bar']])]);

        $filter->setRelation('createdBy', $user);

        // Make sure userHasPermission returns false, but user is owner
        $filter = \Mockery::mock($filter)->makePartial();
        $filter->shouldReceive('userHasPermission')->andReturn(false);

        $result = $this->transformer->transformPredefinedFilter($filter);

        $this->assertTrue($result['available_actions']['update']);
        $this->assertTrue($result['available_actions']['delete']);
    }

    public function test_transform_formats_dates_correctly()
    {
        $this->transformer = new PredefinedFiltersTransformer();

        $user = User::factory()->create();
        $this->actingAs($user);
        $filter = PredefinedFilter::factory()->create(['created_by' => $user->id, 'filter_data' => json_encode([['foo' => 'bar']])]);

        $filter->setRelation('createdBy', $user);

        $filter = \Mockery::mock($filter)->makePartial();
        $filter->shouldReceive('userHasPermission')->andReturn(false);

        $result = $this->transformer->transformPredefinedFilter($filter);

        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('updated_at', $result);
        $this->assertArrayHasKey('deleted_at', $result);
    }

    //------SELECTLIST TESTS------

    public function test_selectlist()
    {
        $owner = User::factory()->create();
        $grant = $this->grant($owner, ['predefinedFilter.view' => '1', 'predefinedFilter.create' => '1', 'predefinedFilter.edit' => '1']);

        $publicFilterA = PredefinedFilter::factory()->create([
            'name' => 'All coffee machines',
            'created_by' => $owner->id,
            'is_public' => 1,
        ]);

        $publicFilterB = PredefinedFilter::factory()->create([
            'name' => 'Desktops',
            'created_by' => $owner->id,
            'is_public' => 1,
        ]);

        $privateFilterA = PredefinedFilter::factory()->create([
            'name' => 'Laptops',
            'created_by' => $owner->id,
            'is_public' => 0,
        ]);

        $privateFilterB = PredefinedFilter::factory()->create([
            'name' => 'Coffee mugs',
            'created_by' => $owner->id,
            'is_public' => 0,
        ]);

        $this->linkGroupFilter($publicFilterA, $grant);
        $this->linkGroupFilter($publicFilterB, $grant);

        $response = $this->actingAs($owner, 'api')
            ->getJson('/api/v1/predefinedFilters/selectlist');

        $response->assertOk()
            ->assertJsonFragment(['id' => $publicFilterA->id, 'text' => $publicFilterA->name . " (Public)"])
            ->assertJsonFragment(['id' => $publicFilterB->id, 'text' => $publicFilterB->name . " (Public)"])
            ->assertJsonFragment(['id' => $privateFilterA->id, 'text' => $privateFilterA->name . " (Private)"])
            ->assertJsonFragment(['id' => $privateFilterB->id, 'text' => $privateFilterB->name . " (Private)"]);

        $this->assertCount(4, $response->json('results'));
    }

    public function test_selectlist_search()
    {
        $owner = User::factory()->create();
        $grant = $this->grant($owner, ['predefinedFilter.view' => '1']);

        $publicFilterA = PredefinedFilter::factory()->create([
            'name' => 'All coffee machines',
            'created_by' => $owner->id,
            'is_public' => 1,
        ]);

        $publicFilterB = PredefinedFilter::factory()->create([
            'name' => 'Desktops',
            'created_by' => $owner->id,
            'is_public' => 1,
        ]);

        $privateFilterA = PredefinedFilter::factory()->create([
            'name' => 'Laptops',
            'created_by' => $owner->id,
            'is_public' => 0,
        ]);

        $privateFilterB = PredefinedFilter::factory()->create([
            'name' => 'Coffee mugs',
            'created_by' => $owner->id,
            'is_public' => 0,
        ]);

        $this->linkGroupFilter($publicFilterA, $grant);
        $this->linkGroupFilter($publicFilterB, $grant);

        $response = $this->actingAs($owner, 'api')
            ->getJson('/api/v1/predefinedFilters/selectlist?search=coffee&page=1');

        $response->assertOk()
            ->assertJsonFragment(['id' => $publicFilterA->id, 'text' => $publicFilterA->name . " (Public)"])
            ->assertJsonFragment(['id' => $privateFilterB->id, 'text' => $privateFilterB->name . " (Private)"]);

        $this->assertCount(2, $response->json('results'));
    }

    public function test_selectlist_private()
    {
        $owner = User::factory()->create();
        $grant = $this->grant($owner, ['predefinedFilter.view' => '1']);

        $publicFilterA = PredefinedFilter::factory()->create([
            'name' => 'All coffee machines',
            'created_by' => $owner->id,
            'is_public' => 1,
        ]);

        $publicFilterB = PredefinedFilter::factory()->create([
            'name' => 'Desktops',
            'created_by' => $owner->id,
            'is_public' => 1,
        ]);

        $privateFilterA = PredefinedFilter::factory()->create([
            'name' => 'Laptops',
            'created_by' => $owner->id,
            'is_public' => 0,
        ]);

        $privateFilterB = PredefinedFilter::factory()->create([
            'name' => 'Coffee mugs',
            'created_by' => $owner->id,
            'is_public' => 0,
        ]);

        $this->linkGroupFilter($publicFilterA, $grant);
        $this->linkGroupFilter($publicFilterB, $grant);

        $response = $this->actingAs($owner, 'api')
            ->getJson('/api/v1/predefinedFilters/selectlist?search=PRIVATE:&page=1');

        $response->assertOk()
            ->assertJsonFragment(['id' => $privateFilterA->id, 'text' => $privateFilterA->name . " (Private)"])
            ->assertJsonFragment(['id' => $privateFilterB->id, 'text' => $privateFilterB->name . " (Private)"]);

        $this->assertCount(2, $response->json('results'));
    }

    public function test_selectlist_public()
    {
        $owner = User::factory()->create();
        $grant = $this->grant($owner, ['predefinedFilter.view' => '1']);

        $publicFilterA = PredefinedFilter::factory()->create([
            'name' => 'All coffee machines',
            'created_by' => $owner->id,
            'is_public' => 1,
        ]);

        $publicFilterB = PredefinedFilter::factory()->create([
            'name' => 'Desktops',
            'created_by' => $owner->id,
            'is_public' => 1,
        ]);

        $privateFilterA = PredefinedFilter::factory()->create([
            'name' => 'Laptops',
            'created_by' => $owner->id,
            'is_public' => 0,
        ]);

        $privateFilterB = PredefinedFilter::factory()->create([
            'name' => 'Coffee mugs',
            'created_by' => $owner->id,
            'is_public' => 0,
        ]);

        $this->linkGroupFilter($publicFilterA, $grant);
        $this->linkGroupFilter($publicFilterB, $grant);

        $response = $this->actingAs($owner, 'api')
            ->getJson('/api/v1/predefinedFilters/selectlist?search=PUBLIC:&page=1');

        $response->assertOk()
            ->assertJsonFragment(['id' => $publicFilterA->id, 'text' => $publicFilterA->name . " (Public)"])
            ->assertJsonFragment(['id' => $publicFilterB->id, 'text' => $publicFilterB->name . " (Public)"]);

        $this->assertCount(2, $response->json('results'));
    }

    public function test_selectlist_private_search()
    {
        $owner = User::factory()->create();
        $grant = $this->grant($owner, ['predefinedFilter.view' => '1']);

        $publicFilterA = PredefinedFilter::factory()->create([
            'name' => 'All coffee machines',
            'created_by' => $owner->id,
            'is_public' => 1,
        ]);

        $publicFilterB = PredefinedFilter::factory()->create([
            'name' => 'Desktops',
            'created_by' => $owner->id,
            'is_public' => 1,
        ]);

        $privateFilterA = PredefinedFilter::factory()->create([
            'name' => 'Laptops',
            'created_by' => $owner->id,
            'is_public' => 0,
        ]);

        $privateFilterB = PredefinedFilter::factory()->create([
            'name' => 'Coffee mugs',
            'created_by' => $owner->id,
            'is_public' => 0,
        ]);

        $this->linkGroupFilter($publicFilterA, $grant);
        $this->linkGroupFilter($publicFilterB, $grant);

        $response = $this->actingAs($owner, 'api')
            ->getJson('/api/v1/predefinedFilters/selectlist?search=PRIVATE: Laptop&page=1');

        $response->assertOk()
            ->assertJsonFragment(['id' => $privateFilterA->id, 'text' => $privateFilterA->name . " (Private)"]);

        $this->assertCount(1, $response->json('results'));
    }

    public function test_selectlist_public_search()
    {
        $owner = User::factory()->create();
        $grant = $this->grant($owner, ['predefinedFilter.view' => '1']);

        $publicFilterA = PredefinedFilter::factory()->create([
            'name' => 'All coffee machines',
            'created_by' => $owner->id,
            'is_public' => 1,
        ]);

        $publicFilterB = PredefinedFilter::factory()->create([
            'name' => 'Desktops',
            'created_by' => $owner->id,
            'is_public' => 1,
        ]);

        $privateFilterA = PredefinedFilter::factory()->create([
            'name' => 'Laptops',
            'created_by' => $owner->id,
            'is_public' => 0,
        ]);

        $privateFilterB = PredefinedFilter::factory()->create([
            'name' => 'Coffee mugs',
            'created_by' => $owner->id,
            'is_public' => 0,
        ]);

        $this->linkGroupFilter($publicFilterA, $grant);
        $this->linkGroupFilter($publicFilterB, $grant);

        $response = $this->actingAs($owner, 'api')
            ->getJson('/api/v1/predefinedFilters/selectlist?search=PUBLIC: coffee&page=1');

        $response->assertOk()
            ->assertJsonFragment(['id' => $publicFilterA->id, 'text' => $publicFilterA->name . " (Public)"]);

        $this->assertCount(1, $response->json('results'));
    }

}