<?php

namespace Tests\Feature\AssetQuery\DateQuery;

use App\Models\Asset;
use App\Models\AssetModel;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DateQueryTest extends TestCase
{
    use RefreshDatabase;

    public function testPurchaseDateQueryStart()
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 1));

        $assetA = Asset::factory()->create(['purchase_date' => Carbon::now()->addDays(14)->toDateString()]);
        $assetB = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(14)->toDateString()]);
        $assetC = Asset::factory()->create(['purchase_date' => Carbon::now()->addMonths(14)->toDateString()]);

        $filter = [[
            'field' => 'purchase_date',
            'value' => ['startDate' => Carbon::now()->addMonths(2)->toDateString()],
            'operator' => 'contains',
            'logic' => 'AND',
        ]];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($assetC));
        $this->assertFalse($results->contains($assetA));
    }

    public function testPurchaseDateQueryEnd()
    {
        Carbon::setTestNow(Carbon::create(2015, 2, 1));

        $assetA = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(5)->toDateString()]);
        $assetB = Asset::factory()->create(['purchase_date' => Carbon::now()->addMonths(14)->toDateString()]);
        $assetC = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(10)->toDateString()]);

        $filter = [[
            'field' => 'purchase_date',
            'value' => ['endDate' => Carbon::now()->addWeeks(7)->toDateString()],
            'operator' => 'contains',
            'logic' => 'AND',
        ]];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
        $this->assertFalse($results->contains($assetC));
    }

    public function testPurchaseDateQueryRange()
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 4));

        $assetA = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(50)->toDateString()]);
        $assetB = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(75)->toDateString()]);
        $assetC = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(100)->toDateString()]);
        $assetD = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(125)->toDateString()]);
        $assetE = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(150)->toDateString()]);

        $filter = [[
            'field' => 'purchase_date',
            'value' => [
                'startDate' => Carbon::now()->addWeeks(70)->toDateString(),
                'endDate'   => Carbon::now()->addWeeks(130)->toDateString(),
            ],
            'operator' => 'contains',
            'logic' => 'AND',
        ]];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(3, $results);
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($assetC));
        $this->assertTrue($results->contains($assetD));
        $this->assertFalse($results->contains($assetA));
        $this->assertFalse($results->contains($assetE));
    }

   public function testEolDateQueryEnd()
    {
        Carbon::setTestNow(Carbon::create(2015, 2, 1));


        $marker = 'eol-end-marker-'.\Illuminate\Support\Str::uuid();

        $modelA = AssetModel::factory()->create(['eol' => 12]);
        $modelB = AssetModel::factory()->create(['eol' => 24]);
        $modelC = AssetModel::factory()->create(['eol' => 36]);

        $purchaseDate = Carbon::now();

        $assetA = Asset::factory()->create([
            'model_id'       => $modelA->id,
            'purchase_date'  => $purchaseDate->toDateString(),
            'asset_eol_date' => $purchaseDate->copy()->addMonths(12)->toDateString(),
            'order_number'   => $marker, 
        ]);
        $assetB = Asset::factory()->create([
            'model_id'       => $modelB->id,
            'purchase_date'  => $purchaseDate->toDateString(),
            'asset_eol_date' => $purchaseDate->copy()->addMonths(24)->toDateString(),
            'order_number'   => $marker,
        ]);
        $assetC = Asset::factory()->create([
            'model_id'       => $modelC->id,
            'purchase_date'  => $purchaseDate->toDateString(),
            'asset_eol_date' => $purchaseDate->copy()->addMonths(36)->toDateString(),
            'order_number'   => $marker,
        ]);

        $filter = [
            [
                'field'    => 'asset_eol_date',
                'value'    => ['endDate' => Carbon::now()->addMonths(14)->toDateString()],
                'operator' => 'contains',
                'logic'    => 'AND',
            ],
            [
                'field'    => 'order_number',
                'value'    => $marker,
                'operator' => 'equals',
                'logic'    => 'AND',
            ],
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
        $this->assertFalse($results->contains($assetC));


    }
}

