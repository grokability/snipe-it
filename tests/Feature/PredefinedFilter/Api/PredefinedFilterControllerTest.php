<?php

namespace Tests\Feature\Api;

use Spatie\FlareClient\Api;
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
    //INDEX TESTS

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
            ->assertExactJson([]);
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

        $this->actingAs($u, 'api')
            ->getJson('/api/v1/predefinedFilters')
            ->assertOk()
            ->assertJsonStructure([['id','name','created_by','is_public']])
            ->assertJsonFragment(['id'=>$viewable->id,'name'=>'A Viewable'])
            ->assertJsonFragment(['id'=>$mine->id,'name'=>'M My Own'])
            ->assertJsonMissing(['id'=>$hidden->id,'name'=>'Z Hidden'])
            ->assertJsonPath('0.name', 'A Viewable');
    }

    //SHOW TESTS

    public function test_show_404_when_missing(): void
    {
        $u = User::factory()->create();
        $this->grant($u, ['predefinedFilter.view'=>'1']);

        $this->actingAs($u, 'api')
            ->getJson('/api/v1/predefinedFilters/999999')
            ->assertStatus(404)
            ->assertJson(['message' => 'admin/predefinedFilters/message.does_not_exist']);
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

    // STORE TESTS
    
    public function test_store_validates_payload()
    {
        $u = User::factory()->create();

        $this->actingAs($u, 'api')
            ->postJson('/api/v1/predefinedFilters', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name','filter_data']);
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

    //UPDATE TESTS

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
            ->assertJson(['message' => 'admin/predefinedFilters/message.does_not_exist']);
    }

    public function test_update_validates_payload(): void
    {
        $u = User::factory()->create();
        $f = PredefinedFilter::factory()->create();

        $this->actingAs($u, 'api')
            ->putJson(route('api.predefined-filters.update', ['id' => $f->id]), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'filter_data']);
    }


    //DESTROY TESTS
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