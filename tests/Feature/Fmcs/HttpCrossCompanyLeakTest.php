<?php

namespace Tests\Feature\Fmcs;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\Department;
use App\Models\License;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

/**
 * End-to-end tenant isolation guard.
 *
 * Every companyable list endpoint is exercised as a NON-SUPERUSER caller
 * scoped to company A, with a matching row present in company B. If any
 * row belonging to company B appears in the response, the test fails and
 * we know a scoping regression is loose in HTTP land, even if the
 * model-level scope tests are still green (e.g. a controller called
 * withoutGlobalScopes, joined bypassed a scope, or used a raw query).
 *
 * Users are covered by tests/Feature/Users/Api/IndexUsersTest;
 * tests/Unit/CompanyScopingTest covers the model-level equivalents. This
 * suite is the outer defense — controller-level and route-level.
 */
class HttpCrossCompanyLeakTest extends TestCase
{
    protected Company $companyA;

    protected Company $companyB;

    protected function setUp(): void
    {
        parent::setUp();
        [$this->companyA, $this->companyB] = Company::factory()->count(2)->create();
    }

    /**
     * Grant a non-superuser caller "view all" permissions for the given
     * resource keys and pin them to company A. Superusers bypass FMCS
     * entirely so we can't use them here; a plain view permission plus
     * company_user pivot to company A is the shape a real customer's
     * non-admin employee has.
     */
    private function callerScopedToCompanyA(array $viewPermissionKeys): User
    {
        $permissions = [];
        foreach ($viewPermissionKeys as $key) {
            $permissions[$key.'.view'] = '1';
        }

        return $this->companyA->users()->save(
            User::factory()->make(['permissions' => json_encode($permissions)])
        );
    }

    private function idsFromResponse(array $rows): array
    {
        return collect($rows)->pluck('id')->all();
    }

    private function assertCompanyBRowHidden(string $endpoint, Model $companyBRow, User $caller, string $rowsKey = 'rows'): void
    {
        $ids = $this->idsFromResponse(
            $this->actingAsForApi($caller)
                ->getJson($endpoint)
                ->assertOk()
                ->json($rowsKey)
        );

        $this->assertNotContains(
            $companyBRow->id,
            $ids,
            'Cross-company leak at '.$endpoint.': company B '.get_class($companyBRow).' id '.$companyBRow->id.' visible to a company A caller.',
        );
    }

    public function test_api_assets_index_hides_other_company_rows(): void
    {
        $companyBAsset = Asset::factory()->for($this->companyB)->create();
        $caller = $this->callerScopedToCompanyA(['assets']);

        $this->settings->enableMultipleFullCompanySupport();
        $this->assertCompanyBRowHidden(route('api.assets.index'), $companyBAsset, $caller);

        $this->settings->enableFloaterMode();
        $this->assertCompanyBRowHidden(route('api.assets.index'), $companyBAsset, $caller);
    }

    public function test_api_accessories_index_hides_other_company_rows(): void
    {
        $companyBAccessory = Accessory::factory()->for($this->companyB)->create();
        $caller = $this->callerScopedToCompanyA(['accessories']);

        $this->settings->enableMultipleFullCompanySupport();
        $this->assertCompanyBRowHidden(route('api.accessories.index'), $companyBAccessory, $caller);

        $this->settings->enableFloaterMode();
        $this->assertCompanyBRowHidden(route('api.accessories.index'), $companyBAccessory, $caller);
    }

    public function test_api_consumables_index_hides_other_company_rows(): void
    {
        $companyBConsumable = Consumable::factory()->for($this->companyB)->create();
        $caller = $this->callerScopedToCompanyA(['consumables']);

        $this->settings->enableMultipleFullCompanySupport();
        $this->assertCompanyBRowHidden(route('api.consumables.index'), $companyBConsumable, $caller);

        $this->settings->enableFloaterMode();
        $this->assertCompanyBRowHidden(route('api.consumables.index'), $companyBConsumable, $caller);
    }

    public function test_api_components_index_hides_other_company_rows(): void
    {
        $companyBComponent = Component::factory()->for($this->companyB)->create();
        $caller = $this->callerScopedToCompanyA(['components']);

        $this->settings->enableMultipleFullCompanySupport();
        $this->assertCompanyBRowHidden(route('api.components.index'), $companyBComponent, $caller);

        $this->settings->enableFloaterMode();
        $this->assertCompanyBRowHidden(route('api.components.index'), $companyBComponent, $caller);
    }

    public function test_api_licenses_index_hides_other_company_rows(): void
    {
        $companyBLicense = License::factory()->for($this->companyB)->create();
        $caller = $this->callerScopedToCompanyA(['licenses']);

        $this->settings->enableMultipleFullCompanySupport();
        $this->assertCompanyBRowHidden(route('api.licenses.index'), $companyBLicense, $caller);

        $this->settings->enableFloaterMode();
        $this->assertCompanyBRowHidden(route('api.licenses.index'), $companyBLicense, $caller);
    }

    public function test_api_locations_index_hides_other_company_rows(): void
    {
        // Location tenant-scoping under FMCS is opt-in via scope_locations_fmcs.
        // With that setting off (the default, and the "locations are shared
        // across tenants" configuration many installs use), the scope
        // intentionally does not fire on the locations table. Enable it here
        // so the cross-company leak assertion below has semantic meaning.
        $companyBLocation = Location::factory()->for($this->companyB)->create();
        $caller = $this->callerScopedToCompanyA(['locations']);

        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
        $this->assertCompanyBRowHidden(route('api.locations.index'), $companyBLocation, $caller);

        $this->settings->enableFloaterMode();
        $this->settings->set(['scope_locations_fmcs' => 1]);
        $this->assertCompanyBRowHidden(route('api.locations.index'), $companyBLocation, $caller);
    }

    public function test_api_departments_index_hides_other_company_rows(): void
    {
        $companyBDepartment = Department::factory()->for($this->companyB)->create();
        $caller = $this->callerScopedToCompanyA(['departments']);

        $this->settings->enableMultipleFullCompanySupport();
        $this->assertCompanyBRowHidden(route('api.departments.index'), $companyBDepartment, $caller);

        $this->settings->enableFloaterMode();
        $this->assertCompanyBRowHidden(route('api.departments.index'), $companyBDepartment, $caller);
    }

    public function test_search_filter_does_not_bypass_scope_via_multi_term_or_clause(): void
    {
        // Pre-fix: the Searchable trait's scopeTextSearch stitched multi-term
        // searches with top-level orWhere clauses. Operator precedence (AND
        // binds tighter than OR) let the second term match rows outside the
        // FMCS scope: `WHERE scope AND col LIKE '%a%' OR col LIKE '%b%'`
        // meant the second LIKE escaped the scope filter. Any scoped caller
        // using the search field could probe for the existence of cross-
        // company rows by name. The fix wraps the free-text branch in a
        // nested where so OR stays inside the AND group with the scope.
        // Note: this covers searchAttributes / searchCustomFields /
        // searchRelations. Models that override advancedTextSearch
        // (Asset, User, Actionlog, AccessoryCheckout, CheckoutRequest)
        // still add top-level joined-column orWhere clauses and keep a
        // narrower residual oracle on multi-term advanced searches.
        // Follow-up PR will refactor those overrides to split joins
        // (outer builder) from where clauses (nested builder).
        $companyBCompany = Company::factory()->create(['name' => 'SECRET_CROSS_COMPANY']);
        $caller = $this->callerScopedToCompanyA(['companies']);

        $this->settings->enableMultipleFullCompanySupport();

        $ids = $this->idsFromResponse(
            $this->actingAsForApi($caller)
                ->getJson(route('api.companies.index', ['search' => 'nothing SECRET_CROSS_COMPANY']))
                ->assertOk()
                ->json('rows')
        );

        $this->assertNotContains(
            $companyBCompany->id,
            $ids,
            'Multi-term search must not leak cross-company rows via the OR clause',
        );
    }

    public function test_api_users_index_hides_other_company_users(): void
    {
        $companyBUser = $this->companyB->users()->save(User::factory()->make());
        $caller = $this->callerScopedToCompanyA(['users']);

        $this->settings->enableMultipleFullCompanySupport();
        $this->assertCompanyBRowHidden(route('api.users.index'), $companyBUser, $caller);

        $this->settings->enableFloaterMode();
        $this->assertCompanyBRowHidden(route('api.users.index'), $companyBUser, $caller);
    }
}
