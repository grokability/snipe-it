<?php

namespace Tests\Feature\Assets\Api;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Statuslabel;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ProvidesDataForFullMultipleCompanySupportTesting;
use Tests\TestCase;

class StoreAssetWithFullMultipleCompanySupportTest extends TestCase
{
    use ProvidesDataForFullMultipleCompanySupportTesting;

    /**
     * @link https://github.com/grokability/snipe-it/issues/15654
     */
    #[DataProvider('dataForFullMultipleCompanySupportTesting')]
    public function testAdheresToFullMultipleCompaniesSupportScoping($data)
    {
        ['actor' => $actor, 'company_attempting_to_associate' => $company, 'assertions' => $assertions] = $data();

        $this->settings->enableMultipleFullCompanySupport();

        $response = $this->actingAsForApi($actor)
            ->postJson(route('api.assets.store'), [
                'asset_tag' => 'random_string',
                'company_id' => $company->id,
                'model_id' => AssetModel::factory()->create()->id,
                'status_id' => Statuslabel::factory()->readyToDeploy()->create()->id,
            ]);

        $asset = Asset::withoutGlobalScopes()->findOrFail($response['payload']['id']);

        $assertions($asset);
    }

    #[DataProvider('dataForFullMultipleCompanySupportTesting')]
    public function testHandlesCompanyIdBeingString($data)
    {
        ['actor' => $actor, 'company_attempting_to_associate' => $company, 'assertions' => $assertions] = $data();

        $this->settings->enableMultipleFullCompanySupport();

        $response = $this->actingAsForApi($actor)
            ->postJson(route('api.assets.store'), [
                'asset_tag' => 'random_string',
                'company_id' => (string) $company->id,
                'model_id' => AssetModel::factory()->create()->id,
                'status_id' => Statuslabel::factory()->readyToDeploy()->create()->id,
            ]);

        $asset = Asset::withoutGlobalScopes()->findOrFail($response['payload']['id']);

        $assertions($asset);
    }
}
