<?php
namespace Tests\Unit;

use App\Models\Asset;
use App\Models\Depreciable;
use App\Models\Depreciation;
use App\Models\Category;
use App\Models\License;
use App\Models\AssetModel;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DepreciationTest extends TestCase
{
    public function testADepreciationHasModels()
    {
        $depreciation = Depreciation::factory()->create();

        AssetModel::factory()
                    ->count(5)
                    ->create(
                        [
                            'category_id' => Category::factory()->assetLaptopCategory()->create(),
                            'depreciation_id' => $depreciation->id               
                        ]);
        
        $this->assertEquals(5, $depreciation->models->count());
    }
    public function testDepreciationAmount()
    {
        $depreciation = Depreciation::factory()->create([
            'depreciation_type' => 'amount',
            'depreciation_min' => 1000,
            'months'=> 36,
        ]);

        $asset = Asset::factory()
            ->laptopMbp()
            ->create(
                [
                    'category_id' => Category::factory()->assetLaptopCategory()->create(),
                    'purchase_date' => now()->subDecade(),
                    'purchase_cost' => 4000,
                ]);
        $asset->model->update([
            'depreciation_id' => $depreciation->id,
        ]);

        $asset->getLinearDepreciatedValue();

        $this->assertEquals($depreciation->depreciation_min, $asset->getLinearDepreciatedValue());
    }
    public function testDepreciationPercentage()
    {
        $depreciation = Depreciation::factory()->create([
            'depreciation_type' => 'percent',
            'depreciation_min' => 50,
            'months'=> 36,
        ]);

        $asset = Asset::factory()
            ->laptopMbp()
            ->create(
                [
                    'category_id' => Category::factory()->assetLaptopCategory()->create(),
                    'purchase_date' => now()->subDecade(),
                    'purchase_cost' => 4000,
                ]);
        $asset->model->update([
            'depreciation_id' => $depreciation->id,
        ]);

        $asset->getLinearDepreciatedValue();

        $this->assertEquals(2000, $asset->getLinearDepreciatedValue());
    }

    public function testADepreciationHasLicenses()
    {

        $depreciation = Depreciation::factory()->create();
        License::factory()
                    ->count(5)
                    ->photoshop()
                    ->create(
                        [
                            'category_id' => Category::factory()->licenseGraphicsCategory()->create(),
                            'depreciation_id' => $depreciation->id               
                        ]);

        $this->assertEquals(5, $depreciation->licenses()->count());
    }
}
