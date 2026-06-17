<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\Depreciation;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Ejercita los scopes de ordenamiento (Order*) y filtros simples de los
 * modelos de catalogo/inventario. Son consultas baratas de cubrir.
 */
class ModelOrderScopesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public static function scopeProvider(): array
    {
        return [
            [Company::class, ['OrderByCreatedBy']],
            [Manufacturer::class, ['OrderByCreatedBy']],
            [Supplier::class, ['OrderByCreatedByName']],
            [Depreciation::class, ['OrderByCreatedBy']],
            [Location::class, ['OrderParent', 'OrderManager', 'OrderCompany', 'OrderByCreatedByName']],
            [Component::class, ['OrderCategory', 'OrderLocation', 'OrderCompany', 'OrderSupplier', 'OrderManufacturer', 'OrderByCreatedBy']],
            [Consumable::class, ['OrderCategory', 'OrderLocation', 'OrderManufacturer', 'OrderCompany', 'OrderSupplier', 'OrderByCreatedBy']],
            [Category::class, ['OrderByCreatedBy']],
        ];
    }

    #[DataProvider('scopeProvider')]
    public function test_order_scopes_return_collection(string $modelClass, array $scopes): void
    {
        foreach ($scopes as $scope) {
            $result = $modelClass::{$scope}('asc')->get();
            $this->assertInstanceOf(Collection::class, $result, "{$modelClass}::{$scope} no devolvio Collection");
        }
    }

    public function test_category_requires_acceptance_scope(): void
    {
        Category::factory()->forAssets()->create(['require_acceptance' => 1]);

        $this->assertInstanceOf(Collection::class, Category::requiresAcceptance()->get());
    }
}
