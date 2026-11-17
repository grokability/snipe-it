<?php

namespace Tests\Feature\PredefinedKits\Ui;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\PredefinedKit;
use App\Models\Statuslabel;
use App\Models\User;
use Tests\TestCase;

/**
 * Regression coverage for #19980. Checking out a predefined kit
 * nulled the `name` column on every included asset:
 * PredefinedKitCheckoutService::saveToDb() called
 * $asset->checkOut(..., $note, null). The sixth arg is the admin-
 * provided rename-at-checkout value, which Asset::checkOut() assigns
 * to $this->name unconditionally, so passing null wiped the asset's
 * name. Passing $asset->name preserves it across the save.
 */
class CheckoutKitPreservesAssetFieldsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_kit_checkout_preserves_asset_name(): void
    {
        $actor = User::factory()->checkoutAssets()->create();
        $target = User::factory()->create();

        $model = AssetModel::factory()->create();
        $asset = Asset::factory()->for($model, 'model')->create(['name' => 'FinanceLaptop-07']);

        $kit = PredefinedKit::factory()->create();
        $kit->models()->attach($model->id, ['quantity' => 1]);

        $this->actingAs($actor)
            ->post(route('kits.checkout.store', $kit), ['user_id' => $target->id])
            ->assertSessionHas('success');

        $this->assertSame(
            'FinanceLaptop-07',
            $asset->fresh()->name,
            'Kit checkout must preserve the asset name across the save.',
        );
    }
}
