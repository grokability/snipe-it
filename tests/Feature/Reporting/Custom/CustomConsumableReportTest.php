<?php

namespace Tests\Feature\Reporting\Custom;

use App\Events\CheckoutableCheckedOut;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('custom-reporting')]
class CustomConsumableReportTest extends TestCase
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
            ->get(route('reports.custom.consumable'))
            ->assertForbidden();
    }

    public function test_requires_permission_to_run_report()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('reports.custom.consumable.run'), [
                //
            ])
            ->assertForbidden();
    }

    public function test_can_load_custom_report_page()
    {
        $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('reports.custom.consumable'))
            ->assertOk();
    }

    public function test_custom_consumable_report_validation()
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

    public function test_custom_consumable_report_headers()
    {
        $this->sendRequest([
            'id' => '1',
            'company' => '1',
            'category' => '1',
            'consumable_name' => '1',
            'manufacturer' => '1',
            'model' => '1',
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
                trans('admin/consumables/general.consumable_name'),
                trans('general.manufacturer'),
                trans('general.model_no'),
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
            'consumable_name' => '1',
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

        Consumable::factory()
            ->count(2)
            ->sequence(
                ['company_id' => $companyA->id, 'name' => 'Consumable for Company A'],
                ['company_id' => $companyB->id, 'name' => 'Consumable for Company B'],
            )
            ->create();

        $this->sendRequest([
            'consumable_name' => '1',
            'company' => '1',
            'by_company_id' => [
                $companyA->id,
            ],
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeePairsInStreamedResponse(['Company' => 'Company A', 'Consumable Name' => 'Consumable for Company A'])
            ->assertDontSeeTextInStreamedResponse('Consumable for Company B');
    }

    public function test_limiting_by_category()
    {
        [$categoryA, $categoryB] = Category::factory()
            ->count(2)
            ->sequence(
                ['name' => 'Category A', 'category_type' => 'consumable'],
                ['name' => 'Category B', 'category_type' => 'consumable'],
            )
            ->create()
            ->all();

        Consumable::factory()
            ->count(2)
            ->sequence(
                ['category_id' => $categoryA->id, 'name' => 'Consumable for Category A'],
                ['category_id' => $categoryB->id, 'name' => 'Consumable for Category B'],
            )
            ->create();

        $this->sendRequest([
            'consumable_name' => '1',
            'category' => '1',
            'by_category_id' => [
                $categoryA->id,
            ],
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeePairsInStreamedResponse(['Category' => 'Category A', 'Consumable Name' => 'Consumable for Category A'])
            ->assertDontSeeTextInStreamedResponse('Consumable for Category B');
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

        Consumable::factory()
            ->count(2)
            ->sequence(
                ['manufacturer_id' => $manufacturerA->id, 'name' => 'Consumable for Manufacturer A'],
                ['manufacturer_id' => $manufacturerB->id, 'name' => 'Consumable for Manufacturer B'],
            )
            ->create();

        $this->sendRequest([
            'consumable_name' => '1',
            'manufacturer' => '1',
            'by_manufacturer_id' => [
                $manufacturerA->id,
            ],
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeePairsInStreamedResponse(['Manufacturer' => 'Manufacturer A', 'Consumable Name' => 'Consumable for Manufacturer A'])
            ->assertDontSeeTextInStreamedResponse('Consumable for Manufacturer B');
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

        Consumable::factory()
            ->count(2)
            ->sequence(
                ['default_supplier_id' => $supplierA->id, 'name' => 'Consumable for Supplier A'],
                ['default_supplier_id' => $supplierB->id, 'name' => 'Consumable for Supplier B'],
            )
            ->create();

        $this->sendRequest([
            'consumable_name' => '1',
            'supplier' => '1',
            'by_supplier_id' => [
                $supplierA->id,
            ],
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeePairsInStreamedResponse(['Supplier' => 'Supplier A', 'Consumable Name' => 'Consumable for Supplier A'])
            ->assertDontSeeTextInStreamedResponse('Consumable for Supplier B');
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

        Consumable::factory()
            ->count(2)
            ->sequence(
                ['location_id' => $locationA->id, 'name' => 'Consumable for Location A'],
                ['location_id' => $locationB->id, 'name' => 'Consumable for Location B'],
            )
            ->create();

        $this->sendRequest([
            'consumable_name' => '1',
            'location' => '1',
            'by_location_id' => [
                $locationA->id,
            ],
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeePairsInStreamedResponse(['Location' => 'Location A', 'Consumable Name' => 'Consumable for Location A'])
            ->assertDontSeeTextInStreamedResponse('Consumable for Location B');
    }

    public function test_limiting_by_name()
    {
        Consumable::factory()->create(['name' => 'RAM']);
        Consumable::factory()->create(['name' => 'Hard Drive']);

        $this->sendRequest([
            'consumable_name' => '1',
            'by_name' => 'RAM',
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeePairsInStreamedResponse(['Consumable Name' => 'RAM'])
            ->assertDontSeeTextInStreamedResponse('Hard Drive');
    }

    public function test_limiting_by_model_number()
    {
        Consumable::factory()->create(['name' => 'Consumable A', 'model_number' => 'MODEL-001']);
        Consumable::factory()->create(['name' => 'Consumable B', 'model_number' => 'MODEL-002']);

        $this->sendRequest([
            'consumable_name' => '1',
            'model' => '1',
            'by_model_number' => 'MODEL-001',
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeePairsInStreamedResponse(['Model No.' => 'MODEL-001', 'Consumable Name' => 'Consumable A'])
            ->assertDontSeeTextInStreamedResponse('MODEL-002');
    }

    public function test_limiting_by_order_number()
    {
        // Under the new Orders / OrderItems model, order_number lives on
        // the Order and each Consumable links via a polymorphic OrderItem.
        // The report's `by_order_number` filter now walks that relation
        // (whereHas('orders')) instead of the removed
        // components.order_number column, and the export's `order`
        // column plucks distinct order numbers from the eager-loaded
        // relation.
        $componentA = Consumable::factory()->create(['name' => 'Consumable A']);
        $componentB = Consumable::factory()->create(['name' => 'Consumable B']);

        $orderA = \App\Models\Order::create(['order_number' => 'ORD-001']);
        $orderB = \App\Models\Order::create(['order_number' => 'ORD-002']);
        \App\Models\OrderItem::create([
            'order_id' => $orderA->id,
            'item_type' => Consumable::class,
            'item_id' => $componentA->id,
            'qty' => 1,
        ]);
        \App\Models\OrderItem::create([
            'order_id' => $orderB->id,
            'item_type' => Consumable::class,
            'item_id' => $componentB->id,
            'qty' => 1,
        ]);

        $this->sendRequest([
            'consumable_name' => '1',
            'order' => '1',
            'by_order_number' => 'ORD-001',
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeePairsInStreamedResponse(['Order Number' => 'ORD-001', 'Consumable Name' => 'Consumable A'])
            ->assertDontSeeTextInStreamedResponse('ORD-002')
            ->assertDontSeeTextInStreamedResponse('Consumable B');
    }

    public function test_limiting_by_purchase_date_range()
    {
        // purchase_date lives on the initial OrderItem's Order now;
        // withInitialAcquisition seeds that Order's date so the report's
        // Orders-EXISTS subquery matches Consumable A but not Consumable B.
        Consumable::factory()->withInitialAcquisition(null, null, '2024-01-15')->create(['name' => 'Consumable A']);
        Consumable::factory()->withInitialAcquisition(null, null, '2024-06-15')->create(['name' => 'Consumable B']);

        $this->sendRequest([
            'consumable_name' => '1',
            'purchase_start' => '2024-01-01',
            'purchase_end' => '2024-03-31',
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse('Consumable A')
            ->assertDontSeeTextInStreamedResponse('Consumable B');
    }

    public function test_limiting_by_quantity_range()
    {
        Consumable::factory()->create(['name' => 'Consumable A', 'qty' => 5]);
        Consumable::factory()->create(['name' => 'Consumable B', 'qty' => 50]);

        $this->sendRequest([
            'consumable_name' => '1',
            'quantity_start' => 1,
            'quantity_end' => 10,
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse('Consumable A')
            ->assertDontSeeTextInStreamedResponse('Consumable B');
    }

    public function test_limiting_by_minimum_quantity_range()
    {
        Consumable::factory()->create(['name' => 'Consumable A', 'min_amt' => 2]);
        Consumable::factory()->create(['name' => 'Consumable B', 'min_amt' => 20]);

        $this->sendRequest([
            'consumable_name' => '1',
            'min_quantity_start' => 1,
            'min_quantity_end' => 5,
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse('Consumable A')
            ->assertDontSeeTextInStreamedResponse('Consumable B');
    }

    public function test_limiting_by_unit_cost_range()
    {
        // unit_cost filters against the parent's default_purchase_cost
        // template (see CustomConsumableReportController::buildQuery for
        // the rationale on why we don't walk OrderItems here).
        Consumable::factory()->create(['name' => 'Consumable A', 'default_purchase_cost' => 10.00]);
        Consumable::factory()->create(['name' => 'Consumable B', 'default_purchase_cost' => 500.00]);

        $this->sendRequest([
            'consumable_name' => '1',
            'unit_cost_start' => 1,
            'unit_cost_end' => 50,
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse('Consumable A')
            ->assertDontSeeTextInStreamedResponse('Consumable B');
    }

    public function test_limiting_by_checkout_date_range()
    {
        // Consumables check out to users (not assets like components).
        // Same action_log-based filter shape either way; source of the
        // checkout event target just differs.
        [$userA, $userB] = User::factory()->count(2)->create()->all();

        $consumableA = Consumable::factory()->checkedOutToUser($userA)->create(['name' => 'Consumable A']);
        $consumableB = Consumable::factory()->checkedOutToUser($userB)->create(['name' => 'Consumable B']);

        $this->travel(-30)->days(
            fn () => event(new CheckoutableCheckedOut(
                $consumableA,
                $userA,
                $userA,
                '',
                [],
                1,
            ))
        );

        $this->travel(-15)->days(
            fn () => event(new CheckoutableCheckedOut(
                $consumableB,
                $userB,
                $userB,
                '',
                [],
                1,
            ))
        );

        $this->sendRequest([
            'consumable_name' => '1',
            'checkout_date_start' => now()->subDays(45)->toDateString(),
            'checkout_date_end' => now()->subDays(20)->toDateString(),
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse('Consumable A')
            ->assertDontSeeTextInStreamedResponse('Consumable B');
    }

    public function test_limiting_by_created_at_range()
    {
        $this->travel(-60)->days(function () {
            Consumable::factory()->create(['name' => 'Consumable A']);
        });

        Consumable::factory()->create(['name' => 'Consumable B']);

        $this->sendRequest([
            'consumable_name' => '1',
            'created_start' => now()->subDays(90)->toDateString(),
            'created_end' => now()->subDays(30)->toDateString(),
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse('Consumable A')
            ->assertDontSeeTextInStreamedResponse('Consumable B');
    }

    public function test_limiting_by_updated_at_range()
    {
        $this->travel(-60)->days(function () {
            Consumable::factory()->create(['name' => 'Consumable A']);
        });

        Consumable::factory()->create(['name' => 'Consumable B']);

        $this->sendRequest([
            'consumable_name' => '1',
            'last_updated_start' => now()->subDays(90)->toDateString(),
            'last_updated_end' => now()->subDays(30)->toDateString(),
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse('Consumable A')
            ->assertDontSeeTextInStreamedResponse('Consumable B');
    }

    public function test_limiting_by_updated_before()
    {
        $this->travel(-60)->days(function () {
            Consumable::factory()->create(['name' => 'Consumable A']);
        });

        Consumable::factory()->create(['name' => 'Consumable B']);

        $this->sendRequest([
            'consumable_name' => '1',
            'last_updated_before' => 30,
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse('Consumable A')
            ->assertDontSeeTextInStreamedResponse('Consumable B');
    }

    public function test_excluding_deleted_consumables()
    {
        Consumable::factory()->create(['name' => 'Deleted Consumable', 'deleted_at' => now()]);
        Consumable::factory()->create(['name' => 'Active Consumable']);

        $this->sendRequest([
            'consumable_name' => '1',
            'deleted_consumables' => 'exclude_deleted',
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse('Active Consumable')
            ->assertDontSeeTextInStreamedResponse('Deleted Consumable');
    }

    public function test_including_deleted_consumables()
    {
        Consumable::factory()->create(['name' => 'Deleted Consumable', 'deleted_at' => now()]);
        Consumable::factory()->create(['name' => 'Active Consumable']);

        $this->sendRequest([
            'consumable_name' => '1',
            'deleted_consumables' => 'include_deleted',
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse('Active Consumable')
            ->assertSeeTextInStreamedResponse('Deleted Consumable');
    }

    public function test_including_only_deleted_consumables()
    {
        Consumable::factory()->create(['name' => 'Deleted Consumable', 'deleted_at' => now()]);
        Consumable::factory()->create(['name' => 'Active Consumable']);

        $this->sendRequest([
            'consumable_name' => '1',
            'deleted_consumables' => 'only_deleted',
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertDontSeeTextInStreamedResponse('Active Consumable')
            ->assertSeeTextInStreamedResponse('Deleted Consumable');
    }

    public function test_does_not_include_assignments_by_default()
    {
        // Consumables check out to users, not assets. include_assignments
        // shape here is user-oriented (name, company, checkout date,
        // checked-out-by) rather than the component's asset+quantity shape.
        [$userA, $userB] = User::factory()
            ->count(2)
            ->sequence(
                ['first_name' => 'Alex', 'last_name' => 'One'],
                ['first_name' => 'Blair', 'last_name' => 'Two'],
            )->create()->all();

        Consumable::factory()->create(['name' => 'Consumable A']);
        Consumable::factory()->checkedOutToUser($userA)->create(['name' => 'Consumable B']);
        Consumable::factory()->checkedOutToUser($userB)->create(['name' => 'Consumable C']);

        $this->sendRequest([
            'consumable_name' => '1',
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertDontSeeTextInStreamedResponse([
                trans('general.user'),
                trans('admin/reports/general.custom_export.asset_company'),
                trans('admin/hardware/form.checkout_date'),
                trans('general.created_by'),
            ])
            ->assertSeeTextInStreamedResponse('Consumable A')
            ->assertSeeTextInStreamedResponse('Consumable B')
            ->assertSeeTextInStreamedResponse('Consumable C')
            ->assertDontSeeTextInStreamedResponse('Alex One')
            ->assertDontSeeTextInStreamedResponse('Blair Two')
            // header + number of consumables
            ->assertRowCountInStreamedResponse(4);
    }

    public function test_can_include_assignments()
    {
        [$userA, $userB] = User::factory()
            ->count(2)
            ->sequence(
                ['first_name' => 'Alex', 'last_name' => 'One'],
                ['first_name' => 'Blair', 'last_name' => 'Two'],
            )->create()->all();

        Consumable::factory()->create(['name' => 'Consumable A']);
        Consumable::factory()->checkedOutToUser($userA)->create(['name' => 'Consumable B']);
        Consumable::factory()->checkedOutToUser($userB)->create(['name' => 'Consumable C']);

        $this->sendRequest([
            'consumable_name' => '1',
            'include_assignments' => '1',
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse([
                trans('general.user'),
                trans('admin/reports/general.custom_export.asset_company'),
                trans('admin/hardware/form.checkout_date'),
                trans('general.created_by'),
            ])
            ->assertSeeTextInStreamedResponse([
                'Consumable A',
                'Consumable B',
                'Consumable C',
                'Alex One',
                'Blair Two',
            ]);
    }

    public function test_include_assignments_lists_every_user_company_alphabetized_and_pipe_joined()
    {
        // GH #19568: the user column read the legacy $user->company scalar
        // mirror and only rendered ONE company under FMCS. It should render
        // the full company_user pivot set, alphabetized, pipe-joined.
        [$companyC, $companyA, $companyB] = Company::factory()->count(3)->sequence(
            ['name' => 'Zeta Co'],
            ['name' => 'alpha co'],
            ['name' => 'Beta Co'],
        )->create()->all();

        $user = User::factory()->create(['first_name' => 'Casey', 'last_name' => 'Multi']);
        $user->companies()->sync([$companyA->id, $companyB->id, $companyC->id]);

        Consumable::factory()->checkedOutToUser($user)->create(['name' => 'Consumable A']);

        $this->sendRequest([
            'consumable_name' => '1',
            'include_assignments' => '1',
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse('alpha co | Beta Co | Zeta Co');
    }

    public function test_custom_consumable_report_adheres_to_company_scoping_for_non_super_users()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create()->all();

        Consumable::factory()->for($companyA)->create(['name' => 'Company A Consumable']);
        Consumable::factory()->for($companyB)->create(['name' => 'Company B Consumable']);

        $this->actor = User::factory()->canViewReports()->forCompany($companyA)->create();

        $this->settings->enableMultipleFullCompanySupport();

        $this->sendRequest([
            'consumable_name' => '1',
            'company' => '1',
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse('Company A Consumable')
            ->assertDontSeeTextInStreamedResponse('Company B Consumable');
    }

    public function test_custom_consumable_report_super_users_can_see_all_components()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create()->all();

        Consumable::factory()->for($companyA)->create(['name' => 'Company A Consumable']);
        Consumable::factory()->for($companyB)->create(['name' => 'Company B Consumable']);

        $this->actor = User::factory()->superuser()->create();

        $this->settings->enableMultipleFullCompanySupport();

        $this->sendRequest([
            'consumable_name' => '1',
            'company' => '1',
        ])
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse('Company A Consumable')
            ->assertSeeTextInStreamedResponse('Company B Consumable');
    }

    private function sendRequest(array $data): TestResponse
    {
        return $this->actingAs($this->actor)
            ->post(route('reports.custom.consumable.run'), $data);
    }
}
