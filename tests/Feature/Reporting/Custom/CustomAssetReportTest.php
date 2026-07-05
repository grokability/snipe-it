<?php

namespace Tests\Feature\Reporting\Custom;

use App\Models\Asset;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\ReportTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
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

        $asset = Asset::factory()->create(['name' => 'Encrypted Asset']);
        $asset->{$columnName} = Crypt::encrypt('super-secret-value');
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
}
