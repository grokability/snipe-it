<?php
namespace Tests\Feature\AssetQuery\DateQuery;

use App\Models\Asset;
use App\Models\AssetModel;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DateQueryTest extends TestCase
{

    use RefreshDatabase;

    public function testPurchaseDateQueryStart()
    {
        // Assets
        $assetA = Asset::factory()->create(['purchase_date' => Carbon::now()->addDays(14)->format('Y-m-d')]);
        $assetB = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(14)->format('Y-m-d')]);
        $assetC = Asset::factory()->create(['purchase_date' => Carbon::now()->addMonths(14)->format('Y-m-d')]);

        $filter = [
            [
                'field' => 'purchase_date',
                'values' => [
                    'start' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                ],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetC));
        $this->assertTrue($results->contains($assetB));
        $this->assertFalse($results->contains($assetA));
    }

    public function testPurchaseDateQueryEnd()
    {
        // Assets
        $assetA = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(5)->format('Y-m-d')]);
        $assetB = Asset::factory()->create(['purchase_date' => Carbon::now()->addMonths(14)->format('Y-m-d')]);
        $assetC = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(10)->format('Y-m-d')]);

        $filter = [
            [
                'field' => 'purchase_date',
                'values' => [
                    'end' => Carbon::now()->addWeeks(7)->format('Y-m-d'),
                ],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
        $this->assertFalse($results->contains($assetC));
    }

    public function testPurchaseDateQueryRange()
    {
        // Assets
        $assetA = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(50)->format('Y-m-d')]);
        $assetB = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(75)->format('Y-m-d')]);
        $assetC = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(100)->format('Y-m-d')]);
        $assetD = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(125)->format('Y-m-d')]);
        $assetE = Asset::factory()->create(['purchase_date' => Carbon::now()->addWeeks(150)->format('Y-m-d')]);

        $filter = [
            [
                'field' => 'purchase_date',
                'values' => [
                    'start' => Carbon::now()->addWeeks(70)->format('Y-m-d'),
                    'end' => Carbon::now()->addWeeks(130)->format('Y-m-d'),
                ],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(3, $results);
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($assetC));
        $this->assertTrue($results->contains($assetD));
        $this->assertFalse($results->contains($assetA));
        $this->assertFalse($results->contains($assetE));
    }

    public function testEolDateQueryStart()
    {
        $modelA = AssetModel::factory()->create(['eol' => 30]);
        $modelB = AssetModel::factory()->create(['eol' => 20]);
        $modelC = AssetModel::factory()->create(['eol' => 10]);

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);
        $assetC = Asset::factory()->create(['model_id' => $modelC->id]);

        $filter = [
            [
                'field' => 'asset_eol_date',
                'values' => [
                    'start' => Carbon::now()->addMonths(12)->format('Y-m-d'),
                ],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertFalse($results->contains($assetC));
    }

    public function testEolDateQueryEnd()
    {
        $modelA = AssetModel::factory()->create(['eol' => 12]);
        $modelB = AssetModel::factory()->create(['eol' => 24]);
        $modelC = AssetModel::factory()->create(['eol' => 36]);

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);
        $assetC = Asset::factory()->create(['model_id' => $modelC->id]);

        $filter = [
            [
                'field' => 'asset_eol_date',
                'values' => [
                    'end' => Carbon::now()->addMonths(14)->format('Y-m-d'),
                ],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
        $this->assertFalse($results->contains($assetC));
    }
    public function testEolDateQueryRange()
    {

        $modelA = AssetModel::factory()->create(['eol' => 12]);
        $modelB = AssetModel::factory()->create(['eol' => 24]);
        $modelC = AssetModel::factory()->create(['eol' => 36]);
        $modelD = AssetModel::factory()->create(['eol' => 48]);
        $modelE = AssetModel::factory()->create(['eol' => 60]);

        // Assets
        $assetA = Asset::factory()->create(['model_id' => $modelA->id]);
        $assetB = Asset::factory()->create(['model_id' => $modelB->id]);
        $assetC = Asset::factory()->create(['model_id' => $modelC->id]);
        $assetD = Asset::factory()->create(['model_id' => $modelD->id]);
        $assetE = Asset::factory()->create(['model_id' => $modelE->id]);

        $filter = [
            [
                'field' => 'asset_eol_date',
                'values' => [
                    'start' => Carbon::now()->addMonths(20)->format('Y-m-d'),
                    'end' => Carbon::now()->addMonths(50)->format('Y-m-d'),
                ],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(3, $results);
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($assetC));
        $this->assertTrue($results->contains($assetD));
        $this->assertFalse($results->contains($assetA));
        $this->assertFalse($results->contains($assetE));
    }

    public function testCreatedAtDateQueryStart()
    {
        // Setup dates
        $yesterday = Carbon::now()->subDays(5)->startOfDay();
        $today = Carbon::now()->startOfDay();
        $tomorrow = Carbon::now()->addDays(5)->startOfDay();

        // Assets
        $assetA = Asset::factory()->create(['created_at' => $yesterday->toDateTimeString()]);
        $assetB = Asset::factory()->create(['created_at' => $today->toDateTimeString()]);
        $assetC = Asset::factory()->create(['created_at' => $tomorrow->toDateTimeString()]);

        // Filter: only include assets created on or after today
        $filter = [
            [
                'field' => 'created_at',
                'values' => [
                    'start' => $today->toDateString(),
                ],
                'operator' => 'contains', // Assuming your filter logic handles this correctly
                'logic' => 'AND',
            ]
        ];

        // Run the query
        $results = Asset::query()->byFilter($filter)->get();

        // Assertions
        $this->assertCount(2, $results);
        $this->assertFalse($results->contains($assetA)); // created yesterday
        $this->assertTrue($results->contains($assetB));  // created today
        $this->assertTrue($results->contains($assetC));  // created tomorrow
    }

    public function testCreatedAtDateQueryEnd()
    {
        // Setup dates
        $yesterday = Carbon::now()->subDays(1)->startOfDay();
        $today = Carbon::now()->startOfDay();
        $tomorrow = Carbon::now()->addDays(1)->startOfDay();

        // Assets
        $assetA = Asset::factory()->create(['created_at' => $yesterday->toDateTimeString()]);
        $assetB = Asset::factory()->create(['created_at' => $today->toDateTimeString()]);
        $assetC = Asset::factory()->create(['created_at' => $tomorrow->toDateTimeString()]);

        $filter = [
            [
                'field' => 'created_at',
                'values' => [
                    'end' => $today->toDateString(),
                ],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertFalse($results->contains($assetC));
    }
    public function testCreatedAtDateQueryRange()
    {
        // Setup dates
        $dayBeforeYesterday = Carbon::now()->subDays(2)->startOfDay();
        $yesterday = Carbon::now()->subDays(1)->startOfDay();
        $today = Carbon::now()->startOfDay();
        $tomorrow = Carbon::now()->addDays(1)->startOfDay();
        $dayAfterTomorrow = Carbon::now()->addDays(2)->startOfDay();

        // Assets
        $assetA = Asset::factory()->create(['created_at' => $dayBeforeYesterday->toDateTimeString()]);
        $assetB = Asset::factory()->create(['created_at' => $yesterday->toDateTimeString()]);
        $assetC = Asset::factory()->create(['created_at' => $today->toDateTimeString()]);
        $assetD = Asset::factory()->create(['created_at' => $tomorrow->toDateTimeString()]);
        $assetE = Asset::factory()->create(['created_at' => $dayAfterTomorrow->toDateTimeString()]);

        $filter = [
            [
                'field' => 'created_at',
                'values' => [
                    'start' => $yesterday->toDateString(),
                    'end' => $tomorrow->toDateString(),
                ],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(3, $results);
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($assetC));
        $this->assertTrue($results->contains($assetD));
        $this->assertFalse($results->contains($assetA));
        $this->assertFalse($results->contains($assetE));
    }

    public function testUpdatedAtDateQueryStart()
    {
        // Setup dates
        $yesterday = Carbon::now()->subDays(1)->startOfDay();
        $today = Carbon::now()->startOfDay();
        $tomorrow = Carbon::now()->addDays(1)->startOfDay();

        // Assets
        $assetA = Asset::factory()->create(['updated_at' => $yesterday->toDateTimeString()]);
        $assetB = Asset::factory()->create(['updated_at' => $today->toDateTimeString()]);
        $assetC = Asset::factory()->create(['updated_at' => $tomorrow->toDateTimeString()]);

        $filter = [
            [
                'field' => 'updated_at',
                'values' => [
                    'start' => $today->toDateString(),
                ],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertFalse($results->contains($assetC));
    }

    public function testUpdatedAtDateQueryEnd()
    {
        // Setup dates
        $yesterday = Carbon::now()->subDays(1)->startOfDay();
        $today = Carbon::now()->startOfDay();
        $tomorrow = Carbon::now()->addDays(1)->startOfDay();

        // Assets
        $assetA = Asset::factory()->create(['updated_at' => $yesterday->toDateTimeString()]);
        $assetB = Asset::factory()->create(['updated_at' => $today->toDateTimeString()]);
        $assetC = Asset::factory()->create(['updated_at' => $tomorrow->toDateTimeString()]);

        $filter = [
            [
                'field' => 'updated_at',
                'values' => [
                    'end' => $today->toDateString(),
                ],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertFalse($results->contains($assetC));
    }
    public function testUpdatedAtDateQueryRange()
    {
        // Setup dates
        $dayBeforeYesterday = Carbon::now()->subDays(2)->startOfDay();
        $yesterday = Carbon::now()->subDays(1)->startOfDay();
        $today = Carbon::now()->startOfDay();
        $tomorrow = Carbon::now()->addDays(1)->startOfDay();
        $dayAfterTomorrow = Carbon::now()->addDays(2)->startOfDay();

        // Assets
        $assetA = Asset::factory()->create(['updated_at' => $dayBeforeYesterday->toDateTimeString()]);
        $assetB = Asset::factory()->create(['updated_at' => $yesterday->toDateTimeString()]);
        $assetC = Asset::factory()->create(['updated_at' => $today->toDateTimeString()]);
        $assetD = Asset::factory()->create(['updated_at' => $tomorrow->toDateTimeString()]);
        $assetE = Asset::factory()->create(['updated_at' => $dayAfterTomorrow->toDateTimeString()]);

        $filter = [
            [
                'field' => 'updated_at',
                'values' => [
                    'start' => $yesterday->toDateString(),
                    'end' => $tomorrow->toDateString(),
                ],
                'operator' => 'contains',
                'logic' => 'AND',
            ]
        ];

        $results = Asset::query()->byFilter($filter)->get();

        $this->assertCount(3, $results);
        $this->assertTrue($results->contains($assetB));
        $this->assertTrue($results->contains($assetC));
        $this->assertTrue($results->contains($assetD));
        $this->assertFalse($results->contains($assetA));
        $this->assertFalse($results->contains($assetE));
    }
}