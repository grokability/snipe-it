<?php

namespace Tests\Feature\Reporting\Custom;

use App\Events\CheckoutableCheckedOut;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Company;
use App\Models\Component;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('custom-reporting')]
class CustomComponentReportTest extends TestCase
{
    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actor = User::factory()->canViewReports()->create();
    }

    public function test_requires_permission_to_view_page()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('reports.custom.component'))
            ->assertForbidden();
    }

    public function test_requires_permission_to_run_report()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('reports.custom.component.run'), [
                //
            ])
            ->assertForbidden();
    }

    public function test_can_load_custom_report_page()
    {
        $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('reports.custom.component'))
            ->assertOk();
    }

    public function test_custom_component_report_validation()
    {
        // Invalid date formats are rejected
        $this->sendRequest([
            'purchase_start' => 'not-a-date',
            'purchase_end' => 'not-a-date',
            'checkout_date_start' => 'not-a-date',
            'checkout_date_end' => 'not-a-date',
            'created_start' => 'not-a-date',
            'created_end' => 'not-a-date',
            'last_updated_start' => 'not-a-date',
            'last_updated_end' => 'not-a-date',
        ])->assertSessionHasErrors([
            'purchase_start', 'purchase_end',
            'checkout_date_start', 'checkout_date_end',
            'created_start', 'created_end',
            'last_updated_start', 'last_updated_end',
        ]);

        // End date must be on or after start date
        $this->sendRequest([
            'purchase_start' => '2024-12-31',
            'purchase_end' => '2024-01-01',
            'checkout_date_start' => '2024-12-31',
            'checkout_date_end' => '2024-01-01',
            'created_start' => '2024-12-31',
            'created_end' => '2024-01-01',
            'last_updated_start' => '2024-12-31',
            'last_updated_end' => '2024-01-01',
        ])->assertSessionHasErrors([
            'purchase_end', 'checkout_date_end', 'created_end', 'last_updated_end',
        ]);

        // Non-numeric values are rejected, and last_updated_before must be an integer
        $this->sendRequest([
            'quantity_start' => 'abc',
            'quantity_end' => 'abc',
            'min_quantity_start' => 'abc',
            'min_quantity_end' => 'abc',
            'unit_cost_start' => 'abc',
            'unit_cost_end' => 'abc',
            'last_updated_before' => 'not-an-integer',
        ])->assertSessionHasErrors([
            'quantity_start', 'quantity_end',
            'min_quantity_start', 'min_quantity_end',
            'unit_cost_start', 'unit_cost_end',
            'last_updated_before',
        ]);

        // End must be >= start for numeric ranges
        $this->sendRequest([
            'quantity_start' => 10, 'quantity_end' => 1,
            'min_quantity_start' => 10, 'min_quantity_end' => 1,
            'unit_cost_start' => 100, 'unit_cost_end' => 1,
        ])->assertSessionHasErrors(['quantity_end', 'min_quantity_end', 'unit_cost_end']);
    }

    public function test_custom_component_report_headers()
    {
        $this->sendRequest([
            'id' => '1',
            'company' => '1',
            'category' => '1',
            'component_name' => '1',
            'manufacturer' => '1',
            'model' => '1',
            'serial' => '1',
            'purchase_date' => '1',
            'quantity' => '1',
            'min_amount' => '1',
            'unit_cost' => '1',
            'order' => '1',
            'supplier' => '1',
            'location' => '1',
            'location_address' => '1',
            'checkout_date' => '1',
            'created_at' => '1',
            'updated_at' => '1',
            'deleted_at' => '1',
            'notes' => '1',
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse([
                trans('general.id'),
                trans('general.company'),
                trans('general.category'),
                trans('admin/components/general.component_name'),
                trans('general.manufacturer'),
                trans('general.model_no'),
                trans('general.serial_number'),
                trans('general.purchase_date'),
                trans('general.quantity'),
                trans('general.min_amt'),
                trans('general.unit_cost'),
                trans('admin/hardware/form.order'),
                trans('general.supplier'),
                trans('general.location'),
                trans('general.address'),
                trans('general.city'),
                trans('general.state'),
                trans('general.country'),
                trans('general.zip'),
                trans('general.created_at'),
                trans('general.updated_at'),
                trans('general.deleted'),
                trans('general.notes'),
            ]);
    }

    public function test_omitted_columns_are_excluded_from_report_headers()
    {
        $this->sendRequest([
            'id' => '1',
            'component_name' => '1',
            // company and category intentionally omitted
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertDontSeeTextInStreamedResponse([
                trans('general.company'),
                trans('general.category'),
            ]);
    }

    public function test_limiting_by_company()
    {
        [$companyA, $companyB] = Company::factory()
            ->count(2)
            ->sequence(
                ['name' => 'Company A'],
                ['name' => 'Company B'],
            )
            ->create()
            ->all();

        Component::factory()
            ->count(2)
            ->sequence(
                ['company_id' => $companyA->id, 'name' => 'Component for Company A'],
                ['company_id' => $companyB->id, 'name' => 'Component for Company B'],
            )
            ->create();

        $this->sendRequest([
            'component_name' => '1',
            'company' => '1',
            'by_company_id' => [
                $companyA->id,
            ],
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeePairsInStreamedResponse(['Company' => 'Company A', 'Component Name' => 'Component for Company A'])
            ->assertDontSeeTextInStreamedResponse('Component for Company B');
    }

    public function test_limiting_by_category()
    {
        [$categoryA, $categoryB] = Category::factory()
            ->count(2)
            ->sequence(
                ['name' => 'Category A'],
                ['name' => 'Category B'],
            )
            ->create()
            ->all();

        Component::factory()
            ->count(2)
            ->sequence(
                ['category_id' => $categoryA->id, 'name' => 'Component for Category A'],
                ['category_id' => $categoryB->id, 'name' => 'Component for Category B'],
            )
            ->create();

        $this->sendRequest([
            'component_name' => '1',
            'category' => '1',
            'by_category_id' => [
                $categoryA->id,
            ],
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeePairsInStreamedResponse(['Category' => 'Category A', 'Component Name' => 'Component for Category A'])
            ->assertDontSeeTextInStreamedResponse('Component for Category B');
    }

    public function test_limiting_by_manufacturer()
    {
        [$manufacturerA, $manufacturerB] = Manufacturer::factory()
            ->count(2)
            ->sequence(
                ['name' => 'Manufacturer A'],
                ['name' => 'Manufacturer B'],
            )
            ->create()
            ->all();

        Component::factory()
            ->count(2)
            ->sequence(
                ['manufacturer_id' => $manufacturerA->id, 'name' => 'Component for Manufacturer A'],
                ['manufacturer_id' => $manufacturerB->id, 'name' => 'Component for Manufacturer B'],
            )
            ->create();

        $this->sendRequest([
            'component_name' => '1',
            'manufacturer' => '1',
            'by_manufacturer_id' => [
                $manufacturerA->id,
            ],
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeePairsInStreamedResponse(['Manufacturer' => 'Manufacturer A', 'Component Name' => 'Component for Manufacturer A'])
            ->assertDontSeeTextInStreamedResponse('Component for Manufacturer B');
    }

    public function test_limiting_by_supplier()
    {
        [$supplierA, $supplierB] = Supplier::factory()
            ->count(2)
            ->sequence(
                ['name' => 'Supplier A'],
                ['name' => 'Supplier B'],
            )
            ->create()
            ->all();

        Component::factory()
            ->count(2)
            ->sequence(
                ['supplier_id' => $supplierA->id, 'name' => 'Component for Supplier A'],
                ['supplier_id' => $supplierB->id, 'name' => 'Component for Supplier B'],
            )
            ->create();

        $this->sendRequest([
            'component_name' => '1',
            'supplier' => '1',
            'by_supplier_id' => [
                $supplierA->id,
            ],
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeePairsInStreamedResponse(['Supplier' => 'Supplier A', 'Component Name' => 'Component for Supplier A'])
            ->assertDontSeeTextInStreamedResponse('Component for Supplier B');
    }

    public function test_limiting_by_location()
    {
        [$locationA, $locationB] = Location::factory()
            ->count(2)
            ->sequence(
                ['name' => 'Location A'],
                ['name' => 'Location B'],
            )
            ->create()
            ->all();

        Component::factory()
            ->count(2)
            ->sequence(
                ['location_id' => $locationA->id, 'name' => 'Component for Location A'],
                ['location_id' => $locationB->id, 'name' => 'Component for Location B'],
            )
            ->create();

        $this->sendRequest([
            'component_name' => '1',
            'location' => '1',
            'by_location_id' => [
                $locationA->id,
            ],
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeePairsInStreamedResponse(['Location' => 'Location A', 'Component Name' => 'Component for Location A'])
            ->assertDontSeeTextInStreamedResponse('Component for Location B');
    }

    public function test_limiting_by_name()
    {
        Component::factory()->create(['name' => 'RAM']);
        Component::factory()->create(['name' => 'Hard Drive']);

        $this->sendRequest([
            'component_name' => '1',
            'by_name' => 'RAM',
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeePairsInStreamedResponse(['Component Name' => 'RAM'])
            ->assertDontSeeTextInStreamedResponse('Hard Drive');
    }

    public function test_limiting_by_model_number()
    {
        Component::factory()->create(['name' => 'Component A', 'model_number' => 'MODEL-001']);
        Component::factory()->create(['name' => 'Component B', 'model_number' => 'MODEL-002']);

        $this->sendRequest([
            'component_name' => '1',
            'model' => '1',
            'by_model_number' => 'MODEL-001',
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeePairsInStreamedResponse(['Model No.' => 'MODEL-001', 'Component Name' => 'Component A'])
            ->assertDontSeeTextInStreamedResponse('MODEL-002');
    }

    public function test_limiting_by_order_number()
    {
        Component::factory()->create(['name' => 'Component A', 'order_number' => 'ORD-001']);
        Component::factory()->create(['name' => 'Component B', 'order_number' => 'ORD-002']);

        $this->sendRequest([
            'component_name' => '1',
            'order' => '1',
            'by_order_number' => 'ORD-001',
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeePairsInStreamedResponse(['Order Number' => 'ORD-001', 'Component Name' => 'Component A'])
            ->assertDontSeeTextInStreamedResponse('ORD-002');
    }

    public function test_limiting_by_purchase_date_range()
    {
        Component::factory()->create(['name' => 'Component A', 'purchase_date' => '2024-01-15']);
        Component::factory()->create(['name' => 'Component B', 'purchase_date' => '2024-06-15']);

        $this->sendRequest([
            'component_name' => '1',
            'purchase_start' => '2024-01-01',
            'purchase_end' => '2024-03-31',
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse('Component A')
            ->assertDontSeeTextInStreamedResponse('Component B');
    }

    public function test_limiting_by_quantity_range()
    {
        Component::factory()->create(['name' => 'Component A', 'qty' => 5]);
        Component::factory()->create(['name' => 'Component B', 'qty' => 50]);

        $this->sendRequest([
            'component_name' => '1',
            'quantity_start' => 1,
            'quantity_end' => 10,
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse('Component A')
            ->assertDontSeeTextInStreamedResponse('Component B');
    }

    public function test_limiting_by_minimum_quantity_range()
    {
        Component::factory()->create(['name' => 'Component A', 'min_amt' => 2]);
        Component::factory()->create(['name' => 'Component B', 'min_amt' => 20]);

        $this->sendRequest([
            'component_name' => '1',
            'min_quantity_start' => 1,
            'min_quantity_end' => 5,
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse('Component A')
            ->assertDontSeeTextInStreamedResponse('Component B');
    }

    public function test_limiting_by_unit_cost_range()
    {
        Component::factory()->create(['name' => 'Component A', 'purchase_cost' => 10.00]);
        Component::factory()->create(['name' => 'Component B', 'purchase_cost' => 500.00]);

        $this->sendRequest([
            'component_name' => '1',
            'unit_cost_start' => 1,
            'unit_cost_end' => 50,
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse('Component A')
            ->assertDontSeeTextInStreamedResponse('Component B');
    }

    public function test_limiting_by_checkout_date_range()
    {
        $user = User::factory()->create();
        [$assetA, $assetB] = Asset::factory()->count(2)->create()->all();

        $componentA = Component::factory()->checkedOutToAsset($assetA)->create(['name' => 'Component A']);
        $componentB = Component::factory()->checkedOutToAsset($assetB)->create(['name' => 'Component B']);

        // we'll time travel a bit when firing these events to set the action log entries for testing.
        $this->travel(-30)->days(
            fn () => event(new CheckoutableCheckedOut(
                $componentA,
                $assetA,
                $user,
                '',
                [],
                1,
            ))
        );

        $this->travel(-15)->days(
            fn () => event(new CheckoutableCheckedOut(
                $componentB,
                $assetB,
                $user,
                '',
                [],
                1,
            ))
        );

        $this->sendRequest([
            'component_name' => '1',
            'checkout_date_start' => now()->subDays(45)->toDateString(),
            'checkout_date_end' => now()->subDays(20)->toDateString(),
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse('Component A')
            ->assertDontSeeTextInStreamedResponse('Component B');
    }

    public function test_limiting_by_created_at_range()
    {
        $this->travel(-60)->days(function () {
            Component::factory()->create(['name' => 'Component A']);
        });

        Component::factory()->create(['name' => 'Component B']);

        $this->sendRequest([
            'component_name' => '1',
            'created_start' => now()->subDays(90)->toDateString(),
            'created_end' => now()->subDays(30)->toDateString(),
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse('Component A')
            ->assertDontSeeTextInStreamedResponse('Component B');
    }

    public function test_limiting_by_updated_at_range()
    {
        $this->travel(-60)->days(function () {
            Component::factory()->create(['name' => 'Component A']);
        });

        Component::factory()->create(['name' => 'Component B']);

        $this->sendRequest([
            'component_name' => '1',
            'last_updated_start' => now()->subDays(90)->toDateString(),
            'last_updated_end' => now()->subDays(30)->toDateString(),
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse('Component A')
            ->assertDontSeeTextInStreamedResponse('Component B');
    }

    public function test_limiting_by_updated_before()
    {
        $this->travel(-60)->days(function () {
            Component::factory()->create(['name' => 'Component A']);
        });

        Component::factory()->create(['name' => 'Component B']);

        $this->sendRequest([
            'component_name' => '1',
            'last_updated_before' => 30,
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse('Component A')
            ->assertDontSeeTextInStreamedResponse('Component B');
    }

    public function test_excluding_deleted_components()
    {
        Component::factory()->create(['name' => 'Deleted Component', 'deleted_at' => now()]);
        Component::factory()->create(['name' => 'Active Component']);

        $this->sendRequest([
            'component_name' => '1',
            'deleted_components' => 'exclude_deleted',
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse('Active Component')
            ->assertDontSeeTextInStreamedResponse('Deleted Component');
    }

    public function test_including_deleted_components()
    {
        Component::factory()->create(['name' => 'Deleted Component', 'deleted_at' => now()]);
        Component::factory()->create(['name' => 'Active Component']);

        $this->sendRequest([
            'component_name' => '1',
            'deleted_components' => 'include_deleted',
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse('Active Component')
            ->assertSeeTextInStreamedResponse('Deleted Component');
    }

    public function test_including_only_deleted_components()
    {
        Component::factory()->create(['name' => 'Deleted Component', 'deleted_at' => now()]);
        Component::factory()->create(['name' => 'Active Component']);

        $this->sendRequest([
            'component_name' => '1',
            'deleted_components' => 'only_deleted',
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertDontSeeTextInStreamedResponse('Active Component')
            ->assertSeeTextInStreamedResponse('Deleted Component');
    }

    public function test_does_not_included_assignments_by_default()
    {
        [$assetA, $assetB] = Asset::factory()
            ->count(2)
            ->sequence(
                ['name' => 'Asset 001'],
                ['name' => 'Asset 002'],
            )->create()->all();

        Component::factory()->create(['name' => 'Component A']);
        Component::factory()->checkedOutToAsset($assetA)->create(['name' => 'Component B']);
        Component::factory()->checkedOutToAsset($assetB)->create(['name' => 'Component C']);

        $this->sendRequest([
            'component_name' => '1',
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertDontSeeTextInStreamedResponse([
                trans('admin/hardware/form.name'),
                trans('admin/hardware/form.tag'),
                trans('admin/reports/general.custom_export.asset_company'),
                trans('admin/reports/general.custom_export.asset_serial'),
                trans('admin/hardware/table.checkout_date'),
                trans('general.assigned_quantity'),
            ])
            ->assertSeeTextInStreamedResponse('Component A')
            ->assertSeeTextInStreamedResponse('Component B')
            ->assertSeeTextInStreamedResponse('Component C')
            ->assertDontSeeTextInStreamedResponse('Asset 001')
            ->assertDontSeeTextInStreamedResponse('Asset 002')
            // header + number of components
            ->assertRowCountInStreamedResponse(4);
    }

    public function test_can_include_assignments()
    {
        [$assetA, $assetB] = Asset::factory()
            ->count(2)
            ->sequence(
                ['name' => 'Asset 001'],
                ['name' => 'Asset 002'],
            )->create()->all();

        Component::factory()->create(['name' => 'Component A']);
        Component::factory()->checkedOutToAsset($assetA)->create(['name' => 'Component B']);
        Component::factory()->checkedOutToAsset($assetB)->create(['name' => 'Component C']);

        $this->sendRequest([
            'component_name' => '1',
            'include_assignments' => '1',
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse([
                trans('admin/hardware/form.name'),
                trans('admin/hardware/form.tag'),
                trans('admin/reports/general.custom_export.asset_company'),
                trans('admin/reports/general.custom_export.asset_serial'),
                trans('admin/hardware/table.checkout_date'),
                trans('general.assigned_quantity'),
            ])
            ->assertSeeTextInStreamedResponse([
                'Component A',
                'Component B',
                'Component C',
                'Asset 001',
                'Asset 002',
            ]);
    }

    public function test_custom_component_report_adheres_to_company_scoping_for_non_super_users()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create()->all();

        Component::factory()->for($companyA)->create(['name' => 'Company A Component']);
        Component::factory()->for($companyB)->create(['name' => 'Company B Component']);

        $this->actor = User::factory()->canViewReports()->for($companyA)->create();

        $this->settings->enableMultipleFullCompanySupport();

        $this->sendRequest([
            'component_name' => '1',
            'company' => '1',
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse('Company A Component')
            ->assertDontSeeTextInStreamedResponse('Company B Component');
    }

    public function test_custom_component_report_super_users_can_see_all_components()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create()->all();

        Component::factory()->for($companyA)->create(['name' => 'Company A Component']);
        Component::factory()->for($companyB)->create(['name' => 'Company B Component']);

        $this->actor = User::factory()->superuser()->create();

        $this->settings->enableMultipleFullCompanySupport();

        $this->sendRequest([
            'component_name' => '1',
            'company' => '1',
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse('Company A Component')
            ->assertSeeTextInStreamedResponse('Company B Component');
    }

    private function sendRequest(array $data): TestResponse
    {
        return $this->actingAs($this->actor)
            ->post(route('reports.custom.component.run'), $data);
    }
}
