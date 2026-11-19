<?php

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

test('an asset model contains assets', function () {
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
});

test('percent remaining returns zero when no assets are available', function () {
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

    expect($model->percentRemaining())->toEqual(0);
});

test('percent remaining returns expected ratio for mixed availability', function () {
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

    expect($model->percentRemaining())->toEqual(40.0);
});

test('percent remaining skips queries when counts were eager loaded', function () {
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

    expect($queryCount)->toBe(0, 'No queries should fire when the counts were eager-loaded');
    expect($result)->toBe(75.0);
});

test('percent remaining only calls available assets count once', function () {
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

    expect($availableCountQueries)->toBe(1, 'availableAssets()->count() must be called exactly once per percentRemaining()');
});

test('percent remaining returns one hundred when all assets are available', function () {
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

    expect($model->percentRemaining())->toEqual(100.0);
});

test('percent remaining returns zero when eager loaded total is zero but available is not', function () {
    // Regression for a production DivisionByZeroError at
    // AssetModel::percentRemaining. In principle
    //   availableAssets ⊆ assets
    // so `remaining > 0 && assets_count === 0` shouldn't happen, but a race
    // between the two correlated withCount subqueries — or a data anomaly
    // where an asset row is visible to one count but not the other — has
    // produced exactly that shape in the wild. percentRemaining must
    // absorb it, not throw.
    $category = Category::factory()->create(['category_type' => 'asset']);
    $model = AssetModel::factory()->create(['category_id' => $category->id]);
    $model->forceFill(['remaining' => 3, 'assets_count' => 0]);

    expect($model->percentRemaining())->toBe(0);
});

test('percent remaining returns zero when eager loaded counts are string zero', function () {
    // PDO with MySQL/MariaDB in the default (emulated-prepares) mode
    // returns COUNT() as PHP string, not int. Eloquent doesn't cast
    // withCount aliases (no $casts entry for `remaining` / `assets_count`),
    // so the value lands on the model as-is. A strict `=== 0` guard misses
    // string "0" — and PHP 8's arithmetic operators auto-convert numeric
    // strings, so the ratio below still throws DivisionByZeroError. The
    // fix is to cast to int at read time before the guard fires.
    //
    // Real trace from the reporting production instance targeted the
    // ratio line even with a === 0 guard sitting above it, which is only
    // possible if $total was "0" (string), not 0 (int).
    $category = Category::factory()->create(['category_type' => 'asset']);
    $model = AssetModel::factory()->create(['category_id' => $category->id]);
    $model->forceFill(['remaining' => '3', 'assets_count' => '0']);

    expect($model->percentRemaining())->toBe(0);
});

test('percent remaining returns zero when runtime total is zero but available is not', function () {
    // Same guard, exercised through the non-eager-loaded fallback path
    // (Api\AssetModelsController::show and any callers that render a
    // single model without the index endpoint's withCount).
    $model = new class extends AssetModel
    {
        public function availableAssets()
        {
            return new class
            {
                public function count()
                {
                    return 3;
                }
            };
        }

        public function assets()
        {
            return new class
            {
                public function count()
                {
                    return 0;
                }
            };
        }
    };

    expect($model->percentRemaining())->toBe(0);
});
