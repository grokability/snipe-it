<?php

namespace Tests\Unit;

use App\Http\Controllers\Assets\BulkAssetsController;
use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Component;
use App\Models\Depreciation;
use App\Models\Setting;
use App\Models\Statuslabel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AssetTest extends TestCase
{
    public function test_required_serial_is_checked_by_validation_and_save_without_changing_rules(): void
    {
        $model = AssetModel::factory()->create(['require_serial' => 1]);
        $asset = Asset::factory()->make(['model_id' => $model->id, 'serial' => null]);
        $rules = $asset->getRules();

        $this->assertFalse($asset->isValid());
        $this->assertTrue($asset->getErrors()->has('serial'));
        $this->assertFalse($asset->save());
        $this->assertSame($rules, $asset->getRules());

        $asset->serial = 'REQUIRED-SERIAL-1';

        $this->assertTrue($asset->isValid());
        $this->assertTrue($asset->save());
        $this->assertSame($rules, $asset->getRules());
    }

    public function test_serial_requirement_is_rechecked_when_switching_models(): void
    {
        $requiredModel = AssetModel::factory()->create(['require_serial' => 1]);
        $optionalModel = AssetModel::factory()->create(['require_serial' => 0]);
        $asset = Asset::factory()->make(['model_id' => $requiredModel->id, 'serial' => null]);

        $this->assertFalse($asset->save());

        $asset->model()->associate($optionalModel);

        $this->assertTrue($asset->isValid());
        $this->assertTrue($asset->save());

        $asset->model()->associate($requiredModel);

        $this->assertFalse($asset->isValid());
        $this->assertFalse($asset->save());
        $this->assertTrue($asset->getErrors()->has('serial'));
    }

    public function test_auto_increment()
    {
        $this->settings->enableAutoIncrement();

        $a = Asset::factory()->create(['asset_tag' => Asset::autoincrement_asset()]);
        $b = Asset::factory()->create(['asset_tag' => Asset::autoincrement_asset()]);

        $this->assertModelExists($a);
        $this->assertModelExists($b);

    }

    public function test_auto_increment_collision()
    {
        $this->settings->enableAutoIncrement();

        // we have to do this by hand to 'simulate' two web pages being open at the same time
        $a = Asset::factory()->make(['asset_tag' => Asset::autoincrement_asset()]);
        $b = Asset::factory()->make(['asset_tag' => Asset::autoincrement_asset()]);

        $this->assertTrue($a->save());
        $this->assertFalse($b->save());
    }

    public function test_auto_increment_double()
    {
        // make one asset with the autoincrement *ONE* higher than the next auto-increment
        // make sure you can then still make another
        $this->settings->enableAutoIncrement();

        $gap_number = Asset::autoincrement_asset(1);
        $final_number = Asset::autoincrement_asset(2);
        $a = Asset::factory()->make(['asset_tag' => $gap_number]); // make an asset with an ID that is one *over* the next increment
        $b = Asset::factory()->make(['asset_tag' => Asset::autoincrement_asset()]); // but also make one with one that is *at* the next increment
        $this->assertTrue($a->save());
        $this->assertTrue($b->save());

        // and ensure a final asset ends up at *two* over what would've been the next increment at the start
        $c = Asset::factory()->make(['asset_tag' => Asset::autoincrement_asset()]);
        $this->assertTrue($c->save());
        $this->assertEquals($c->asset_tag, $final_number);
    }

    public function test_auto_increment_gap_and_backfill()
    {
        // make one asset 3 higher than the next auto-increment
        // manually make one that's 1 lower than that
        // make sure the next one is one higher than the 3 higher one.
        $this->settings->enableAutoIncrement();

        $big_gap = Asset::autoincrement_asset(3);
        $final_result = Asset::autoincrement_asset(4);
        $backfill_one = Asset::autoincrement_asset(0);
        $backfill_two = Asset::autoincrement_asset(1);
        $backfill_three = Asset::autoincrement_asset(2);
        $a = Asset::factory()->create(['asset_tag' => $big_gap]);
        $this->assertModelExists($a);

        $b = Asset::factory()->create(['asset_tag' => $backfill_one]);
        $this->assertModelExists($b);

        $c = Asset::factory()->create(['asset_tag' => $backfill_two]);
        $this->assertModelExists($c);

        $d = Asset::factory()->create(['asset_tag' => $backfill_three]);
        $this->assertModelExists($d);

        $final = Asset::factory()->create(['asset_tag' => Asset::autoincrement_asset()]);
        $this->assertModelExists($final);
        $this->assertEquals($final->asset_tag, $final_result);
    }

    public function test_prefixless_autoincrement_backfill()
    {
        // TODO: COPYPASTA FROM above, is there a way to still run this test but not have it be so duplicative?
        $this->settings->enableAutoIncrement()->set(['auto_increment_prefix' => '']);

        $big_gap = Asset::autoincrement_asset(3);
        $final_result = Asset::autoincrement_asset(4);
        $backfill_one = Asset::autoincrement_asset(0);
        $backfill_two = Asset::autoincrement_asset(1);
        $backfill_three = Asset::autoincrement_asset(2);
        $a = Asset::factory()->create(['asset_tag' => $big_gap]);
        $this->assertModelExists($a);

        $b = Asset::factory()->create(['asset_tag' => $backfill_one]);
        $this->assertModelExists($b);

        $c = Asset::factory()->create(['asset_tag' => $backfill_two]);
        $this->assertModelExists($c);

        $d = Asset::factory()->create(['asset_tag' => $backfill_three]);
        $this->assertModelExists($d);

        $final = Asset::factory()->create(['asset_tag' => Asset::autoincrement_asset()]);
        $this->assertModelExists($final);
        $this->assertEquals($final->asset_tag, $final_result);
    }

    public function test_unzerofilled_prefixless_autoincrement_backfill()
    {
        // TODO: COPYPASTA FROM above (AGAIN), is there a way to still run this test but not have it be so duplicative?
        $this->settings->enableAutoIncrement()->set(['auto_increment_prefix' => '', 'zerofill_count' => 0]);

        $big_gap = Asset::autoincrement_asset(3);
        $final_result = Asset::autoincrement_asset(4);
        $backfill_one = Asset::autoincrement_asset(0);
        $backfill_two = Asset::autoincrement_asset(1);
        $backfill_three = Asset::autoincrement_asset(2);
        $a = Asset::factory()->create(['asset_tag' => $big_gap]);
        $this->assertModelExists($a);

        $b = Asset::factory()->create(['asset_tag' => $backfill_one]);
        $this->assertModelExists($b);

        $c = Asset::factory()->create(['asset_tag' => $backfill_two]);
        $this->assertModelExists($c);

        $d = Asset::factory()->create(['asset_tag' => $backfill_three]);
        $this->assertModelExists($d);

        $final = Asset::factory()->create(['asset_tag' => Asset::autoincrement_asset()]);
        $this->assertModelExists($final);
        $this->assertEquals($final->asset_tag, $final_result);
    }

    public function test_auto_increment_big()
    {
        $this->settings->enableAutoIncrement();

        // we have to do this by hand to 'simulate' two web pages being open at the same time
        $a = Asset::factory()->make(['asset_tag' => Asset::autoincrement_asset()]);
        $b = Asset::factory()->make(['asset_tag' => 'ABCD'.(PHP_INT_MAX - 1)]);

        $this->assertTrue($a->save());
        $this->assertTrue($b->save());
        $matches = [];
        preg_match('/\d+/', $a->asset_tag, $matches);
        // AssetObserver::created updates next_auto_tag_base via a
        // query-builder UPDATE and intentionally does NOT mutate the
        // static Setting cache. Read the committed value
        // off the DB rather than the cache.
        $committedBase = (int) DB::table('settings')->value('next_auto_tag_base');
        $this->assertEquals($committedBase, $matches[0] + 1, "Next auto increment number should be the last normally-saved one plus one, but isn't");
    }

    public function test_concurrent_asset_create_does_not_regress_next_auto_tag_base()
    {
        // Regression test for issue #19744. Two parallel asset-create
        // requests both read next_auto_tag_base, then both try to write
        // their own catch-up value. If the second writer naively saves,
        // it clobbers the first writer's higher value. The fix (and
        // this test) uses a conditional UPDATE that leaves the higher
        // value alone.
        $this->settings->enableAutoIncrement();

        // Prime the in-process Setting cache so the observer later reads
        // this stale object instead of refetching from the DB.
        $settings = Setting::getSettings();
        $staleBase = $settings->next_auto_tag_base;

        // Simulate a concurrent writer that already won the race and
        // bumped next_auto_tag_base past our stale read. Direct DB
        // update bypasses the Setting-save cache invalidation, which
        // is exactly what a sibling PHP process on the same DB would
        // do from this process's point of view.
        $winnerBase = $staleBase + 500;
        DB::table('settings')->where('id', $settings->id)->update([
            'next_auto_tag_base' => $winnerBase,
        ]);

        // Create an asset at the stale value. The observer will see
        // the cached $settings (which still thinks the base is
        // $staleBase) and try to catch next_auto_tag_base up to
        // $staleBase + 1.
        $asset = Asset::factory()->create([
            'asset_tag' => 'ABCD'.$staleBase,
        ]);
        $this->assertModelExists($asset);

        // The DB value must still be the higher value from the
        // simulated concurrent writer, not clobbered down to our
        // smaller catch-up.
        $actual = DB::table('settings')->where('id', $settings->id)->value('next_auto_tag_base');
        $this->assertEquals($winnerBase, $actual, 'next_auto_tag_base was regressed by a stale catch-up write');
    }

    public function test_rolled_back_asset_create_does_not_inflate_next_auto_tag_base_for_subsequent_creates()
    {
        // Regression for #19954. A sync-adapter record wraps its
        // asset create in DB::transaction. Before the fix, AssetObserver's
        // $settings->refresh() mutated the static Setting cache
        // instance in-process. When a later step in the same record
        // failed and the transaction rolled back, the DB UPDATE to
        // next_auto_tag_base rolled back but the inflated cache
        // survived. Subsequent records in the same long-running
        // process generated asset tags far beyond the committed DB
        // value.
        $this->settings->enableAutoIncrement();
        $baselineBase = Setting::getSettings()->next_auto_tag_base;

        try {
            DB::transaction(function () {
                Asset::factory()->create(['asset_tag' => Asset::autoincrement_asset()]);
                // Simulate the sync-adapter record failing after the
                // asset-save observer ran, forcing a rollback of the
                // transaction that bumped next_auto_tag_base.
                throw new \RuntimeException('simulated sync record failure');
            });
            $this->fail('Transaction should have rolled back.');
        } catch (\RuntimeException $e) {
            $this->assertSame('simulated sync record failure', $e->getMessage());
        }

        // Committed DB value must still be the pre-create baseline.
        $this->assertSame(
            $baselineBase,
            (int) DB::table('settings')->value('next_auto_tag_base'),
            'Rolled-back asset create must not advance the committed next_auto_tag_base.',
        );

        // The next autoincrement call must honor the DB value, not a
        // stale cache instance that still remembers the aborted bump.
        $nextTag = Asset::autoincrement_asset();
        preg_match('/\d+/', $nextTag, $matches);
        $this->assertSame(
            $baselineBase,
            (int) $matches[0],
            'autoincrement_asset must read next_auto_tag_base from the committed DB state after a rollback.',
        );
    }

    public function test_rolled_back_asset_create_does_not_inflate_legacy_increment_path()
    {
        // Legacy branch (no auto_increment_prefix, non-numeric tags
        // etc.) runs the else-branch increment path in
        // AssetObserver::created. Same rollback-safety expectation.
        $this->settings->enableAutoIncrement()->set(['auto_increment_prefix' => '']);
        $baselineBase = (int) DB::table('settings')->value('next_auto_tag_base');

        try {
            DB::transaction(function () {
                // A non-numeric asset_tag drives the observer down the
                // legacy increment path instead of the parse-and-UPDATE
                // branch.
                Asset::factory()->create(['asset_tag' => 'manual-tag-rollback']);
                throw new \RuntimeException('simulated legacy-path failure');
            });
            $this->fail('Transaction should have rolled back.');
        } catch (\RuntimeException $e) {
            $this->assertSame('simulated legacy-path failure', $e->getMessage());
        }

        $this->assertSame(
            $baselineBase,
            (int) DB::table('settings')->value('next_auto_tag_base'),
            'Legacy-path increment must not survive a transaction rollback.',
        );
    }

    public function test_auto_increment_almost_big()
    {
        // TODO: this looks pretty close to the one above, could we maybe squish them together?
        $this->settings->enableAutoIncrement();

        // we have to do this by hand to 'simulate' two web pages being open at the same time
        $a = Asset::factory()->make(['asset_tag' => Asset::autoincrement_asset()]);
        $b = Asset::factory()->make(['asset_tag' => 'ABCD'.(PHP_INT_MAX - 2)]);

        $this->assertTrue($a->save());
        $this->assertTrue($b->save());
        $matches = [];
        preg_match('/\d+/', $b->asset_tag, $matches); // this is *b*, not *a* - slight difference from above test
        // See the DB-vs-cache rationale in test_auto_increment_big (#19954).
        $committedBase = (int) DB::table('settings')->value('next_auto_tag_base');
        $this->assertEquals($committedBase, $matches[0] + 1, "Next auto increment number should be the last normally-saved one plus one, but isn't");
    }

    public function test_warranty_expires_attribute()
    {

        $asset = Asset::factory()
            ->create(
                [
                    'model_id' => AssetModel::factory()
                        ->create(
                            [
                                'category_id' => Category::factory()->assetLaptopCategory()->create()->id,
                            ]
                        )->id,
                    'warranty_months' => 24,
                    'purchase_date' => Carbon::createFromDate(2017, 1, 1)->hour(0)->minute(0)->second(0),
                ]);

        $this->assertEquals(Carbon::createFromDate(2017, 1, 1)->format('Y-m-d'), $asset->purchase_date->format('Y-m-d'));
        $this->assertEquals(Carbon::createFromDate(2019, 1, 1)->format('Y-m-d'), $asset->warranty_expires->format('Y-m-d'));

    }

    public function test_warranty_expires_stays_in_the_last_month_for_a_purchase_late_in_the_month()
    {
        $asset = Asset::factory()->create([
            'warranty_months' => 1,
            'purchase_date' => '2026-08-31',
        ]);
        $this->assertEquals('2026-09-30', $asset->warranty_expires->format('Y-m-d'));

        $asset = Asset::factory()->create([
            'warranty_months' => 1,
            'purchase_date' => '2026-01-31',
        ]);
        $this->assertEquals('2026-02-28', $asset->warranty_expires->format('Y-m-d'));
    }

    public function test_eol_progress_percent_returns_expected_value(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 1, 0, 0, 0));

        try {
            $asset = Asset::factory()->create([
                'purchase_date' => '2025-01-01',
                'eol_explicit' => 1,
            ]);

            $asset->asset_eol_date = '2027-01-01';
            $asset->save();
            $asset->refresh();

            $this->assertSame(50.0, $asset->eolProgressPercent());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_depreciation_progress_percent_returns_expected_value(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 1, 0, 0, 0));

        try {
            $depreciation = Depreciation::factory()->create(['months' => 24]);
            $model = AssetModel::factory()->create(['depreciation_id' => $depreciation->id]);

            $asset = Asset::factory()->create([
                'model_id' => $model->id,
                'purchase_date' => '2025-01-01',
            ]);

            $this->assertSame(50.0, $asset->depreciationProgressPercent());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_warranty_progress_percent_returns_zero_without_required_dates(): void
    {
        $asset = Asset::factory()->create([
            'purchase_date' => null,
            'warranty_months' => 24,
        ]);

        $this->assertSame(0.0, $asset->warrantyProgressPercent());
    }

    public function test_assigned_type_without_assign_to()
    {
        // Model-level rule: assigned_type is required_with:assigned_to.
        // assigned_to is not in $fillable so we use a raw DB write to simulate
        // legacy / inconsistent data, then verify the model treats the row as invalid.
        $user = User::factory()->create();
        $asset = Asset::factory()->create();

        DB::table('assets')->where('id', $asset->id)->update(['assigned_to' => $user->id, 'assigned_type' => null]);
        $asset->refresh();

        $this->assertFalse($asset->isValid());
    }

    public function test_get_image_url_method()
    {
        // Build expected URLs through the same disk the model uses so
        // the assertion stays valid whether or not the public disk is
        // faked (base setUp fakes it under --parallel).
        $publicDisk = \Illuminate\Support\Facades\Storage::disk('public');

        $category = Category::factory()->create(['image' => 'category-image.jpg']);
        $model = AssetModel::factory()->for($category)->create(['image' => 'asset-model-image.jpg']);
        $asset = Asset::factory()->for($model, 'model')->create(['image' => 'asset-image.jpg']);

        $this->assertEquals(
            $publicDisk->url('assets/asset-image.jpg'),
            $asset->getImageUrl()
        );

        $asset->update(['image' => null]);

        $this->assertEquals(
            $publicDisk->url('models/asset-model-image.jpg'),
            $asset->refresh()->getImageUrl()
        );

        $model->update(['image' => null]);

        $this->assertEquals(
            $publicDisk->url('categories/category-image.jpg'),
            $asset->refresh()->getImageUrl()
        );

        $category->image = null;
        $category->save();

        $this->assertFalse($asset->refresh()->getImageUrl());

        // handles case where model does not exist
        $asset->model_id = 9999999;
        $asset->forceSave();

        $this->assertFalse($asset->refresh()->getImageUrl());
    }

    public function test_undeployable_status_returns_falseif_asset_is_deployable()
    {
        $assets = Asset::factory()->count(3)->create();
        $asset_ids = $assets->pluck('id')->toArray();

        $bulk_assets = new BulkAssetsController;

        $result = $bulk_assets->hasUndeployableStatus($asset_ids);

        $this->assertFalse($result);
    }

    public function test_undeployable_status_returns_trueand_tags_if_asset_is_un_deployable()
    {
        $deployable = Asset::factory()->create();
        $undeployableStatus = Statuslabel::factory()->create(['deployable' => 0]);
        $undeployable = Asset::factory()->create(
            [
                'status_id' => $undeployableStatus->id,
            ]);

        $bulk_assets = new BulkAssetsController;

        $result = $bulk_assets->hasUndeployableStatus([$deployable->id, $undeployable->id]);

        $this->assertIsArray($result);
        $this->assertTrue($result['status']);
        $this->assertEquals($undeployable->id, $result['tags'][0]['id']);
        $this->assertEquals($undeployable->asset_tag, $result['tags'][0]['asset_tag']);
    }

    public function test_undeployable_status_check_filters_out_undeployable_ids()
    {
        $deployable = Asset::factory()->create();
        $undeployableStatus = Statuslabel::factory()->create(['deployable' => 0]);
        $undeployable = Asset::factory()->create(
            [
                'status_id' => $undeployableStatus->id,
            ]);

        $bulk_assets = new BulkAssetsController;

        $result = $bulk_assets->hasUndeployableStatus([$deployable->id, $undeployable->id]);

        $undeployableIds = array_column($result['tags'], 'id');
        $filtered = array_diff([$deployable->id, $undeployable->id], $undeployableIds);

        $this->assertEquals([$deployable->id], array_values($filtered));
    }

    public function test_asset_accessories_relationship_uses_accessory_checkout_rows(): void
    {
        $asset = Asset::factory()->create();
        $otherAsset = Asset::factory()->create();

        // See test_asset_components_calculated_total_uses_assigned_quantity
        // for why per-unit cost must be seeded via withInitialAcquisition
        // rather than a create-array override.
        $primaryAccessory = Accessory::factory()->withInitialAcquisition(null, 10.0)->create();
        $secondaryAccessory = Accessory::factory()->withInitialAcquisition(null, 15.0)->create();

        AccessoryCheckout::factory()->create([
            'accessory_id' => $primaryAccessory->id,
            'assigned_to' => $asset->id,
            'assigned_type' => Asset::class,
        ]);
        AccessoryCheckout::factory()->create([
            'accessory_id' => $primaryAccessory->id,
            'assigned_to' => $asset->id,
            'assigned_type' => Asset::class,
        ]);
        AccessoryCheckout::factory()->create([
            'accessory_id' => $secondaryAccessory->id,
            'assigned_to' => $asset->id,
            'assigned_type' => Asset::class,
        ]);
        AccessoryCheckout::factory()->create([
            'accessory_id' => $primaryAccessory->id,
            'assigned_to' => $otherAsset->id,
            'assigned_type' => Asset::class,
        ]);

        $this->assertCount(3, $asset->accessories()->get());
        $this->assertSame(35.0, $asset->getAccessoryCost());
    }

    public function test_asset_components_calculated_total_uses_assigned_quantity(): void
    {
        $asset = Asset::factory()->create();

        // Per-unit cost lives on the initial OrderItem the observer
        // writes; withInitialAcquisition seeds that OrderItem's price so
        // calculatedPurchaseCost (via lastOrderDefaults) reads the
        // expected value back for this test.
        $firstComponent = Component::factory()->withInitialAcquisition(null, 10.0)->create();
        $secondComponent = Component::factory()->withInitialAcquisition(null, 25.0)->create();

        $asset->components()->attach($firstComponent->id, [
            'assigned_qty' => 3,
            'created_by' => User::factory()->create()->id,
            'created_at' => now(),
        ]);

        $asset->components()->attach($secondComponent->id, [
            'assigned_qty' => 2,
            'created_by' => User::factory()->create()->id,
            'created_at' => now(),
        ]);

        $freshAsset = $asset->fresh();

        $this->assertEquals(80, $freshAsset->components->sum('calculated_purchase_cost'));
        $this->assertSame(80.0, $freshAsset->getComponentCost());
    }

    public function test_asset_components_calculated_total_treats_null_purchase_cost_as_zero(): void
    {
        $asset = Asset::factory()->create();

        // Force the observer-written OrderItem's price to null AND null
        // out the parent's default_purchase_cost so lastOrderDefaults
        // returns unit_cost=null; calculatedPurchaseCost coerces to 0.0
        // when assigned to an asset.
        $componentWithoutCost = Component::factory()->create(['default_purchase_cost' => null]);
        $componentWithoutCost->orderItems()->update(['price' => null]);

        $asset->components()->attach($componentWithoutCost->id, [
            'assigned_qty' => 4,
            'created_by' => User::factory()->create()->id,
            'created_at' => now(),
        ]);

        $freshAsset = $asset->fresh();

        $this->assertSame(0.0, $freshAsset->components->first()->calculated_purchase_cost);
        $this->assertEquals(0, $freshAsset->components->sum('calculated_purchase_cost'));
        $this->assertSame(0.0, $freshAsset->getComponentCost());
    }
}
