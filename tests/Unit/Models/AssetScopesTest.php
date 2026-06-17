<?php

namespace Tests\Unit\Models;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AssetScopesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
        Asset::factory()->count(3)->create();
    }

    public static function noArgScopeProvider(): array
    {
        return [
            ['Hardware'], ['Pending'], ['RTD'], ['Undeployable'], ['NotArchived'],
            ['AssetsForShow'], ['Archived'], ['Deployed'], ['RequestableAssets'],
            ['OverdueForAudit'], ['OverdueForCheckin'],
        ];
    }

    #[DataProvider('noArgScopeProvider')]
    public function test_no_arg_scopes_return_collection(string $scope): void
    {
        $this->assertInstanceOf(Collection::class, Asset::{$scope}()->get());
    }

    public function test_settings_scopes(): void
    {
        $settings = Setting::getSettings();

        $this->assertInstanceOf(Collection::class, Asset::DueForAudit($settings)->get());
        $this->assertInstanceOf(Collection::class, Asset::DueOrOverdueForAudit($settings)->get());
        $this->assertInstanceOf(Collection::class, Asset::DueForCheckin($settings)->get());
    }

    public function test_order_scopes(): void
    {
        foreach (['OrderModels', 'OrderStatus', 'OrderCompany', 'OrderCategory', 'OrderManufacturer', 'OrderLocation', 'OrderSupplier'] as $scope) {
            $this->assertInstanceOf(Collection::class, Asset::{$scope}('asc')->get());
        }
    }

    public function test_filter_by_id_scopes(): void
    {
        $category = Category::factory()->forAssets()->create();
        $manufacturer = Manufacturer::factory()->create();
        $location = Location::factory()->create();

        $this->assertInstanceOf(Collection::class, Asset::InCategory($category->id)->get());
        $this->assertInstanceOf(Collection::class, Asset::ByManufacturer($manufacturer->id)->get());
        $this->assertInstanceOf(Collection::class, Asset::ByLocationId($location->id)->get());
    }
}
