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
use Tests\TestCase;

class AssetTest extends TestCase
{
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
        $this->assertEquals(Setting::getSettings()->next_auto_tag_base, $matches[0] + 1, "Next auto increment number should be the last normally-saved one plus one, but isn't");
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
        $this->assertEquals(Setting::getSettings()->next_auto_tag_base, $matches[0] + 1, "Next auto increment number should be the last normally-saved one plus one, but isn't");
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
        $user = User::factory()->create();
        $asset = Asset::factory()->create([
            'assigned_to' => $user->id,
        ]);
        $this->assertModelMissing($asset);
    }

    public function test_get_image_url_method()
    {
        $urlBase = config('filesystems.disks.public.url');

        $category = Category::factory()->create(['image' => 'category-image.jpg']);
        $model = AssetModel::factory()->for($category)->create(['image' => 'asset-model-image.jpg']);
        $asset = Asset::factory()->for($model, 'model')->create(['image' => 'asset-image.jpg']);

        $this->assertEquals(
            "{$urlBase}/assets/asset-image.jpg",
            $asset->getImageUrl()
        );

        $asset->update(['image' => null]);

        $this->assertEquals(
            "{$urlBase}/models/asset-model-image.jpg",
            $asset->refresh()->getImageUrl()
        );

        $model->update(['image' => null]);

        $this->assertEquals(
            "{$urlBase}/categories/category-image.jpg",
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

        $primaryAccessory = Accessory::factory()->create(['purchase_cost' => 10]);
        $secondaryAccessory = Accessory::factory()->create(['purchase_cost' => 15]);

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

        $firstComponent = Component::factory()->create(['purchase_cost' => 10]);
        $secondComponent = Component::factory()->create(['purchase_cost' => 25]);

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

        $this->assertEquals(35, $freshAsset->components->sum('purchase_cost'));
        $this->assertEquals(80, $freshAsset->components->sum('calculated_purchase_cost'));
        $this->assertSame(80.0, $freshAsset->getComponentCost());
    }

    public function test_asset_components_calculated_total_treats_null_purchase_cost_as_zero(): void
    {
        $asset = Asset::factory()->create();

        $componentWithoutCost = Component::factory()->create(['purchase_cost' => null]);

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
