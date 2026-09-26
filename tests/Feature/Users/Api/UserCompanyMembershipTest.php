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

        $user = User::factory()->forCompany($companyA->id)->create();
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

        $user = User::factory()->forCompany($companyA->id)->create();
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
        $user = User::factory()->forCompany($company->id)->create();
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

        $userInA = User::factory()->forCompany($companyA)->create(['first_name' => 'Alice', 'last_name' => 'Alpha']);
        $userInB = User::factory()->forCompany($companyB)->create(['first_name' => 'Bob', 'last_name' => 'Beta']);
        $userInC = User::factory()->forCompany($companyC)->create(['first_name' => 'Carol', 'last_name' => 'Gamma']);

        // Acting user belongs to both A and B.
        $actor = User::factory()->viewUsers()->withoutCompany()->create();
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

        $assignedUser = User::factory()->forCompany($company->id)->create();
        $company->users()->syncWithoutDetaching([$assignedUser->id]);

        $unassignedUser = User::factory()->withoutCompany()->create();

        // Actor belongs to no companies.
        $actor = User::factory()->viewUsers()->withoutCompany()->create();

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
        $user = User::factory()->forCompany($company->id)->create();
        $user->companies()->sync([$company->id]);

        $actor = User::factory()->superuser()->create();

        $this->actingAsForApi($actor)
            ->patchJson(route('api.users.update', $user), [
                'company_id' => 99_999_999,
            ])
            ->assertStatus(200)
            ->assertStatusMessageIs('error');

        $user->refresh();
        $this->assertEquals($company->id, $user->legacy_company_id, 'legacy_company_id mirror should not be changed on invalid input');
    }

    public function test_put_with_invalid_company_id_returns_error()
    {
        $company = Company::factory()->create();
        $user = User::factory()->forCompany($company->id)->create();

        $actor = User::factory()->superuser()->create();

        $this->actingAsForApi($actor)
            ->putJson(route('api.users.update', $user), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'company_id' => 99_999_999,
            ])
            ->assertStatus(200)
            ->assertStatusMessageIs('error');

        $user->refresh();
        $this->assertEquals($company->id, $user->legacy_company_id, 'legacy_company_id mirror should not be changed on invalid input');
    }

    public function test_patch_with_invalid_company_ids_returns_error()
    {
        $company = Company::factory()->create();
        $user = User::factory()->forCompany($company->id)->create();
        $user->companies()->sync([$company->id]);

        $actor = User::factory()->superuser()->create();

        $this->actingAsForApi($actor)
            ->patchJson(route('api.users.update', $user), [
                'company_ids' => [99_999_999, 88_888_888],
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
                'company_ids' => [99_999_999],
            ])
            ->assertStatus(200)
            ->assertStatusMessageIs('error');

        $this->assertNull(User::where('username', 'testuser_invalid_companies')->first());
    }

    public function test_store_denies_mixed_cross_tenant_company_ids_from_non_superuser()
    {
        // Regression for Christopher Finks / Issue 2. A non-superuser with
        // users.create who is a member of Company A only cannot create a
        // user whose company_ids[] includes any company outside the actor's
        // permitted scope. This exercises the mixed case (some permitted,
        // some not) where SaveUserRequest::withValidator() does not fire
        // its "cannot make floater" gate because the filter still returns
        // a non-empty set. Prior behavior filled and saved the user, then
        // silently dropped the foreign company id at sync time - leaving
        // a user record whose eventual pivot did not match the request.
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = User::factory()->createUsers()->forCompany($companyA->id)->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.users.store'), [
                'first_name' => 'Cross',
                'last_name' => 'Tenant',
                'username' => 'cross_tenant_mixed',
                'password' => 'secret123456',
                'password_confirmation' => 'secret123456',
                'company_ids' => [$companyA->id, $companyB->id],
            ])
            ->assertStatus(403);

        $this->assertNull(User::where('username', 'cross_tenant_mixed')->first(), 'User must not be persisted when any requested company is outside the actor scope.');
    }

    public function test_update_denies_mixed_cross_tenant_company_ids_from_non_superuser()
    {
        // Same regression as above but on the update path. Actor edits an
        // existing user's company_ids to include a foreign company alongside
        // their own permitted one - must be rejected before any pivot write.
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $target = User::factory()->forCompany($companyA->id)->create();
        $target->companies()->sync([$companyA->id]);
        $actor = User::factory()->editUsers()->forCompany($companyA->id)->create();

        $this->actingAsForApi($actor)
            ->patchJson(route('api.users.update', $target), [
                'first_name' => $target->first_name,
                'last_name' => $target->last_name,
                'username' => $target->username,
                'company_ids' => [$companyA->id, $companyB->id],
            ])
            ->assertStatus(403);

        $target->refresh();
        $this->assertFalse($target->companies->contains($companyB), 'Cross-tenant company must not be attached on update.');
        $this->assertTrue($target->companies->contains($companyA), 'Existing legitimate company assignment must survive the rejection.');
    }

    public function test_store_scalar_cross_tenant_company_id_rejected_by_validation()
    {
        // Scalar company_id case with a single foreign company id is
        // rejected earlier by SaveUserRequest::withValidator() (the
        // "cannot make floater" gate fires because the permitted-id filter
        // returns empty). Kept here as a coverage marker so this path does
        // not regress and someone does not later remove the validator gate
        // thinking the controller alone protects the endpoint.
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = User::factory()->createUsers()->forCompany($companyA->id)->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.users.store'), [
                'first_name' => 'Cross',
                'last_name' => 'Tenant',
                'username' => 'cross_tenant_scalar',
                'password' => 'secret123456',
                'password_confirmation' => 'secret123456',
                'company_id' => $companyB->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');

        $this->assertNull(User::where('username', 'cross_tenant_scalar')->first());
    }

    public function test_store_allows_non_superuser_to_assign_own_company()
    {
        // Sanity: the fix should not break the legitimate case of a
        // non-superuser creating a user in their own company.
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $actor = User::factory()->createUsers()->forCompany($company->id)->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.users.store'), [
                'first_name' => 'Same',
                'last_name' => 'Tenant',
                'username' => 'same_tenant_ok',
                'password' => 'secret123456',
                'password_confirmation' => 'secret123456',
                'company_id' => $company->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $user = User::where('username', 'same_tenant_ok')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->companies->contains($company));
    }
}
