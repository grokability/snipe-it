<?php

namespace Tests\Feature\Assets\Ui;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Statuslabel;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ProvidesDataForFullMultipleCompanySupportTesting;
use Tests\TestCase;

class StoreAssetWithFullMultipleCompanySupportTest extends TestCase
{
    use ProvidesDataForFullMultipleCompanySupportTesting;

    #[DataProvider('dataForFullMultipleCompanySupportTesting')]
    public function testAdheresToFullMultipleCompaniesSupportScoping($data)
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
}
