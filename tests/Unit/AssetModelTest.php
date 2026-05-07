<?php

namespace Tests\Unit;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use Tests\TestCase;

class AssetModelTest extends TestCase
{
    public function test_an_asset_model_contains_assets()
    {
        $category = Category::factory()->create([
            'category_type' => 'asset',
        ]);
        $model = AssetModel::factory()->create([
            'category_id' => $category->id,
        ]);

        $asset = Asset::factory()->create([
            'model_id' => $model->id,
        ]);
        $this->assertEquals(1, $model->assets()->count());
    }

    public function test_percent_remaining_returns_zero_when_no_assets_are_available()
    {
        $model = new class extends AssetModel
        {
            public function availableAssets()
            {
                return new class
                {
                    public function count()
                    {
                        return 0;
                    }
                };
            }

            public function assets()
            {
                return new class
                {
                    public function count()
                    {
                        return 10;
                    }
                };
            }
        };

        $this->assertEquals(0, $model->percentRemaining());
    }

    public function test_percent_remaining_returns_expected_ratio_for_mixed_availability()
    {
        $model = new class extends AssetModel
        {
            public function availableAssets()
            {
                return new class
                {
                    public function count()
                    {
                        return 2;
                    }
                };
            }

            public function assets()
            {
                return new class
                {
                    public function count()
                    {
                        return 5;
                    }
                };
            }
        };

        $this->assertEquals(40.0, $model->percentRemaining());
    }

    public function test_percent_remaining_returns_one_hundred_when_all_assets_are_available()
    {
        $model = new class extends AssetModel
        {
            public function availableAssets()
            {
                return new class
                {
                    public function count()
                    {
                        return 4;
                    }
                };
            }

            public function assets()
            {
                return new class
                {
                    public function count()
                    {
                        return 4;
                    }
                };
            }
        };

        $this->assertEquals(100.0, $model->percentRemaining());
    }
}
