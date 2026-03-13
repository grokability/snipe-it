<?php

namespace Tests\Unit\Transformers;

use App\Http\Transformers\DepreciationReportTransformer;
use App\Models\Asset;
use App\Models\Depreciation;
use Tests\TestCase;

class DepreciationReportTransformerTest extends TestCase
{
    public function testHandlesModelDepreciationMonthsBeingZero()
    {
        $asset = Asset::factory()->create();
        $depreciation = Depreciation::factory()->create(['months' => 0]);
        $asset->model->depreciation()->associate($depreciation);

        $transformer = new DepreciationReportTransformer;

        $result = $transformer->transformAsset($asset);

        $this->assertIsArray($result);
    }
}
