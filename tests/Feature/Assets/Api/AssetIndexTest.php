<?php

namespace Tests\Feature\Assets\Api;

use App\Models\Asset;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AssetIndexTest extends TestCase
{
    public function test_asset_api_index_returns_expected_assets()
    {
        Asset::factory()->count(3)->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(
                route('api.assets.index', [
                    'sort' => 'name',
                    'order' => 'asc',
                    'offset' => '0',
                    'limit' => '20',
                ]))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
    }

    public function test_asset_api_index_returns_display_upcoming_audits_due()
    {
        Asset::factory()->count(3)->create(['next_audit_date' => Carbon::now()->format('Y-m-d')]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(
                route('api.assets.list-upcoming', ['action' => 'audits', 'upcoming_status' => 'due']))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
    }

    public function test_asset_api_index_returns_overdue_for_audit()
    {
        Asset::factory()->count(3)->create(['next_audit_date' => Carbon::now()->subDays(1)->format('Y-m-d')]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(
                route('api.assets.list-upcoming', ['action' => 'audits', 'upcoming_status' => 'overdue']))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
    }

    public function test_asset_api_index_returns_due_or_overdue_for_audit()
    {
        Asset::factory()->count(3)->create(['next_audit_date' => Carbon::now()->format('Y-m-d')]);
        Asset::factory()->count(2)->create(['next_audit_date' => Carbon::now()->subDays(1)->format('Y-m-d')]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(
                route('api.assets.list-upcoming', ['action' => 'audits', 'upcoming_status' => 'due-or-overdue']))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 5)->etc());
    }

    public function test_asset_api_index_returns_due_for_expected_checkin()
    {
        Asset::factory()->count(3)->create(['assigned_to' => '1', 'assigned_type' => User::class, 'expected_checkin' => Carbon::now()->format('Y-m-d')]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(
                route('api.assets.list-upcoming', ['action' => 'checkins', 'upcoming_status' => 'due'])
            )
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
    }

    public function test_asset_api_index_returns_overdue_for_expected_checkin()
    {
        Asset::factory()->count(3)->create(['assigned_to' => '1', 'assigned_type' => User::class, 'expected_checkin' => Carbon::now()->subDays(1)->format('Y-m-d')]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.assets.list-upcoming', ['action' => 'checkins', 'upcoming_status' => 'overdue']))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
    }

    public function test_asset_api_index_returns_due_or_overdue_for_expected_checkin()
    {
        Asset::factory()->count(3)->create(['assigned_to' => '1', 'assigned_type' => User::class, 'expected_checkin' => Carbon::now()->subDays(1)->format('Y-m-d')]);
        Asset::factory()->count(2)->create(['assigned_to' => '1', 'assigned_type' => User::class, 'expected_checkin' => Carbon::now()->format('Y-m-d')]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.assets.list-upcoming', ['action' => 'checkins', 'upcoming_status' => 'due-or-overdue']))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 5)->etc());
    }

    public function test_asset_api_index_adheres_to_company_scoping()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $assetA = Asset::factory()->for($companyA)->create();
        $assetB = Asset::factory()->for($companyB)->create();

        $superUser = $companyA->users()->save(User::factory()->superuser()->make());
        $userInCompanyA = $companyA->users()->save(User::factory()->viewAssets()->make());
        $userInCompanyB = $companyB->users()->save(User::factory()->viewAssets()->make());

        $this->settings->disableMultipleFullCompanySupport();

        $this->actingAsForApi($superUser)
            ->getJson(route('api.assets.index'))
            ->assertResponseContainsInRows($assetA, 'asset_tag')
            ->assertResponseContainsInRows($assetB, 'asset_tag');

        $this->actingAsForApi($userInCompanyA)
            ->getJson(route('api.assets.index'))
            ->assertResponseContainsInRows($assetA, 'asset_tag')
            ->assertResponseContainsInRows($assetB, 'asset_tag');

        $this->actingAsForApi($userInCompanyB)
            ->getJson(route('api.assets.index'))
            ->assertResponseContainsInRows($assetA, 'asset_tag')
            ->assertResponseContainsInRows($assetB, 'asset_tag');

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAsForApi($superUser)
            ->getJson(route('api.assets.index'))
            ->assertResponseContainsInRows($assetA, 'asset_tag')
            ->assertResponseContainsInRows($assetB, 'asset_tag');

        $this->actingAsForApi($userInCompanyA)
            ->getJson(route('api.assets.index'))
            ->assertResponseContainsInRows($assetA, 'asset_tag')
            ->assertResponseDoesNotContainInRows($assetB, 'asset_tag');

        $this->actingAsForApi($userInCompanyB)
            ->getJson(route('api.assets.index'))
            ->assertResponseDoesNotContainInRows($assetA, 'asset_tag')
            ->assertResponseContainsInRows($assetB, 'asset_tag');
    }

    public function test_assets_can_be_filtered_by_custom_field()
    {
        $field = CustomField::factory()->create();

        $matchingAssets = Asset::factory()->count(3)->hasMultipleCustomFields([$field])->create();
        foreach ($matchingAssets as $asset) {
            $asset->{$field->db_column_name()} = 'target-value';
            $asset->save();
        }

        // These assets have a null value for the custom field column and should not be returned
        Asset::factory()->count(2)->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.assets.index', [
                $field->db_column_name() => 'target-value',
            ]))
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
    }

    public function test_custom_field_with_link_template_includes_link()
    {
        $field = CustomField::factory()->create([
            'name' => 'AnyDesk ID',
            'link_template' => 'anydesk:{VALUE}',
        ]);

        $asset = Asset::factory()->hasMultipleCustomFields([$field])->create();
        $asset->{$field->db_column_name()} = '123456789';
        $asset->save();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.assets.index'))
            ->assertOk()
            ->assertJsonPath('rows.0.custom_fields.AnyDesk ID.value', '123456789')
            ->assertJsonPath('rows.0.custom_fields.AnyDesk ID.link', 'anydesk:123456789');
    }

    public function test_gracefully_handles_malformed_filter()
    {
        $this->actingAsForApi(User::factory()->viewAssets()->create())
            ->getJson(route('api.assets.index', [
                // filter should be a json encoded array and not a string
                'filter' => 'asset_tag:12345',
            ]))
            ->assertStatusMessageIs('error')
            ->assertJson(function (AssertableJson $json) {
                $json->has('messages.filter')->etc();
            });
    }

    public function test_returns_result_via_filter()
    {

        Asset::factory()->count(3)->create(['name' => 'MY AWESOME ASSET NAME']);
        $this->actingAsForApi(User::factory()->viewAssets()->create())
            ->getJson(route('api.assets.index', [
                'filter' => '{"name":"MY AWESOME ASSET NAME"}',
            ]))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
    }

    /**
     * Regression for a missing `break` in the API asset sort switch that
     * silently fell through from `case 'location'` into `case 'rtd_location'`,
     * applying both OrderLocation and OrderRtdLocation to the query so the
     * caller got a sort keyed on rtd_location first and location second when
     * they asked for location only. Verify sort=location alone controls the
     * result ordering.
     */
    public function test_sort_by_location_does_not_fall_through_to_rtd_location(): void
    {
        $zebraLocation = Location::factory()->create(['name' => 'Zebra Warehouse']);
        $aardvarkLocation = Location::factory()->create(['name' => 'Aardvark Warehouse']);

        // Cross the pairing between location and rtd_location so a fallthrough
        // that applied OrderRtdLocation would put the assets in a different
        // order than a clean OrderLocation.
        $assetLocatedAtAardvark = Asset::factory()->create([
            'location_id' => $aardvarkLocation->id,
            'rtd_location_id' => $zebraLocation->id,
        ]);
        $assetLocatedAtZebra = Asset::factory()->create([
            'location_id' => $zebraLocation->id,
            'rtd_location_id' => $aardvarkLocation->id,
        ]);

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.assets.index', [
                'sort' => 'location',
                'order' => 'asc',
                'limit' => 20,
            ]))
            ->assertOk()
            ->json('rows');

        $this->assertSame(
            [$assetLocatedAtAardvark->id, $assetLocatedAtZebra->id],
            [$response[0]['id'], $response[1]['id']],
        );
    }

    public function test_deleted_status_type_returns_soft_deleted_assets()
    {
        $deleted = Asset::factory()->count(3)->create();
        foreach ($deleted as $asset) {
            $asset->delete();
        }
        Asset::factory()->count(2)->create();

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.assets.index', ['status_type' => 'Deleted', 'limit' => 10]))
            ->assertOk();

        $response->assertJsonPath('total', 3);
        $rows = $response->json('rows');
        $this->assertCount(3, $rows, 'Deleted view must return the soft-deleted assets, not an empty rows array.');

        $returnedIds = collect($rows)->pluck('id')->sort()->values()->all();
        $expectedIds = $deleted->pluck('id')->sort()->values()->all();
        $this->assertSame($expectedIds, $returnedIds, 'Returned rows must match the soft-deleted asset ids, not any live ones.');
    }

    public function test_default_index_still_excludes_soft_deleted_assets()
    {
        // Companion to the Deleted-view regression. The withTrashed() on
        // re-hydration is scoped to that one query, so the id pluck
        // (which has no explicit onlyTrashed() for a default request)
        // keeps excluding soft-deleted rows and the rows array matches.
        $live = Asset::factory()->count(2)->create();
        $deleted = Asset::factory()->count(3)->create();
        foreach ($deleted as $asset) {
            $asset->delete();
        }

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.assets.index', ['limit' => 10]))
            ->assertOk();

        $response->assertJsonPath('total', 2);
        $returnedIds = collect($response->json('rows'))->pluck('id')->sort()->values()->all();
        $this->assertSame($live->pluck('id')->sort()->values()->all(), $returnedIds);
    }
}
