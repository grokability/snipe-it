<?php

namespace Tests\Feature\Users\Api;

use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class UserCompanyMembershipTest extends TestCase
{
    public function test_store_with_company_ids_syncs_pivot()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $actor = User::factory()->superuser()->create();

        $response = $this->actingAsForApi($actor)
            ->postJson(route('api.users.store'), [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'username' => 'janedoe_pivot_test',
                'password' => 'secret123456',
                'password_confirmation' => 'secret123456',
                'company_ids' => [$companyA->id, $companyB->id],
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $user = User::where('username', 'janedoe_pivot_test')->firstOrFail();

        $this->assertCount(2, $user->companies, 'User should belong to two companies via pivot');
        $this->assertTrue($user->companies->contains($companyA));
        $this->assertTrue($user->companies->contains($companyB));
    }

    public function test_update_with_company_ids_syncs_pivot()
    {
        [$companyA, $companyB, $companyC] = Company::factory()->count(3)->create();

        $user = User::factory()->create(['company_id' => $companyA->id]);
        $user->companies()->sync([$companyA->id]);

        $actor = User::factory()->superuser()->create();

        $this->actingAsForApi($actor)
            ->patchJson(route('api.users.update', $user), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'company_ids' => [$companyB->id, $companyC->id],
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $user->refresh();
        $this->assertCount(2, $user->companies, 'Pivot should be updated to two companies');
        $this->assertFalse($user->companies->contains($companyA), 'Old company should be removed');
        $this->assertTrue($user->companies->contains($companyB));
        $this->assertTrue($user->companies->contains($companyC));
    }

    public function test_api_response_includes_companies_array()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $user = User::factory()->create(['company_id' => $companyA->id]);
        $user->companies()->sync([$companyA->id, $companyB->id]);

        $actor = User::factory()->superuser()->create();

        $response = $this->actingAsForApi($actor)
            ->getJson(route('api.users.show', $user))
            ->assertOk();

        $companies = $response->json('companies');

        $this->assertIsArray($companies);
        $this->assertCount(2, $companies, 'Response should include both companies');

        $returnedIds = collect($companies)->pluck('id')->all();
        $this->assertContains($companyA->id, $returnedIds);
        $this->assertContains($companyB->id, $returnedIds);
    }

    public function test_api_response_company_entries_include_tag_color()
    {
        $company = Company::factory()->create(['tag_color' => '#ff0000']);
        $user = User::factory()->create(['company_id' => $company->id]);
        $user->companies()->sync([$company->id]);

        $actor = User::factory()->superuser()->create();

        $response = $this->actingAsForApi($actor)
            ->getJson(route('api.users.show', $user))
            ->assertOk();

        $companies = $response->json('companies');

        $this->assertEquals('#ff0000', $companies[0]['tag_color']);
    }

    public function test_multi_company_user_can_see_users_from_all_their_companies_when_fmcs_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB, $companyC] = Company::factory()->count(3)->create();

        $userInA = User::factory()->create(['first_name' => 'Alice', 'last_name' => 'Alpha', 'company_id' => $companyA->id]);
        $companyA->users()->syncWithoutDetaching([$userInA->id]);

        $userInB = User::factory()->create(['first_name' => 'Bob', 'last_name' => 'Beta', 'company_id' => $companyB->id]);
        $companyB->users()->syncWithoutDetaching([$userInB->id]);

        $userInC = User::factory()->create(['first_name' => 'Carol', 'last_name' => 'Gamma', 'company_id' => $companyC->id]);
        $companyC->users()->syncWithoutDetaching([$userInC->id]);

        // Acting user belongs to both A and B.
        $actor = User::factory()->viewUsers()->create(['company_id' => null]);
        $actor->companies()->sync([$companyA->id, $companyB->id]);

        $response = $this->actingAsForApi($actor)
            ->getJson(route('api.users.index'))
            ->assertOk();

        $names = collect($response->json('rows'))->pluck('name');

        $this->assertTrue($names->contains(fn ($n) => str_contains($n, 'Alice')), 'Should see company A user');
        $this->assertTrue($names->contains(fn ($n) => str_contains($n, 'Bob')), 'Should see company B user');
        $this->assertFalse($names->contains(fn ($n) => str_contains($n, 'Carol')), 'Should NOT see company C user');
    }

    public function test_user_with_no_companies_sees_only_unassigned_users_when_fmcs_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();

        $assignedUser = User::factory()->create(['company_id' => $company->id]);
        $company->users()->syncWithoutDetaching([$assignedUser->id]);

        $unassignedUser = User::factory()->create(['company_id' => null]);

        // Actor belongs to no companies.
        $actor = User::factory()->viewUsers()->create(['company_id' => null]);

        $response = $this->actingAsForApi($actor)
            ->getJson(route('api.users.index'))
            ->assertOk();

        $ids = collect($response->json('rows'))->pluck('id');

        $this->assertFalse($ids->contains($assignedUser->id), 'Should not see user assigned to a company');
        $this->assertTrue($ids->contains($unassignedUser->id), 'Should see user with no company');
        $this->assertTrue($ids->contains($actor->id), 'Should see self');
    }

    public function test_patch_with_invalid_company_id_returns_error()
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $user->companies()->sync([$company->id]);

        $actor = User::factory()->superuser()->create();

        $this->actingAsForApi($actor)
            ->patchJson(route('api.users.update', $user), [
                'company_id' => 99999999,
            ])
            ->assertStatus(200)
            ->assertStatusMessageIs('error');

        $user->refresh();
        $this->assertEquals($company->id, $user->company_id, 'company_id should not be changed on invalid input');
    }

    public function test_put_with_invalid_company_id_returns_error()
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        $actor = User::factory()->superuser()->create();

        $this->actingAsForApi($actor)
            ->putJson(route('api.users.update', $user), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'company_id' => 99999999,
            ])
            ->assertStatus(200)
            ->assertStatusMessageIs('error');

        $user->refresh();
        $this->assertEquals($company->id, $user->company_id, 'company_id should not be changed on invalid input');
    }

    public function test_patch_with_invalid_company_ids_returns_error()
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $user->companies()->sync([$company->id]);

        $actor = User::factory()->superuser()->create();

        $this->actingAsForApi($actor)
            ->patchJson(route('api.users.update', $user), [
                'company_ids' => [99999999, 88888888],
            ])
            ->assertStatus(200)
            ->assertStatusMessageIs('error');

        $user->refresh();
        $this->assertCount(1, $user->companies, 'Company pivot should not be changed on invalid input');
        $this->assertTrue($user->companies->contains($company));
    }

    public function test_legacy_company_id_on_update_adds_without_removing_other_associations()
    {
        // An older integration that hasn't been updated still sends company_id (scalar).
        // If the user already belongs to multiple companies via the pivot, the legacy
        // company_id should be added (if not already present) without stripping others.
        [$companyA, $companyB, $companyC] = Company::factory()->count(3)->create();

        $user = User::factory()->create();
        $user->companies()->sync([$companyA->id, $companyB->id]);

        $actor = User::factory()->superuser()->create();

        $this->actingAsForApi($actor)
            ->patchJson(route('api.users.update', $user), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'company_id' => $companyC->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $user->refresh();
        $this->assertCount(3, $user->companies, 'All three companies should be present after legacy company_id update');
        $this->assertTrue($user->companies->contains($companyA), 'companyA should not have been stripped');
        $this->assertTrue($user->companies->contains($companyB), 'companyB should not have been stripped');
        $this->assertTrue($user->companies->contains($companyC), 'companyC should have been added');
    }

    public function test_legacy_company_id_on_update_is_idempotent_when_already_a_member()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $user = User::factory()->create();
        $user->companies()->sync([$companyA->id, $companyB->id]);

        $actor = User::factory()->superuser()->create();

        $this->actingAsForApi($actor)
            ->patchJson(route('api.users.update', $user), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'company_id' => $companyA->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $user->refresh();
        $this->assertCount(2, $user->companies, 'Company count should not change when company_id already in pivot');
    }

    public function test_post_with_invalid_company_ids_returns_error()
    {
        $actor = User::factory()->superuser()->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.users.store'), [
                'first_name' => 'Test',
                'last_name' => 'User',
                'username' => 'testuser_invalid_companies',
                'password' => 'secret123456',
                'password_confirmation' => 'secret123456',
                'company_ids' => [99999999],
            ])
            ->assertStatus(200)
            ->assertStatusMessageIs('error');

        $this->assertNull(User::where('username', 'testuser_invalid_companies')->first());
    }
}
