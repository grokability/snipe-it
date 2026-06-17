<?php

namespace Tests\Unit\Actions;

use App\Actions\Categories\DestroyCategoryAction;
use App\Actions\CheckoutRequests\CreateCheckoutRequestAction;
use App\Actions\Manufacturers\DeleteManufacturerAction;
use App\Actions\Suppliers\DestroySupplierAction;
use App\Exceptions\AssetNotRequestable;
use App\Exceptions\ItemStillHasAccessories;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Tests\TestCase;

class DestroyActionsTest extends TestCase
{
    public function test_destroy_empty_category_returns_true(): void
    {
        $category = Category::factory()->forAccessories()->create();

        $this->assertTrue(DestroyCategoryAction::run($category));
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_destroy_category_with_accessories_throws(): void
    {
        $category = Category::factory()->forAccessories()->create();
        Accessory::factory()->create(['category_id' => $category->id]);

        $this->expectException(ItemStillHasAccessories::class);
        DestroyCategoryAction::run($category);
    }

    public function test_destroy_empty_manufacturer_returns_true(): void
    {
        $manufacturer = Manufacturer::factory()->create();

        $this->assertTrue(DeleteManufacturerAction::run($manufacturer));
    }

    public function test_destroy_empty_supplier_returns_true(): void
    {
        $supplier = Supplier::factory()->create();

        $this->assertTrue(DestroySupplierAction::run($supplier));
    }

    public function test_create_checkout_request_throws_for_non_requestable_asset(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        // requestable=false garantiza que RequestableAssets() no lo encuentre.
        $asset = Asset::factory()->create(['requestable' => false]);
        $user = User::factory()->create();

        $this->expectException(AssetNotRequestable::class);
        CreateCheckoutRequestAction::run($asset, $user);
    }
}
