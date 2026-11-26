<?php

namespace Tests\Feature\ActionLogs;

use App\Models\Actionlog;
use App\Models\Category;
use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

/**
 * Regression coverage for GHSA-mch3-g6rh-gj22.
 *
 * User-item action_logs (item_type = App\Models\User) land with
 * company_id = NULL because the users table has no scalar company_id
 * column (users belong to companies via the company_user pivot). Before
 * the fix, CompanyableScope treated ALL null-company action_logs as
 * globally visible, on the assumption that null-company meant "global
 * config record" (AssetModel, Manufacturer, Category, Statuslabel,
 * etc.). That assumption was wrong for User-item rows, which carry PII
 * in their log_meta diff (email, phone, employee_num, notes, address,
 * jobtitle, ...) written by UserObserver on every profile edit.
 *
 * Result: any FMCS-scoped user with reports.view (or the more granular
 * users.view for a specific target) could read the entire activity
 * trail of every user in every other company on the install.
 *
 * Fix (Company::scopeCompanyablesDirectly): null-company action_logs
 * are now split by item_type.
 *
 *   - Non-User items keep the existing "null is safe" rule. Global
 *     config action_logs (Category create/edit, Manufacturer create,
 *     etc.) remain cross-company visible as they were.
 *   - User-item rows require the viewer to share at least one company
 *     with the target user via the company_user pivot, matching the
 *     same visibility rule the users list itself applies under FMCS.
 */
class ActionlogFmcsUserItemScopeTest extends TestCase
{
    private function makeUserInCompanies(Company ...$companies): User
    {
        // Users use the company_user pivot for membership; there is no
        // scalar users.company_id column, so ->for() / ->hasAttached()
        // factory helpers don't work here. Save through the
        // company->users() relation the same way HttpCrossCompanyLeakTest
        // does, then attach any extra companies afterwards.
        $first = array_shift($companies);
        $user = $first->users()->save(User::factory()->make());
        foreach ($companies as $additional) {
            $user->companies()->attach($additional->id);
        }

        return $user;
    }

    private function makeViewerInCompany(Company $company, array $permissionKeys = ['users.view']): User
    {
        $permissions = [];
        foreach ($permissionKeys as $key) {
            $permissions[$key] = '1';
        }

        return $company->users()->save(
            User::factory()->make(['permissions' => json_encode($permissions)])
        );
    }

    private function seedUserItemActionLog(User $targetUser): Actionlog
    {
        // The write path stays as-is: User-item rows land with
        // company_id = NULL by design (there is no single company_id
        // to assign for a user who may belong to N companies). Force
        // the shape here rather than triggering an observer, so the
        // test doesn't depend on which action_type happens to fire.
        return Actionlog::factory()->create([
            'item_type' => User::class,
            'item_id' => $targetUser->id,
            'company_id' => null,
        ]);
    }

    public function test_strict_fmcs_hides_cross_company_user_action_log(): void
    {
        // The reporter's exact scenario. CompanyA viewer with users.view
        // must not see a User-item action_log for a CompanyB-only user.
        $this->settings->enableMultipleFullCompanySupport();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $viewer = $this->makeViewerInCompany($companyA);
        $targetInB = $this->makeUserInCompanies($companyB);

        $log = $this->seedUserItemActionLog($targetInB);

        $this->actingAs($viewer);

        $visibleIds = Actionlog::query()->pluck('id')->all();

        $this->assertNotContains(
            $log->id,
            $visibleIds,
            'Cross-company User-item action_log leaked to a strict-FMCS viewer.'
        );
    }

    public function test_strict_fmcs_shows_same_company_user_action_log(): void
    {
        // Positive regression: don't over-hide. Same-company viewer with
        // users.view must still see the target user's activity trail.
        $this->settings->enableMultipleFullCompanySupport();
        $company = Company::factory()->create();

        $viewer = $this->makeViewerInCompany($company);
        $targetInSame = $this->makeUserInCompanies($company);

        $log = $this->seedUserItemActionLog($targetInSame);

        $this->actingAs($viewer);

        $visibleIds = Actionlog::query()->pluck('id')->all();

        $this->assertContains(
            $log->id,
            $visibleIds,
            'Same-company User-item action_log was hidden from a viewer who shares a company with the target.'
        );
    }

    public function test_strict_fmcs_shows_action_log_when_viewer_shares_one_of_targets_companies(): void
    {
        // Multi-company overlap. Viewer in {A, B}, target in {B} only.
        // Any shared company should make the row visible.
        $this->settings->enableMultipleFullCompanySupport();
        [$companyA, $companyB, $companyC] = Company::factory()->count(3)->create();

        $viewer = $this->makeViewerInCompany($companyA);
        $viewer->companies()->attach($companyB->id);

        $target = $this->makeUserInCompanies($companyB);
        $log = $this->seedUserItemActionLog($target);

        // Also seed a User in C to confirm C's user isn't leaked.
        $unrelated = $this->makeUserInCompanies($companyC);
        $unrelatedLog = $this->seedUserItemActionLog($unrelated);

        $this->actingAs($viewer);

        $visibleIds = Actionlog::query()->pluck('id')->all();

        $this->assertContains($log->id, $visibleIds, 'Shared-company target action_log should be visible.');
        $this->assertNotContains($unrelatedLog->id, $visibleIds, 'Non-overlapping target action_log should be hidden.');
    }

    public function test_floater_mode_still_hides_cross_company_user_action_log(): void
    {
        // Floater mode makes null-company ITEMS visible to company-scoped
        // callers, but that policy is about admin-authored config records
        // that happen to have no company_id. User rows are null-company
        // for a different reason (pivot-based membership), so the floater
        // relaxation must not turn User-item PII into a cross-company
        // leak.
        $this->settings->enableFloaterMode();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $viewer = $this->makeViewerInCompany($companyA);
        $target = $this->makeUserInCompanies($companyB);

        $log = $this->seedUserItemActionLog($target);

        $this->actingAs($viewer);

        $visibleIds = Actionlog::query()->pluck('id')->all();

        $this->assertNotContains(
            $log->id,
            $visibleIds,
            'Floater mode leaked a cross-company User-item action_log; the pivot-share rule should still gate it.'
        );
    }

    public function test_non_user_null_company_action_log_still_visible_cross_company(): void
    {
        // Regression guard: the fix must not change existing behavior for
        // legit global-config null-company rows (Category / Manufacturer /
        // Statuslabel / etc). Category is used here as a representative;
        // its table has no company_id column so its action_logs land with
        // company_id = NULL, same shape as the User case, but is
        // administrator-authored config rather than user PII.
        $this->settings->enableMultipleFullCompanySupport();
        $companyA = Company::factory()->create();

        $viewer = $this->makeViewerInCompany($companyA);

        $category = Category::factory()->create();
        $log = Actionlog::factory()->create([
            'item_type' => Category::class,
            'item_id' => $category->id,
            'company_id' => null,
        ]);

        $this->actingAs($viewer);

        $visibleIds = Actionlog::query()->pluck('id')->all();

        $this->assertContains(
            $log->id,
            $visibleIds,
            'Non-User null-company action_log (safe global config) should still be visible cross-company under FMCS. Regression check.'
        );
    }

    public function test_no_company_viewer_cannot_see_other_companies_user_action_logs(): void
    {
        // Strict FMCS (floater off) + viewer with no company_user pivot.
        // The empty-companies branch of scopeCompanyablesDirectly was
        // returning whereNull(company_id) for action_logs, which matched
        // every User-item row on the install.
        $this->settings->enableMultipleFullCompanySupport();
        $companyB = Company::factory()->create();

        // Viewer has reports.view, no company membership.
        $viewer = User::factory()->create([
            'permissions' => json_encode(['reports.view' => '1']),
        ]);

        $targetInB = $this->makeUserInCompanies($companyB);
        $log = $this->seedUserItemActionLog($targetInB);

        $this->actingAs($viewer);

        $this->assertNotContains(
            $log->id,
            Actionlog::query()->pluck('id')->all(),
        );
    }

    public function test_no_company_viewer_still_sees_global_config_action_logs(): void
    {
        // Positive regression: Category/Manufacturer/etc. action_logs
        // remain cross-company visible to a no-company viewer. Those are
        // genuinely global config, not tenant data.
        $this->settings->enableMultipleFullCompanySupport();

        $viewer = User::factory()->create([
            'permissions' => json_encode(['reports.view' => '1']),
        ]);

        $category = Category::factory()->create();
        $log = Actionlog::factory()->create([
            'item_type' => Category::class,
            'item_id' => $category->id,
            'company_id' => null,
        ]);

        $this->actingAs($viewer);

        $this->assertContains(
            $log->id,
            Actionlog::query()->pluck('id')->all(),
        );
    }

    public function test_superuser_sees_every_user_item_action_log_regardless_of_company(): void
    {
        // Superusers bypass FMCS entirely (Company::scopeCompanyables
        // short-circuits at the top). This just pins that the User-item
        // gate we added does not accidentally start hiding rows from
        // superusers.
        $this->settings->enableMultipleFullCompanySupport();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $superuser = User::factory()->superuser()->create();
        $targetInA = $this->makeUserInCompanies($companyA);
        $targetInB = $this->makeUserInCompanies($companyB);

        $logA = $this->seedUserItemActionLog($targetInA);
        $logB = $this->seedUserItemActionLog($targetInB);

        $this->actingAs($superuser);

        $visibleIds = Actionlog::query()->pluck('id')->all();

        $this->assertContains($logA->id, $visibleIds);
        $this->assertContains($logB->id, $visibleIds);
    }
}
