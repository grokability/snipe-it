<?php

namespace Tests\Unit\Transformers;

use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Ejercita los transformers de modelo llamando su metodo transform{Plural}(Collection),
 * que internamente recorre el transform{Singular}() — construye el array de salida
 * completo (el grueso de cada archivo), igual que dataTableLayout en los Presenters.
 */
class ModelTransformersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public static function transformerProvider(): array
    {
        return [
            ['App\Http\Transformers\AccessoriesTransformer', 'transformAccessories', 'App\Models\Accessory'],
            ['App\Http\Transformers\ActionlogsTransformer', 'transformActionlogs', 'App\Models\Actionlog'],
            ['App\Http\Transformers\AssetModelsTransformer', 'transformAssetModels', 'App\Models\AssetModel'],
            ['App\Http\Transformers\AssetsTransformer', 'transformAssets', 'App\Models\Asset'],
            ['App\Http\Transformers\CategoriesTransformer', 'transformCategories', 'App\Models\Category'],
            ['App\Http\Transformers\CompaniesTransformer', 'transformCompanies', 'App\Models\Company'],
            ['App\Http\Transformers\ComponentsTransformer', 'transformComponents', 'App\Models\Component'],
            ['App\Http\Transformers\ConsumablesTransformer', 'transformConsumables', 'App\Models\Consumable'],
            ['App\Http\Transformers\CustomFieldsTransformer', 'transformCustomFields', 'App\Models\CustomField'],
            ['App\Http\Transformers\CustomFieldsetsTransformer', 'transformCustomFieldsets', 'App\Models\CustomFieldset'],
            ['App\Http\Transformers\DepartmentsTransformer', 'transformDepartments', 'App\Models\Department'],
            ['App\Http\Transformers\DepreciationsTransformer', 'transformDepreciations', 'App\Models\Depreciation'],
            ['App\Http\Transformers\GroupsTransformer', 'transformGroups', 'App\Models\Group'],
            ['App\Http\Transformers\LicenseSeatsTransformer', 'transformLicenseSeats', 'App\Models\LicenseSeat'],
            ['App\Http\Transformers\LicensesTransformer', 'transformLicenses', 'App\Models\License'],
            ['App\Http\Transformers\LocationsTransformer', 'transformLocations', 'App\Models\Location'],
            ['App\Http\Transformers\MaintenancesTransformer', 'transformMaintenances', 'App\Models\Maintenance'],
            ['App\Http\Transformers\ManufacturersTransformer', 'transformManufacturers', 'App\Models\Manufacturer'],
            ['App\Http\Transformers\PredefinedKitsTransformer', 'transformPredefinedKits', 'App\Models\PredefinedKit'],
            ['App\Http\Transformers\StatuslabelsTransformer', 'transformStatuslabels', 'App\Models\Statuslabel'],
            ['App\Http\Transformers\SuppliersTransformer', 'transformSuppliers', 'App\Models\Supplier'],
            ['App\Http\Transformers\UsersTransformer', 'transformUsers', 'App\Models\User'],
        ];
    }

    #[DataProvider('transformerProvider')]
    public function test_transformer_returns_array(string $transformerClass, string $method, string $modelClass): void
    {
        $items = $modelClass::factory()->count(2)->create();

        $result = (new $transformerClass)->{$method}($items, $items->count());

        $this->assertIsArray($result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('rows', $result);
        $this->assertCount(2, $result['rows']);
    }
}
