<?php

namespace Tests\Unit\Presenters;

use App\Models\Asset;
use App\Models\AssetModel;
use Tests\TestCase;

class AssetPresenterTest extends TestCase
{
    public function test_dynamic_url()
    {
        $this->settings->set(['locale' => 'en-US']);

        $assetModel = AssetModel::factory()->create([
            'model_number' => 'MN-123',
            'name' => 'Macbook',
        ]);

        $asset = Asset::factory()
            ->for($assetModel, 'model')
            ->create(['serial' => 'SN-123']);

        $this->assertEquals(
            'https://example.com/en-US/SN-123/MN-123/Macbook',
            $asset->present()->dynamicUrl('https://example.com/{LOCALE}/{SERIAL}/{MODEL_NUMBER}/{MODEL_NAME}')
        );
    }
}
