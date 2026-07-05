<?php

namespace Tests\Unit;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
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

    public function test_percent_remaining_skips_queries_when_counts_were_eager_loaded(): void
    {
        // Api\AssetModelsController::index loads `remaining` and `assets_count`
        // on every model via withCount. percentRemaining() must read those
        // attributes instead of re-running the same counts — otherwise the
        // transformer loop turns into per-model N+1 (visible as duplicated
        // `count(*) … assets where model_id = ?` rows in /api/v1/models).
        $category = Category::factory()->create(['category_type' => 'asset']);
        $model = AssetModel::factory()->create(['category_id' => $category->id]);
        Asset::factory()->count(4)->create(['model_id' => $model->id]);

        // Simulate the withCount load by setting the raw attributes directly,
        // matching exactly what Api\AssetModelsController::index does.
        $model->forceFill(['remaining' => 3, 'assets_count' => 4]);

        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        $result = $model->percentRemaining();

        $this->assertSame(0, $queryCount, 'No queries should fire when the counts were eager-loaded');
        $this->assertSame(75.0, $result);
    }

    public function test_percent_remaining_only_calls_available_assets_count_once(): void
    {
        // Regression: the old implementation called $this->availableAssets()
        // ->count() twice — once for the zero guard, once for the ratio.
        // Under the API transformer loop in /api/v1/models that doubled the
        // status-label pluck + count(*) queries for every row. Pin the
        // single-call contract so a future cleanup doesn't reintroduce it.
        $category = Category::factory()->create(['category_type' => 'asset']);
        $model = AssetModel::factory()->create(['category_id' => $category->id]);
        Asset::factory()->count(2)->create(['model_id' => $model->id]);

        $availableCountQueries = 0;
        DB::listen(function ($query) use (&$availableCountQueries, $model) {
            // The availableAssets() relation generates a count(*) on assets
            // filtered by model_id AND assigned_to IS NULL.
            //
            // Strip identifier quotes so the matcher works regardless of
            // driver — sqlite emits `"assets"`, MySQL/MariaDB emits backticks.
            // Without this normalization the test passes on the sqlite CI
            // run and fails on the mysql one.
            $normalized = str_replace(['`', '"'], '', $query->sql);
            if (str_contains($normalized, 'count(*)')
                && str_contains($normalized, 'from assets')
                && str_contains($normalized, 'assigned_to is null')
                && in_array($model->id, $query->bindings, true)
            ) {
                $availableCountQueries++;
            }
        });

        $model->percentRemaining();

        $this->assertSame(1, $availableCountQueries, 'availableAssets()->count() must be called exactly once per percentRemaining()');
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
