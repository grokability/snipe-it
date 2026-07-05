<?php

namespace Tests\Feature\Users\Ui;

use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

/**
 * #19200: with floater mode on, a non-superuser with users.edit could blank a
 * user's company assignments and promote that user (often themselves) to a
 * system-wide floater. SaveUserRequest::withValidator and BulkUsersController
 * both refuse the operation; only superusers can intentionally make a floater.
 */
class FloaterModeGateTest extends TestCase
{
    public function test_non_superuser_cannot_blank_their_own_companies_in_floater_mode()
    {
        $this->settings->enableFloaterMode();

        $company = Company::factory()->create();
        $actor = $company->users()->save(User::factory()->editUsers()->create());

        $this->actingAs($actor)
            ->put(route('users.update', $actor), [
                'first_name' => $actor->first_name,
                'username' => $actor->username,
                'company_ids' => [],
            ])
            ->assertSessionHasErrors('company_ids');

        $this->assertContains($company->id, $actor->fresh()->companies->pluck('id')->all(), 'Actor should still belong to original company');
        $this->assertNotEmpty($actor->fresh()->companies, 'Actor pivot should not be wiped');
    }

    public function test_non_superuser_cannot_blank_another_users_companies_in_floater_mode()
    {
        $this->settings->enableFloaterMode();

        $company = Company::factory()->create();
        $actor = $company->users()->save(User::factory()->editUsers()->create());
        $victim = $company->users()->save(User::factory()->create());

        $this->actingAs($actor)
            ->put(route('users.update', $victim), [
                'first_name' => $victim->first_name,
                'username' => $victim->username,
                'company_ids' => [],
            ])
            ->assertSessionHasErrors('company_ids');

        $this->assertContains($company->id, $victim->fresh()->companies->pluck('id')->all());
        $this->assertNotEmpty($victim->fresh()->companies, 'Victim pivot should not be wiped');
    }

    public function test_superuser_can_save_a_user_with_no_companies_in_floater_mode()
    {
        $this->settings->enableFloaterMode();

        $company = Company::factory()->create();
        $superuser = User::factory()->superuser()->create();
        $target = $company->users()->save(User::factory()->create());

        $this->actingAs($superuser)
            ->put(route('users.update', $target), [
                'first_name' => $target->first_name,
                'username' => $target->username,
                'company_ids' => [],
            ])
            ->assertSessionDoesntHaveErrors('company_ids');

        $this->assertEmpty($target->fresh()->companies->pluck('id')->all(), 'Superuser is trusted to grant floater status');
    }

    public function test_guard_does_not_apply_when_floater_mode_is_off()
    {
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();

        $company = Company::factory()->create();
        $actor = $company->users()->save(User::factory()->editUsers()->create());

        $this->actingAs($actor)
            ->put(route('users.update', $actor), [
                'first_name' => $actor->first_name,
                'username' => $actor->username,
                'company_ids' => [],
            ])
            ->assertSessionDoesntHaveErrors('company_ids');
    }

    public function test_non_superuser_cannot_bulk_clear_companies_in_floater_mode()
    {
        $this->settings->enableFloaterMode();

        $company = Company::factory()->create();
        $actor = $company->users()->save(User::factory()->editUsers()->create());
        $victim = $company->users()->save(User::factory()->create());

        $this->actingAs($actor)
            ->post(route('users/bulkeditsave'), [
                'ids' => [$victim->id => '1'],
                'null_company_ids' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertContains($company->id, $victim->fresh()->companies->pluck('id')->all(), 'Bulk clear should be refused');
        $this->assertNotEmpty($victim->fresh()->companies);
    }

    public function test_non_superuser_cannot_create_a_new_user_with_no_companies_in_floater_mode()
    {
        // Covers the web POST path — SaveUserRequest's withValidator fires on
        // both store and update because it's the same FormRequest.
        $this->settings->enableFloaterMode();

        $company = Company::factory()->create();
        $actor = $company->users()->save(User::factory()->createUsers()->viewUsers()->create());

        $this->actingAs($actor)
            ->post(route('users.store'), [
                'first_name' => 'New',
                'username' => 'newfloateruser',
                'password' => 'testpassword1235!!',
                'password_confirmation' => 'testpassword1235!!',
                'company_ids' => [],
            ])
            ->assertSessionHasErrors('company_ids');

        $this->assertDatabaseMissing('users', ['username' => 'newfloateruser']);
    }

    public function test_non_superuser_cannot_create_a_new_user_with_no_companies_via_api_in_floater_mode()
    {
        // Covers the API POST path. Same SaveUserRequest underneath, but
        // worth pinning the JSON entry point separately so the gate isn't
        // accidentally bypassed by a future API rewrite.
        $this->settings->enableFloaterMode();

        $company = Company::factory()->create();
        $actor = $company->users()->save(User::factory()->createUsers()->viewUsers()->create());

        $this->actingAsForApi($actor)
            ->postJson(route('api.users.store'), [
                'first_name' => 'New',
                'username' => 'newfloateruserapi',
                'password' => 'testpassword1235!!',
                'password_confirmation' => 'testpassword1235!!',
                'company_ids' => [],
            ])
            ->assertJsonStructure(['messages' => ['company_ids']]);

        $this->assertDatabaseMissing('users', ['username' => 'newfloateruserapi']);
    }

    public function test_superuser_can_create_a_user_with_no_companies_in_floater_mode()
    {
        // Sanity counterpart to the two preceding tests — confirms the gate
        // only fires for non-superusers, not as an unconditional block on
        // empty-pivot user creation.
        $this->settings->enableFloaterMode();

        $superuser = User::factory()->superuser()->create();

        $this->actingAs($superuser)
            ->post(route('users.store'), [
                'first_name' => 'New',
                'username' => 'superminted',
                'password' => 'testpassword1235!!',
                'password_confirmation' => 'testpassword1235!!',
                'company_ids' => [],
            ])
            ->assertSessionDoesntHaveErrors('company_ids');

        $this->assertDatabaseHas('users', ['username' => 'superminted']);
    }

    public function test_floater_actor_can_save_another_user_with_no_companies()
    {
        // An actor who is themselves uncompanied (already a floater under
        // floater mode) is trusted to manage other floaters — they can't
        // escalate their privileges because they already have none above
        // floater-level. Legitimate use case: an HR / onboarding role that
        // sits outside any specific sub-company and is responsible for
        // creating users across the org (see PR review thread for #19200).
        // Only the *companied → uncompanied* transition by a companied actor
        // is blocked.
        $this->settings->enableFloaterMode();

        $floaterActor = User::factory()->editUsers()->create();
        $floaterActor->companies()->sync([]);
        $target = User::factory()->create();
        $target->companies()->sync([]);

        $this->actingAs($floaterActor)
            ->put(route('users.update', $target), [
                'first_name' => $target->first_name,
                'username' => $target->username,
                'company_ids' => [],
            ])
            ->assertSessionDoesntHaveErrors('company_ids');
    }

    public function test_non_superuser_cannot_assign_only_companies_they_do_not_belong_to()
    {
        // Regression: Company::getIdsForCurrentUser does an array_intersect
        // against the actor's company memberships. A non-superuser actor in
        // companyA who submits company_ids=[companyB.id] would have the
        // submitted IDs silently filtered to [] — flipping the target into
        // floater status without ever clicking "no companies". Pin both the
        // update path (the original report) and the store path.
        $this->settings->enableFloaterMode();

        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $actor = $companyA->users()->save(User::factory()->editUsers()->createUsers()->viewUsers()->create());
        $target = $companyA->users()->save(User::factory()->create());

        $this->actingAs($actor)
            ->put(route('users.update', $target), [
                'first_name' => $target->first_name,
                'username' => $target->username,
                'company_ids' => [$companyB->id],
            ])
            ->assertSessionHasErrors('company_ids');

        $freshIds = $target->fresh()->companies->pluck('id')->all();
        $this->assertContains($companyA->id, $freshIds, 'Update: target should keep companyA — intersect-to-empty was refused');
        $this->assertNotContains($companyB->id, $freshIds);

        $this->actingAs($actor)
            ->post(route('users.store'), [
                'first_name' => 'Intersect',
                'username' => 'intersect-bypass',
                'password' => 'testpassword1235!!',
                'password_confirmation' => 'testpassword1235!!',
                'company_ids' => [$companyB->id],
            ])
            ->assertSessionHasErrors('company_ids');

        // Store: floater user must not be created via intersect-to-empty bypass.
        $this->assertDatabaseMissing('users', ['username' => 'intersect-bypass']);
    }

    public function test_non_superuser_cannot_assign_only_companies_they_do_not_belong_to_via_api()
    {
        // Mirror of the UI regression above for the API surface — both
        // surfaces share SaveUserRequest, so this pins that contract.
        $this->settings->enableFloaterMode();

        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $actor = $companyA->users()->save(User::factory()->editUsers()->createUsers()->viewUsers()->create());
        $target = $companyA->users()->save(User::factory()->create());

        $this->actingAsForApi($actor)
            ->putJson(route('api.users.update', $target), [
                'first_name' => $target->first_name,
                'username' => $target->username,
                'company_ids' => [$companyB->id],
            ])
            ->assertJsonStructure(['messages' => ['company_ids']]);

        $freshIds = $target->fresh()->companies->pluck('id')->all();
        $this->assertContains($companyA->id, $freshIds);
        $this->assertNotContains($companyB->id, $freshIds);

        $this->actingAsForApi($actor)
            ->postJson(route('api.users.store'), [
                'first_name' => 'IntersectApi',
                'username' => 'intersect-bypass-api',
                'password' => 'testpassword1235!!',
                'password_confirmation' => 'testpassword1235!!',
                'company_ids' => [$companyB->id],
            ])
            ->assertJsonStructure(['messages' => ['company_ids']]);

        $this->assertDatabaseMissing('users', ['username' => 'intersect-bypass-api']);
    }

    public function test_non_superuser_can_change_their_companies_to_a_non_empty_set_in_floater_mode()
    {
        $this->settings->enableFloaterMode();

        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $actor = User::factory()->editUsers()->create();
        $actor->companies()->sync([$companyA->id, $companyB->id]);

        // Drop B, keep A — still has memberships, so not becoming a floater.
        $this->actingAs($actor)
            ->put(route('users.update', $actor), [
                'first_name' => $actor->first_name,
                'username' => $actor->username,
                'company_ids' => [$companyA->id],
            ])
            ->assertSessionDoesntHaveErrors('company_ids');

        $freshIds = $actor->fresh()->companies->pluck('id')->all();
        $this->assertContains($companyA->id, $freshIds);
        $this->assertNotContains($companyB->id, $freshIds);
    }
}
