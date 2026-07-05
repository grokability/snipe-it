<?php

namespace Tests\Feature\Assets\Ui;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ProvidesDataForFullMultipleCompanySupportTesting;
use Tests\TestCase;

class StoreAssetWithFullMultipleCompanySupportTest extends TestCase
{
    use ProvidesDataForFullMultipleCompanySupportTesting;

    #[DataProvider('dataForFullMultipleCompanySupportTesting')]
    public function test_adheres_to_full_multiple_companies_support_scoping($data)
    {
        ['actor' => $actor, 'company_attempting_to_associate' => $company, 'assertions' => $assertions] = $data();

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAs($actor)
            ->post(route('hardware.store'), [
                'asset_tags' => ['1' => '1234'],
                'model_id' => AssetModel::factory()->create()->id,
                'status_id' => Statuslabel::factory()->create()->id,
                'company_id' => $company->id,
            ]);

        $asset = Asset::where('asset_tag', '1234')->sole();

        $assertions($asset);
    }

    /**
     * Non-admin whose scalar company_id is null but has a pivot membership should be able to create
     * an asset in their company. Previously getIdForCurrentUser() returned null (scalar), causing
     * the asset to be saved with company_id = null and then failing FMCS scoping on the redirect.
     */
    public function test_non_admin_with_pivot_only_company_membership_can_create_asset_in_their_company()
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

        $company = Company::factory()->create();
        $location = Location::factory()->for($company)->create();

        // Create a user with NULL scalar company_id but a pivot membership in $company.
        // This mirrors users created/updated via the web UI, which writes only to the pivot.
        $user = User::factory()->createAssets()->create(['company_id' => null]);
        $user->companies()->sync([$company->id]);

        $this->actingAs($user)
            ->post(route('hardware.store'), [
                'asset_tags' => ['1' => '5678'],
                'serials' => ['1' => null],
                'model_id' => AssetModel::factory()->create()->id,
                'status_id' => Statuslabel::factory()->readyToDeploy()->create()->id,
                'company_id' => $company->id,
                'rtd_location_id' => $location->id,
                'redirect_option' => 'back',
            ])->assertSessionHasNoErrors();

        $asset = Asset::withoutGlobalScopes()->where('asset_tag', '5678')->first();

        $this->assertNotNull($asset, 'Asset was not created.');
        $this->assertEquals($company->id, $asset->company_id, 'Asset should be assigned to the submitted company, not null.');
    }

    /**
     * @link https://github.com/grokability/snipe-it/issues/18798
     */
    public function test_allows_creating_asset_with_scoped_location()
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

        $company = Company::factory()->create();
        $location = Location::factory()->for($company)->create();

        $admin = User::factory()->admin()->for($company)->create();

        $this->actingAs($admin)
            ->post(route('hardware.store'), [
                'asset_tags' => ['1' => '1234'],
                'serials' => ['1' => null],
                'model_id' => AssetModel::factory()->create()->id,
                'status_id' => Statuslabel::factory()->readyToDeploy()->create()->id,
                'checkout_to_type' => 'user',
                'assigned_user' => $admin->id,
                'assigned_asset' => null,
                'notes' => null,
                'rtd_location_id' => $location->id,
                'name' => null,
                'warranty_months' => null,
                'expected_checkin' => null,
                'next_audit_date' => null,
                'order_number' => null,
                'purchase_date' => null,
                'asset_eol_date' => null,
                'purchase_cost' => null,
                'redirect_option' => 'back',
            ])->assertSessionHasNoErrors();

        $asset = Asset::where(['asset_tag' => '1234'])->first();

        if (! $asset->exists()) {
            $this->fail('Asset was not created.');
        }

        $this->assertEquals($location->id, $asset->rtd_location_id);
        $this->assertEquals($admin->id, $asset->assigned_to);
    }
}
