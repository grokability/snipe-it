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
        $this->attachCanView($viewable, $g);

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
    
    public function test_show_forbidden_without_view_permission(): void //CHECK
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
    public function test_store_validates_payload() //CHECK
    {
        $u = User::factory()->create();

        $this->actingAs($u, 'api')
            ->postJson('/api/v1/predefinedFilters', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name','filter_data']);
    }


    public function test_store_creates_and_sets_owner() //CHECK
    {
        $u = User::factory()->create();

        $this->actingAs($u, 'api')
            ->postJson(route('api.predefined-filters.store'), [
                'name'=>'Neu',
                'filter_data'=>['status_id'=>[1,2]],
                'created_by'=>999,
            ])
        ->assertCreated();
    }
    public function test_store_public_requires_create_permission() //CHECK
    {
        //TODO neue Logik: public speichern erfordert create-Recht
    }

    //UPDATE TESTS

    public function test_update_owner_private_to_public_requires_create() //CHECK
    {
        //TODO
    }
    public function test_update_non_owner_public_requires_update_permission() //CHECK
    {
        //TODO
    }

    public function test_update_404_when_missing() //CHECK
    {
        $u = User::factory()->create();
        $this->grant($u, ['predefinedFilters.edit' => '1']);
        $this->actingAs($u, 'api')
            ->putJson('/api/v1/predefinedFilters/999999', ['name' => 'X', 'filter_data' =>[]])
            ->assertStatus(404)
            ->assertJson(['error' => 'Filter not found']);
    }

    public function test_update_validates_payload() //CHECK
    {
        $u = User::factory()->create();
        $this->grant($u, [
            'predefinedFilter.view'=>'1',
            'predefinedFilter.edit'=>'1',
        ]);

        $this->assertTrue($u->can('edit', PredefinedFilter::class));

        $f = PredefinedFilter::factory()->create();

        $this->actingAs($u, 'api')
            ->putJson(route('api.predefined-filters.update', ['id'=>$f->id]), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name','filter_data']);
    }

    public function test_update_persists_changes_and_keeps_owner() //CHECK
    {
        //TODO Eingeloggter User MIT edit-Recht PUT mit neuem Namen + neuem filter_data + manipuliertem created_by
    }

    //DESTROY TESTS

    public function test_destroy_non_owner_public_requires_destroy_permission() //CHECK
    {
        //TODO Eingeloggter User OHNE delete-Recht DELETE auf bestehenden Filter
    }

    public function test_destroy_404_when_missing() //CHECK
    {
        //TODO Eingeloggter User MIT delete-Recht DELETE auf nicht existierende ID
    }

    public function test_destroy_owner_private_ok_200() //CHECK
    {
        //TODO Eingeloggter User MIT delete-Recht DELETE auf bestehenden Filter
    }
}