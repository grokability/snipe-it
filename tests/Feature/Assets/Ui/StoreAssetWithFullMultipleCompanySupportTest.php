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
