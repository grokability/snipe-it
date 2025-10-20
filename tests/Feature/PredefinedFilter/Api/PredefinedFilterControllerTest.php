<?php

namespace Tests\Feature\PredefinedFilter\Api;

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

    private function linkGroupFilter(PredefinedFilter $f,  PermissionGroup $g)
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
        $u     = User::factory()->create();
        $g     = $this->grant($u, ['predefinedFilter.view' => '1']);

        $viewable = PredefinedFilter::factory()->create([
            'name'       => 'A Viewable',
            'created_by' => $owner->id,
            'is_public'  => 1,
        ]);
        $this->linkGroupFilter($viewable, $g);

        $hidden = PredefinedFilter::factory()->create([
            'name'       => 'Z Hidden',
            'created_by' => $owner->id,
            'is_public'  => 0,
        ]);

        $mine = PredefinedFilter::factory()->create([
            'name'       => 'M My Own',
            'created_by' => $u->id,
            'is_public'  => 0,
        ]);

        $response = $this->actingAs($u, 'api')
            ->getJson('/api/v1/predefinedFilters')
            ->assertOk()
            ->assertJsonFragment(['id'=>$viewable->id,'name'=>'A Viewable'])
            ->assertJsonFragment(['id'=>$mine->id,'name'=>'M My Own']);
        $this->assertCount(2, $response->json('rows'));
    }
    
    public function test_index_lists_only_public_linked_or_owned(): void
    {
        $owner = User::factory()->create();
        $user  = User::factory()->create();
        $g = $this->grant($user, ['predefinedFilter.view' => '1']);

        $viewable = PredefinedFilter::factory()->create([
            'name'=>'Viewable Filter','created_by'=>$owner->id,'is_public'=>1,
        ]);
        $this->linkGroupFilter($viewable,$g);

        $hidden = PredefinedFilter::factory()->create([
            'name'=>'Hidden Filter','created_by'=>$owner->id,'is_public'=>0,
        ]);

        $mine = PredefinedFilter::factory()->create([
            'name'=>'My Own','created_by'=>$user->id,'is_public'=>0,
        ]);

        $this->actingAs($user,'api')
            ->getJson('/api/v1/predefinedFilters')
            ->assertOk()
            ->assertJsonFragment(['name'=>'Viewable Filter'])
            ->assertJsonFragment(['name'=>'My Own'])
            ->assertJsonMissing(['name'=>'Hidden Filter']);
    }

    //------SHOW TESTS------

    public function test_show_404_when_missing(): void
    {
        $u = User::factory()->create();
        $this->grant($u, ['predefinedFilter.view'=>'1']);

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
            'is_public'  => 0,
        ]);

        $this->actingAs($u, 'api')
            ->getJson("/api/v1/predefinedFilters/{$f->id}")
            ->assertOk()
            ->assertJsonFragment(['id'=>$f->id,'name'=>$f->name]);
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
            ->assertJsonFragment(['id' => $f->id, 'name'=> $f->name]);
    }

    public function test_show_forbidden_when_private_and_not_owner(): void
    {
        $userWithout = User::factory()->create();
        $owner = User::factory()->create();

        $filter = PredefinedFilter::factory()->create([
            'created_by'=>$owner->id,
            'is_public'=>0,
        ]);

        $this->actingAs($userWithout,'api')
            ->getJson("/api/v1/predefinedFilters/{$filter->id}")
            ->assertStatus(403)
            ->assertJson(['message'=>trans('admin/predefinedFilters/message.show.not_allowed')]);
    }

    public function test_show_non_owner_public_with_view_is_ok(): void
    {
        $owner = User::factory()->create();
        $user  = User::factory()->create();
        $g = $this->grant($user, ['predefinedFilter.view'=>'1']);

        $filter = PredefinedFilter::factory()->create([
            'name'=>'Allowed Public Filter',
            'created_by'=>$owner->id,
            'is_public'=>1,
        ]);
        $this->linkGroupFilter($filter,$g);

        $this->actingAs($user,'api')
            ->getJson("/api/v1/predefinedFilters/{$filter->id}")
            ->assertOk()
            ->assertJsonFragment(['name'=>'Allowed Public Filter']);
    }

    public function test_show_forbidden_for_private_non_owner(): void
    {
        $owner = User::factory()->create();
        $user  = User::factory()->create();

        $filter = PredefinedFilter::factory()->create([
            'name'=>'Private Filter',
            'created_by'=>$owner->id,
            'is_public'=>0,
        ]);

        $this->actingAs($user,'api')
            ->getJson("/api/v1/predefinedFilters/{$filter->id}")
            ->assertStatus(403)
            ->assertJson(['message'=>trans('admin/predefinedFilters/message.show.not_allowed')]);
    }

    public function test_show_returns_404_if_filter_not_found(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user,'api')
            ->getJson('/api/v1/predefinedFilters/404')
            ->assertStatus(404)
            ->assertJson(['message'=>trans('admin/predefinedFilters/message.does_not_exist')]);
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
                'name'        => 'Neu',
                'filter_data' => ['status_id' => [1, 2]],
                'is_public'   => 1,
                'created_by'  => 999,
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
            ->postJson(route('api.predefined-filters.store'),[
                'name'  => 'X',
                'filter_data' => ['a' => 1],
                'is_public' => 1,
            ])
        ->assertStatus(403)
        ->assertJson(['message' => trans('admin/predefinedFilters/message.create.not_allowed')]);
    }
    public function test_store_public_with_create_permission_returns_201(): void
    {
        $user = User::factory()->create();
        $this->grant($user, ['predefinedFilter.create'=>'1']);

        $payload = [
            'name'=>'Test Public Filter',
            'filter_data'=>['status_id'=>[1]],
            'is_public'=>true,
        ];

        $this->actingAs($user,'api')
            ->postJson('/api/v1/predefinedFilters', $payload)
            ->assertCreated()
            ->assertJsonPath('filter_data.name','Test Public Filter')
            ->assertJsonPath('filter_data.is_public', true)
            ->assertJsonPath('filter_data.created_by', $user->id);
    }

    public function test_store_public_without_create_permission_returns_403(): void
    {
        $user = User::factory()->create();

        $payload = [
            'name'=>'Unauthorized Public Filter',
            'filter_data'=>['status_id'=>[1]],
            'is_public'=>true,
        ];

        $this->actingAs($user,'api')
            ->postJson('/api/v1/predefinedFilters', $payload)
            ->assertStatus(403)
            ->assertJson(['message'=>trans('admin/predefinedFilters/message.create.not_allowed')]);
    }

    //------UPDATE TESTS------

    public function test_update_owner_private_to_public_requires_create(): void
    {
        $u = User::factory()->create();
        $f = PredefinedFilter::factory()->create([
            'created_by' => $u->id,
            'is_public'  => 0,
            'name'       => 'Old',
            'filter_data'=> ['a' => 1],
        ]);

        $this->actingAs($u, 'api')
            ->putJson("/api/v1/predefinedFilters/{$f->id}", [
                'name'=>'New','filter_data'=>['a'=>2],'is_public'=>1
            ])
            ->assertStatus(403);

        $this->grant($u, ['predefinedFilter.create' => '1']);
        $u->refresh();

        $this->actingAs($u->fresh(), 'api')
            ->putJson("/api/v1/predefinedFilters/{$f->id}", [
                'name'=>'New','filter_data'=>['a'=>2],'is_public'=>1
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
            'is_public'  => 1,
            'name'       => 'Old',
            'filter_data'=> ['a' => 1],
        ]);

        $this->actingAs($other, 'api')
            ->putJson("/api/v1/predefinedFilters/{$f->id}", [
                'name'=>'X','filter_data'=>['a'=>3],'is_public'=>1
            ])
            ->assertStatus(403);
            
        $g = $this->grant($other, ['predefinedFilter.update'=>'1']);
        $this->linkGroupFilter($f, $g);
            
        $this->actingAs($other, 'api')
            ->putJson("/api/v1/predefinedFilters/{$f->id}", [
                'name'=>'X','filter_data'=>['a'=>3],'is_public'=>1
            ])
        ->assertOk()
        ->assertJsonPath('filter_data.name','X');
    }
    public function test_update_404_when_missing(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u, 'api')
            ->putJson('/api/v1/predefinedFilters/999999', [
                'name' => 'X', 'filter_data' => [], 'is_public' => 0
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
        'is_public'  => 1,
    ]);

    $this->actingAs($other, 'api')
        ->deleteJson("/api/v1/predefinedFilters/{$f->id}")
        ->assertStatus(403);

    $g = $this->grant($other, ['predefinedFilter.destroy' => '1']);
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
            ->assertJson(['message'=>trans('admin/predefinedFilters/message.does_not_exist')]);
    }

    public function test_destroy_owner_private_ok_200() 
    {
    $u = User::factory()->create();
    $f = PredefinedFilter::factory()->create(['created_by'=>$u->id,'is_public'=>0]);

    $this->actingAs($u, 'api')
        ->deleteJson("/api/v1/predefinedFilters/{$f->id}")
        ->assertOk()
        ->assertJson(['message'=>trans('admin/predefinedFilters/message.delete.success')]);

    $this->assertSoftDeleted('predefined_filters', ['id' => $f->id]);
    }
}