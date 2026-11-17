<?php

namespace Tests\Feature\Reporting\Custom;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\ReportTemplate;
use App\Models\User;
use League\Csv\Reader;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('custom-reporting')]
class CustomAssetReportTest extends TestCase
{
    public function test_requires_permission_to_view_page()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('reports/custom'))
            ->assertForbidden();
    }

    public function test_requires_permission_to_run_report()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('reports.post-custom'), [
                //
            ])
            ->assertForbidden();
    }

    public function test_can_load_custom_report_page()
    {
        $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('reports/custom'))
            ->assertOk()
            ->assertViewHas([
                'template' => function (ReportTemplate $template) {
                    // the view should have an empty report by default
                    return $template->exists() === false;
                },
            ]);
    }

    public function test_custom_asset_report()
    {
        Asset::factory()->create(['name' => 'Asset A']);
        Asset::factory()->create(['name' => 'Asset B']);

        $this->actingAs(User::factory()->canViewReports()->create())
            ->post('reports/custom', [
                'asset_name' => '1',
                'asset_tag' => '1',
                'serial' => '1',
            ])->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=utf-8')
            ->assertSeeTextInStreamedResponse('Asset A')
            ->assertSeeTextInStreamedResponse('Asset B');
    }

    public function test_custom_asset_report_adheres_to_company_scoping()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create()->all();

        Asset::factory()->for($companyA)->create(['name' => 'Asset A']);
        Asset::factory()->for($companyB)->create(['name' => 'Asset B']);

        $superUser = $companyA->users()->save(User::factory()->superuser()->make());
        $userInCompanyA = $companyA->users()->save(User::factory()->canViewReports()->make());
        $userInCompanyB = $companyB->users()->save(User::factory()->canViewReports()->make());

        $this->settings->disableMultipleFullCompanySupport();

        $this->actingAs($superUser)
            ->post('reports/custom', ['asset_name' => '1', 'asset_tag' => '1', 'serial' => '1'])
            ->assertSeeTextInStreamedResponse(['Asset A', 'Asset B']);

        $this->actingAs($userInCompanyA)
            ->post('reports/custom', ['asset_name' => '1', 'asset_tag' => '1', 'serial' => '1'])
            ->assertSeeTextInStreamedResponse(['Asset A', 'Asset B']);

        $this->actingAs($userInCompanyB)
            ->post('reports/custom', ['asset_name' => '1', 'asset_tag' => '1', 'serial' => '1'])
            ->assertSeeTextInStreamedResponse(['Asset A', 'Asset B']);

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAs($superUser)
            ->post('reports/custom', ['asset_name' => '1', 'asset_tag' => '1', 'serial' => '1'])
            ->assertSeeTextInStreamedResponse(['Asset A', 'Asset B']);

        $this->actingAs($userInCompanyA)
            ->post('reports/custom', ['asset_name' => '1', 'asset_tag' => '1', 'serial' => '1'])
            ->assertSeeTextInStreamedResponse('Asset A')
            ->assertDontSeeTextInStreamedResponse(['Asset B']);

        $this->actingAs($userInCompanyB)
            ->post('reports/custom', ['asset_name' => '1', 'asset_tag' => '1', 'serial' => '1'])
            ->assertDontSeeTextInStreamedResponse('Asset A')
            ->assertSeeTextInStreamedResponse('Asset B');
    }

    public function test_user_company_column_lists_every_assignee_company_alphabetized_and_pipe_joined()
    {
        // GH #19568: the user_company column read the legacy scalar
        // company mirror on the assignee and only rendered ONE company
        // under FMCS. Report the full company_user pivot set,
        // alphabetized, pipe-joined.
        [$companyC, $companyA, $companyB] = Company::factory()->count(3)->sequence(
            ['name' => 'Zeta Co'],
            ['name' => 'alpha co'],
            ['name' => 'Beta Co'],
        )->create()->all();

        $assignee = User::factory()->create(['first_name' => 'Casey', 'last_name' => 'Multi']);
        $assignee->companies()->sync([$companyA->id, $companyB->id, $companyC->id]);

        Asset::factory()->assignedToUser($assignee)->create(['name' => 'Assigned Asset']);

        $this->actingAs(User::factory()->superuser()->create())
            ->post('reports/custom', [
                'asset_name' => '1',
                'user_company' => '1',
            ])
            ->assertOk()
            ->assertSeeTextInStreamedResponse('alpha co | Beta Co | Zeta Co');
    }

    public function test_can_limit_assets_by_last_check_in()
    {
        Asset::factory()->create(['name' => 'Asset A', 'last_checkin' => '2023-08-01']);
        Asset::factory()->create(['name' => 'Asset B', 'last_checkin' => '2023-08-02']);
        Asset::factory()->create(['name' => 'Asset C', 'last_checkin' => '2023-08-03']);
        Asset::factory()->create(['name' => 'Asset D', 'last_checkin' => '2023-08-04']);
        Asset::factory()->create(['name' => 'Asset E', 'last_checkin' => '2023-08-05']);

        $this->actingAs(User::factory()->canViewReports()->create())
            ->post('reports/custom', [
                'asset_name' => '1',
                'asset_tag' => '1',
                'serial' => '1',
                'checkin_date' => '1',
                'checkin_date_start' => '2023-08-02',
                'checkin_date_end' => '2023-08-04',
            ])->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=utf-8')
            ->assertDontSeeTextInStreamedResponse('Asset A')
            ->assertSeeTextInStreamedResponse('Asset B')
            ->assertSeeTextInStreamedResponse('Asset C')
            ->assertSeeTextInStreamedResponse('Asset D')
            ->assertDontSeeTextInStreamedResponse('Asset E');
    }

    public function test_can_limit_custom_report_to_assigned_assets(): void
    {
        Asset::factory()->assignedToUser()->create(['name' => 'Assigned Asset']);
        Asset::factory()->create(['name' => 'Unassigned Asset']);

        $this->actingAs(User::factory()->canViewReports()->create())
            ->post('reports/custom', [
                'asset_name' => '1',
                'assignment_status' => 'assigned',
            ])
            ->assertOk()
            ->assertSeeTextInStreamedResponse('Assigned Asset')
            ->assertDontSeeTextInStreamedResponse('Unassigned Asset');
    }

    public function test_can_limit_custom_report_to_unassigned_assets(): void
    {
        Asset::factory()->assignedToUser()->create(['name' => 'Assigned Asset']);
        Asset::factory()->create(['name' => 'Unassigned Asset']);

        $this->actingAs(User::factory()->canViewReports()->create())
            ->post('reports/custom', [
                'asset_name' => '1',
                'assignment_status' => 'unassigned',
            ])
            ->assertOk()
            ->assertDontSeeTextInStreamedResponse('Assigned Asset')
            ->assertSeeTextInStreamedResponse('Unassigned Asset');
    }

    public function test_assigned_asset_tag_column_emits_parent_tag_only_when_opted_in(): void
    {
        // #18281: opt-in column that emits the parent asset's tag in its own
        // cell when an asset is checked out to another asset. Users/locations
        // /unassigned rows leave the cell empty. Header must not appear when
        // the checkbox isn't submitted, so existing templates are unchanged.
        $parent = Asset::factory()->create(['asset_tag' => 'PARENT-001']);
        Asset::factory()->assignedToAsset()->create([
            'name' => 'Child Asset',
            'assigned_to' => $parent->id,
            'assigned_type' => Asset::class,
        ]);
        Asset::factory()->assignedToUser()->create(['name' => 'User-Assigned Asset']);
        Asset::factory()->create(['name' => 'Unassigned Asset']);

        $reporter = User::factory()->canViewReports()->create();

        // With the checkbox: column appears, parent tag in the asset-to-asset
        // row, empty cell everywhere else.
        $response = $this->actingAs($reporter)
            ->post('reports/custom', [
                'asset_name' => '1',
                'assigned_asset_tag' => '1',
            ])
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=utf-8');

        $rows = collect(Reader::createFromString($response->streamedContent())->getRecords())->values();
        $header = $rows->first();
        $body = $rows->slice(1)->values();

        $columnIndex = array_search(
            trans('admin/reports/general.custom_export.assigned_asset_tag'),
            $header,
            true,
        );
        $this->assertNotFalse($columnIndex, 'Checked Out Asset Tag header should be present when checkbox is submitted');

        $byName = $body->keyBy(fn ($row) => $row[array_search(trans('general.name'), $header, true)] ?? null);

        $this->assertSame('PARENT-001', $byName['Child Asset'][$columnIndex] ?? null);
        $this->assertSame('', $byName['User-Assigned Asset'][$columnIndex] ?? null);
        $this->assertSame('', $byName['Unassigned Asset'][$columnIndex] ?? null);

        // Without the checkbox: column header is absent entirely.
        $responseWithout = $this->actingAs($reporter)
            ->post('reports/custom', ['asset_name' => '1'])
            ->assertOk();

        $headerWithout = collect(Reader::createFromString($responseWithout->streamedContent())->getRecords())
            ->first();

        $this->assertFalse(
            array_search(trans('admin/reports/general.custom_export.assigned_asset_tag'), $headerWithout, true),
            'Checked Out Asset Tag column must not appear when checkbox is unchecked',
        );
    }

    public function test_custom_report_decrypts_encrypted_custom_fields_when_user_has_permission(): void
    {
        $customField = CustomField::factory()->encrypt()->create();
        $columnName = $customField->db_column_name();

        $fieldset = CustomFieldset::factory()->create();
        $fieldset->fields()->attach($customField, ['order' => 1, 'required' => false]);
        $model = AssetModel::factory()->create(['fieldset_id' => $fieldset->id]);

        $asset = Asset::factory()->for($model, 'model')->create(['name' => 'Encrypted Asset']);
        $asset->{$columnName} = 'super-secret-value';
        $asset->save();

        $user = User::factory()->create([
            'permissions' => json_encode([
                'reports.view' => '1',
                'assets.view.encrypted_custom_fields' => '1',
            ]),
        ]);

        $response = $this->actingAs($user)
            ->post('reports/custom', [
                'asset_name' => '1',
                $columnName => '1',
            ])
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=utf-8');

        $records = collect(Reader::createFromString($response->streamedContent())->getRecords())
            ->flatten()
            ->filter();

        $this->assertTrue($records->contains('super-secret-value'));
    }

    public function test_checkout_date_range_combined_with_assigned_only_filter()
    {
        // Reproducing the "Allocated Assets (Innovations)" template shape more
        // literally: an assigned-only report combined with a checkout_date_range.
        // Historically the pluck() version of the subquery returned an array
        // that whereIn could handle. The subquery-based version we ship now
        // depends on Laravel correctly compiling an inline SQL subquery. If
        // that ever breaks (or the item_type/action_type filter drifts), the
        // whole join returns empty and the customer's report is just the
        // header row.
        $reporter = User::factory()->canViewReports()->create();

        // Asset currently checked out to a user AND had a checkout log in range.
        $expected = Asset::factory()->assignedToUser()->create(['name' => 'Should Appear']);
        $log = new Actionlog;
        $log->item_type = Asset::class;
        $log->item_id = $expected->id;
        $log->action_type = 'checkout';
        $log->action_date = '2025-03-15 10:00:00';
        $log->created_at = '2025-03-15 10:00:00';
        $log->created_by = $reporter->id;
        $log->save();

        // Currently assigned but checkout log outside range.
        $wrongDate = Asset::factory()->assignedToUser()->create(['name' => 'Wrong Date']);
        $log2 = new Actionlog;
        $log2->item_type = Asset::class;
        $log2->item_id = $wrongDate->id;
        $log2->action_type = 'checkout';
        $log2->action_date = '2024-06-15 10:00:00';
        $log2->created_at = '2024-06-15 10:00:00';
        $log2->created_by = $reporter->id;
        $log2->save();

        // Checkout log in range but currently unassigned (was checked back in).
        $unassigned = Asset::factory()->create(['name' => 'Was Checked Out But Returned']);
        $log3 = new Actionlog;
        $log3->item_type = Asset::class;
        $log3->item_id = $unassigned->id;
        $log3->action_type = 'checkout';
        $log3->action_date = '2025-03-20 10:00:00';
        $log3->created_at = '2025-03-20 10:00:00';
        $log3->created_by = $reporter->id;
        $log3->save();

        $this->actingAs($reporter)
            ->post('reports/custom', [
                'asset_name' => '1',
                'asset_tag' => '1',
                'assignment_status' => 'assigned',
                'checkout_date_start' => '2025-03-01',
                'checkout_date_end' => '2025-03-31',
            ])
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=utf-8')
            ->assertSeeTextInStreamedResponse('Should Appear')
            ->assertDontSeeTextInStreamedResponse('Wrong Date')
            ->assertDontSeeTextInStreamedResponse('Was Checked Out But Returned');
    }

    public function test_can_limit_assets_by_checkout_date_range()
    {
        // Reproducing the original issue: user selects "Allocated Assets" template,
        // sets a "Checked Out date range", clicks Generate, gets only the header
        // row. The controller uses an action_logs subquery to find asset_ids that
        // had a checkout action within the range.
        $insideRange = Asset::factory()->create(['name' => 'Asset Checked Out In Range']);
        $outsideRange = Asset::factory()->create(['name' => 'Asset Checked Out Elsewhere']);
        $neverCheckedOut = Asset::factory()->create(['name' => 'Asset Never Checked Out']);

        $reporter = User::factory()->canViewReports()->create();

        $log = new Actionlog;
        $log->item_type = Asset::class;
        $log->item_id = $insideRange->id;
        $log->action_type = 'checkout';
        $log->action_date = '2025-03-15 10:00:00';
        $log->created_at = '2025-03-15 10:00:00';
        $log->created_by = $reporter->id;
        $log->save();

        $log2 = new Actionlog;
        $log2->item_type = Asset::class;
        $log2->item_id = $outsideRange->id;
        $log2->action_type = 'checkout';
        $log2->action_date = '2025-01-15 10:00:00';
        $log2->created_at = '2025-01-15 10:00:00';
        $log2->created_by = $reporter->id;
        $log2->save();

        $this->actingAs($reporter)
            ->post('reports/custom', [
                'asset_name' => '1',
                'asset_tag' => '1',
                'checkout_date_start' => '2025-03-01',
                'checkout_date_end' => '2025-03-31',
            ])
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=utf-8')
            ->assertSeeTextInStreamedResponse('Asset Checked Out In Range')
            ->assertDontSeeTextInStreamedResponse('Asset Checked Out Elsewhere')
            ->assertDontSeeTextInStreamedResponse('Asset Never Checked Out');
    }

    public function test_purchase_cost_start_only_is_inclusive_of_the_boundary()
    {
        // Regression: with only purchase_cost_start supplied, the filter used
        // strict > which quietly hid assets whose cost equalled the boundary
        // (including 0). The other branch (both endpoints set) uses
        // whereBetween which is inclusive, so start-only should match.
        Asset::factory()->create(['name' => 'Asset At Boundary', 'purchase_cost' => 100]);
        Asset::factory()->create(['name' => 'Asset Below', 'purchase_cost' => 50]);
        Asset::factory()->create(['name' => 'Asset Above', 'purchase_cost' => 200]);

        $this->actingAs(User::factory()->canViewReports()->create())
            ->post('reports/custom', [
                'asset_name' => '1',
                'purchase_cost_start' => 100,
            ])
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=utf-8')
            ->assertSeeTextInStreamedResponse('Asset At Boundary')
            ->assertSeeTextInStreamedResponse('Asset Above')
            ->assertDontSeeTextInStreamedResponse('Asset Below');
    }

    public function test_custom_report_hides_values_for_custom_fields_not_in_the_assets_model_fieldset()
    {
        // Two custom fields exist, each attached to a different fieldset. Both
        // physical `_snipeit_` columns still hold values on both assets
        // (simulating the customer scenario where a field was removed from a
        // fieldset after values had already been written). The custom report
        // used to expose those orphaned values because it read the column
        // directly. It now gates the value on membership in the asset's model's
        // fieldset so orphaned values come back blank.
        $priority = CustomField::factory()->create(['name' => 'Priority']);
        $majorOsRelease = CustomField::factory()->create(['name' => 'Major OS Release']);

        $laptopFieldset = CustomFieldset::factory()->create(['name' => 'Laptop']);
        $laptopFieldset->fields()->attach($priority, ['order' => 1, 'required' => false]);

        $switchFieldset = CustomFieldset::factory()->create(['name' => 'Switch']);
        $switchFieldset->fields()->attach($majorOsRelease, ['order' => 1, 'required' => false]);

        $laptopModel = AssetModel::factory()->create(['fieldset_id' => $laptopFieldset->id]);
        $switchModel = AssetModel::factory()->create(['fieldset_id' => $switchFieldset->id]);

        $priorityColumn = $priority->db_column_name();
        $osColumn = $majorOsRelease->db_column_name();

        $laptop = Asset::factory()->for($laptopModel, 'model')->create(['name' => 'Laptop 001']);
        $laptop->{$priorityColumn} = 'HIGH';
        $laptop->{$osColumn} = 'ORPHANED-OS-VALUE';
        $laptop->save();

        $switch = Asset::factory()->for($switchModel, 'model')->create(['name' => 'Switch 001']);
        $switch->{$priorityColumn} = 'ORPHANED-PRIORITY-VALUE';
        $switch->{$osColumn} = '17.9';
        $switch->save();

        $response = $this->actingAs(User::factory()->canViewReports()->create())
            ->post('reports/custom', [
                'asset_name' => '1',
                $priorityColumn => '1',
                $osColumn => '1',
            ])
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=utf-8');

        $rows = collect(Reader::createFromString($response->streamedContent())->getRecords())->values();
        $header = $rows->first();
        $body = $rows->slice(1)->values();

        $priorityIndex = array_search('Priority', $header, true);
        $osIndex = array_search('Major OS Release', $header, true);
        $nameIndex = array_search(trans('admin/hardware/form.name'), $header, true);
        $this->assertNotFalse($priorityIndex);
        $this->assertNotFalse($osIndex);
        $this->assertNotFalse($nameIndex);

        $byName = $body->keyBy(fn ($row) => $row[$nameIndex] ?? null);

        $this->assertSame('HIGH', $byName['Laptop 001'][$priorityIndex] ?? null);
        $this->assertSame('', $byName['Laptop 001'][$osIndex] ?? null,
            'Orphaned Major OS Release value should be hidden on an asset whose model fieldset does not include it');

        $this->assertSame('', $byName['Switch 001'][$priorityIndex] ?? null,
            'Orphaned Priority value should be hidden on an asset whose model fieldset does not include it');
        $this->assertSame('17.9', $byName['Switch 001'][$osIndex] ?? null);
    }

    public function test_custom_report_form_only_shows_custom_fields_that_belong_to_a_fieldset()
    {
        // Fields with no fieldset association can never surface a value in a
        // report (post-fix), so the form should not offer them as selectable
        // columns either. Otherwise the user ticks a checkbox and gets an
        // always-empty column back, which is confusing.
        $attached = CustomField::factory()->create(['name' => 'Priority']);
        $orphan = CustomField::factory()->create(['name' => 'Retired Field']);

        $fieldset = CustomFieldset::factory()->create();
        $fieldset->fields()->attach($attached, ['order' => 1, 'required' => false]);

        $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('reports/custom'))
            ->assertOk()
            ->assertSee('Priority')
            ->assertDontSee('Retired Field');
    }

    public function test_last_updated_range_is_inclusive_of_the_end_day()
    {
        // Regression: whereBetween on assets.updated_at (a timestamp) with a
        // raw Y-m-d string was widened by MySQL to Y-m-d 00:00:00, so anything
        // updated on the end day AFTER midnight was silently excluded. Result:
        // reports that ended "today" (the common case) returned empty or too
        // few rows. The fix normalizes with Carbon startOfDay / endOfDay to
        // match the created_at handling right above it in the controller.
        //
        // Explicit assertions per timestamp instead of a factory `->create()`
        // because Eloquent bumps updated_at to now() on save() unless we set
        // it directly.
        Asset::factory()->create(['name' => 'Asset Before'])
            ->forceFill(['updated_at' => '2025-03-01 00:00:00'])->save();

        Asset::factory()->create(['name' => 'Asset First Day'])
            ->forceFill(['updated_at' => '2025-03-15 08:30:00'])->save();

        Asset::factory()->create(['name' => 'Asset Last Day Afternoon'])
            ->forceFill(['updated_at' => '2025-03-31 15:45:00'])->save();

        Asset::factory()->create(['name' => 'Asset After'])
            ->forceFill(['updated_at' => '2025-04-01 09:00:00'])->save();

        $this->actingAs(User::factory()->canViewReports()->create())
            ->post('reports/custom', [
                'asset_name' => '1',
                'asset_tag' => '1',
                'last_updated_start' => '2025-03-01',
                'last_updated_end' => '2025-03-31',
            ])
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=utf-8')
            ->assertSeeTextInStreamedResponse('Asset First Day')
            ->assertSeeTextInStreamedResponse('Asset Last Day Afternoon')
            ->assertDontSeeTextInStreamedResponse('Asset After');
    }
}
