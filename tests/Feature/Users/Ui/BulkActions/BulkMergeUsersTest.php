<?php

namespace Tests\Feature\Users\Ui\BulkActions;

use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BulkMergeUsersTest extends TestCase
{
    public function test_requires_delete_permission()
    {
        $target = User::factory()->create();
        $to_merge = User::factory()->create();

        $this->actingAs(User::factory()->editUsers()->create())
            ->post(route('users.merge.save'), [
                'ids_to_merge' => [$to_merge->id],
                'merge_into_id' => $target->id,
            ])
            ->assertForbidden();

        $this->assertNotSoftDeleted($to_merge);
    }

    public function test_non_admin_cannot_merge_admin_into_self()
    {
        $actor = User::factory()->deleteUsers()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($actor)
            ->post(route('users.merge.save'), [
                'ids_to_merge' => [$admin->id],
                'merge_into_id' => $actor->id,
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($admin);
    }

    public function test_non_admin_cannot_merge_superuser_into_self()
    {
        $actor = User::factory()->deleteUsers()->create();
        $superuser = User::factory()->superuser()->create();

        $this->actingAs($actor)
            ->post(route('users.merge.save'), [
                'ids_to_merge' => [$superuser->id],
                'merge_into_id' => $actor->id,
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($superuser);
    }

    public function test_admin_cannot_merge_superuser_into_self()
    {
        $admin = User::factory()->admin()->create();
        $superuser = User::factory()->superuser()->create();

        $this->actingAs($admin)
            ->post(route('users.merge.save'), [
                'ids_to_merge' => [$superuser->id],
                'merge_into_id' => $admin->id,
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($superuser);
    }

    public function test_assets_are_transferred_and_source_user_is_deleted_on_merge()
    {
        $admin = User::factory()->admin()->create();
        $source = User::factory()->create();
        $target = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($source)->create();

        $this->actingAs($admin)
            ->post(route('users.merge.save'), [
                'ids_to_merge' => [$source->id],
                'merge_into_id' => $target->id,
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted($source);
        $this->assertEquals($target->id, $asset->fresh()->assigned_to);
    }

    public function test_merge_does_not_duplicate_shared_company_pivot_rows()
    {
        // Raw insertOrIgnore path should quietly skip the shared company
        $admin = User::factory()->admin()->create();
        $source = User::factory()->withoutCompany()->create();
        $target = User::factory()->withoutCompany()->create();

        $sharedCompany = Company::factory()->create();
        $sourceOnlyCompany = Company::factory()->create();

        DB::table('company_user')->insert([
            ['company_id' => $sharedCompany->id, 'user_id' => $source->id, 'created_at' => now(), 'updated_at' => now()],
            ['company_id' => $sharedCompany->id, 'user_id' => $target->id, 'created_at' => now(), 'updated_at' => now()],
            ['company_id' => $sourceOnlyCompany->id, 'user_id' => $source->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->actingAs($admin)
            ->post(route('users.merge.save'), [
                'ids_to_merge' => [$source->id],
                'merge_into_id' => $target->id,
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted($source);

        // Target ends up with both companies (one brought over from
        // source, one it already had) and exactly one pivot row per
        // company. The duplicate-key path would have aborted the
        // whole merge before this assertion.
        $targetCompanyIds = DB::table('company_user')
            ->where('user_id', $target->id)
            ->pluck('company_id')
            ->sort()
            ->values()
            ->all();
        $expected = collect([$sharedCompany->id, $sourceOnlyCompany->id])->sort()->values()->all();
        $this->assertSame($expected, $targetCompanyIds);

        // Explicit no-dup check on the shared pivot pair.
        $this->assertSame(1, DB::table('company_user')
            ->where('company_id', $sharedCompany->id)
            ->where('user_id', $target->id)
            ->count());
    }

    public function test_merge_does_not_transfer_assets_when_source_is_protected()
    {
        $actor = User::factory()->deleteUsers()->create();
        $admin = User::factory()->admin()->create();
        $asset = Asset::factory()->assignedToUser($admin)->create();

        $this->actingAs($actor)
            ->post(route('users.merge.save'), [
                'ids_to_merge' => [$admin->id],
                'merge_into_id' => $actor->id,
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('error');

        $this->assertEquals($admin->id, $asset->fresh()->assigned_to);
    }


    public function test_scoped_actor_merging_shared_user_does_not_inherit_foreign_company_memberships()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $actor = User::factory()->deleteUsers()->viewUsers()->forCompany($companyA)->create();
        $shared = User::factory()->withoutCompany()->create();

        DB::table('company_user')->insert([
            ['company_id' => $companyA->id, 'user_id' => $shared->id, 'created_at' => now(), 'updated_at' => now()],
            ['company_id' => $companyB->id, 'user_id' => $shared->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->actingAs($actor)
            ->post(route('users.merge.save'), [
                'ids_to_merge' => [$shared->id],
                'merge_into_id' => $actor->id,
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted($shared);

        $actorCompanyIds = DB::table('company_user')
            ->where('user_id', $actor->id)
            ->pluck('company_id')
            ->map(fn($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        $this->assertSame(
            [$companyA->id],
            $actorCompanyIds,
            'Scoped actor must not inherit the foreign-company membership from a shared merged user (GHSA-83xp-vf89-f9gc).',
        );
    }

    public function test_superuser_merging_shared_user_still_carries_every_pivot_membership()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $superuser = User::factory()->superuser()->withoutCompany()->create();
        $target = User::factory()->withoutCompany()->create();
        $source = User::factory()->withoutCompany()->create();

        DB::table('company_user')->insert([
            ['company_id' => $companyA->id, 'user_id' => $source->id, 'created_at' => now(), 'updated_at' => now()],
            ['company_id' => $companyB->id, 'user_id' => $source->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->actingAs($superuser)
            ->post(route('users.merge.save'), [
                'ids_to_merge' => [$source->id],
                'merge_into_id' => $target->id,
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $targetCompanyIds = DB::table('company_user')
            ->where('user_id', $target->id)
            ->pluck('company_id')
            ->map(fn($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        $expected = collect([$companyA->id, $companyB->id])->sort()->values()->all();
        $this->assertSame(
            $expected,
            $targetCompanyIds,
            'Superuser merge must carry every pivot row. The scope filter added for GHSA-83xp-vf89-f9gc is a no-op for superusers.',
        );
    }
}
