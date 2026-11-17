<?php

namespace Tests\Feature\Fmcs;

use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class FmcsManagerTargetCompatTest extends TestCase
{
    public function test_multi_company_admin_cannot_assign_cross_company_manager_in_strict_mode(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $actor = User::factory()->create();
        $actor->companies()->sync([$companyA->id, $companyB->id]);
        $this->actingAs($actor);

        $target = User::factory()->create();
        $target->companies()->sync([$companyB->id]);
        $manager = User::factory()->create();
        $manager->companies()->sync([$companyA->id]);

        $target->manager_id = $manager->id;

        $this->assertFalse(
            $target->save(),
            'Target-compat must block a cross-company manager even when the actor can see both tenants.',
        );
        $this->assertArrayHasKey('manager_id', $target->getErrors()->toArray());
    }

    public function test_same_company_manager_assignment_passes_in_strict_mode(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        $company = Company::factory()->create();

        $actor = User::factory()->create();
        $actor->companies()->sync([$company->id]);
        $this->actingAs($actor);

        $target = User::factory()->create();
        $target->companies()->sync([$company->id]);
        $manager = User::factory()->create();
        $manager->companies()->sync([$company->id]);

        $target->manager_id = $manager->id;

        $this->assertTrue($target->save(), 'Same-company manager must pass target-compat.');
    }

    public function test_pivot_overlap_allows_manager_assignment_in_strict_mode(): void
    {
        // User pivot: manager in A+B, target in B+C. The
        // overlap on B is enough to satisfy the invariant.
        $this->settings->enableMultipleFullCompanySupport();
        [$companyA, $companyB, $companyC] = Company::factory()->count(3)->create();

        $actor = User::factory()->superuser()->create();
        $this->actingAs($actor);

        $target = User::factory()->create();
        $target->companies()->sync([$companyB->id, $companyC->id]);
        $manager = User::factory()->create();
        $manager->companies()->sync([$companyA->id, $companyB->id]);

        $target->manager_id = $manager->id;

        $this->assertTrue(
            $target->save(),
            'A manager whose pivot overlaps the target on at least one company must be allowed.',
        );
    }

    public function test_child_company_manager_allowed_for_parent_company_target(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        $parent = Company::factory()->create();
        $child = Company::factory()->create(['parent_id' => $parent->id]);

        $actor = User::factory()->create();
        $actor->companies()->sync([$parent->id, $child->id]);
        $this->actingAs($actor);

        $target = User::factory()->create();
        $target->companies()->sync([$parent->id]);
        $manager = User::factory()->create();
        $manager->companies()->sync([$child->id]);

        $target->manager_id = $manager->id;

        $this->assertTrue(
            $target->save(),
            'A child-company manager must be allowed for a parent-company target under the hierarchy expansion.',
        );
    }

    public function test_null_company_manager_allowed_on_companied_target_when_floater_is_on(): void
    {
        $this->settings->enableFloaterMode();
        $company = Company::factory()->create();

        $actor = User::factory()->create();
        $actor->companies()->sync([$company->id]);
        $this->actingAs($actor);

        $target = User::factory()->create();
        $target->companies()->sync([$company->id]);
        // withoutCompany() suppresses the default UserFactory pivot
        // attach so the manager is genuinely pivot-less, which is the
        // "null-company user" the floater branch is supposed to cover.
        $manager = User::factory()->withoutCompany()->create();

        $target->manager_id = $manager->id;

        $this->assertTrue(
            $target->save(),
            'A null-company manager must be allowed against a companied target when floater mode is on.',
        );
    }

    public function test_null_company_target_accepts_companied_manager_when_floater_is_on(): void
    {
        $this->settings->enableFloaterMode();
        $company = Company::factory()->create();

        $actor = User::factory()->create();
        $actor->companies()->sync([$company->id]);
        $this->actingAs($actor);

        $target = User::factory()->withoutCompany()->create();
        $manager = User::factory()->create();
        $manager->companies()->sync([$company->id]);

        $target->manager_id = $manager->id;

        $this->assertTrue(
            $target->save(),
            'A null-company target must accept a companied manager when floater mode is on.',
        );
    }

    public function test_null_company_manager_blocked_when_floater_is_off(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();
        $company = Company::factory()->create();

        $actor = User::factory()->create();
        $actor->companies()->sync([$company->id]);
        $this->actingAs($actor);

        $target = User::factory()->create();
        $target->companies()->sync([$company->id]);
        $manager = User::factory()->withoutCompany()->create();

        $target->manager_id = $manager->id;

        $this->assertFalse(
            $target->save(),
            'Strict mode must reject a null-company manager against a companied target.',
        );
        $this->assertArrayHasKey('manager_id', $target->getErrors()->toArray());
    }

    public function test_cross_company_manager_rejected_under_scoped_locations_mode(): void
    {
        // Location-scoping doesn't touch manager assignment directly,
        // but the rule must still enforce cross-company rejection when
        // the deployment also has scope_locations_fmcs on.
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $actor = User::factory()->create();
        $actor->companies()->sync([$companyA->id, $companyB->id]);
        $this->actingAs($actor);

        $target = User::factory()->create();
        $target->companies()->sync([$companyB->id]);
        $manager = User::factory()->create();
        $manager->companies()->sync([$companyA->id]);

        $target->manager_id = $manager->id;

        $this->assertFalse(
            $target->save(),
            'Cross-company manager must stay rejected when scoped locations mode is also on.',
        );
    }

    public function test_cli_save_without_auth_bypasses_target_compat(): void
    {
        // Matches the sibling fmcs_* rules: LDAP sync, importer,
        // seeders, queue workers have no FMCS scope to measure against
        // and the CLI trust boundary authorizes those writes.
        $this->settings->enableMultipleFullCompanySupport();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $target = User::factory()->create();
        $target->companies()->sync([$companyB->id]);
        $manager = User::factory()->create();
        $manager->companies()->sync([$companyA->id]);

        // No actingAs call. auth()->check() is false.
        $target->manager_id = $manager->id;

        $this->assertTrue(
            $target->save(),
            'CLI (no-auth) save must bypass target-compat so LDAP sync and importers stay functional.',
        );
    }

    public function test_superuser_bypasses_target_compat(): void
    {
        // House convention for validator-level FMCS rules: superusers
        // pass. Matches the fmcs_manager / fmcs_department / fmcs_location
        // bypass branches and the existing superuser test in
        // FmcsModelRuleCoverageTest.
        $this->settings->enableMultipleFullCompanySupport();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $actor = User::factory()->superuser()->create();
        $this->actingAs($actor);

        $target = User::factory()->create();
        $target->companies()->sync([$companyB->id]);
        $manager = User::factory()->create();
        $manager->companies()->sync([$companyA->id]);

        $target->manager_id = $manager->id;

        $this->assertTrue(
            $target->save(),
            'Superuser must not be blocked by the target-compat rule.',
        );
    }

    public function test_null_manager_id_write_passes_target_compat(): void
    {
        // Clearing a manager is a legitimate workflow (user departs,
        // reorg). The rule early-returns true on empty values.
        $this->settings->enableMultipleFullCompanySupport();
        $company = Company::factory()->create();

        $actor = User::factory()->create();
        $actor->companies()->sync([$company->id]);
        $this->actingAs($actor);

        $manager = User::factory()->create();
        $manager->companies()->sync([$company->id]);
        $target = User::factory()->create(['manager_id' => $manager->id]);
        $target->companies()->sync([$company->id]);

        $target->manager_id = null;

        $this->assertTrue($target->save(), 'Null manager_id writes must pass target-compat.');
    }
}
