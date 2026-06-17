<?php

namespace Tests\Unit\Presenters;

use App\Models\CustomField;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Cubre los metodos estaticos de layout adicionales (no dataTableLayout) que
 * varios presenters exponen para tablas secundarias (seats, accesorios, etc.).
 */
class ExtraLayoutsTest extends TestCase
{
    public static function staticLayoutProvider(): array
    {
        return [
            ['App\Presenters\AccessoryPresenter', 'assignedDataTableLayout'],
            ['App\Presenters\AccessoryPresenter', 'assignedDataTableLayoutForObject'],
            ['App\Presenters\AssetPresenter', 'assignedAccessoriesDataTableLayout'],
            ['App\Presenters\ComponentPresenter', 'checkedOut'],
            ['App\Presenters\ConsumablePresenter', 'checkedOut'],
            ['App\Presenters\LicensePresenter', 'dataTableLayoutSeats'],
            ['App\Presenters\LicensePresenter', 'dataTableLayoutSeatsCheckedOutToAssets'],
            ['App\Presenters\LocationPresenter', 'assignedAccessoriesDataTableLayout'],
            ['App\Presenters\MaintenancesPresenter', 'reportLayout'],
        ];
    }

    #[DataProvider('staticLayoutProvider')]
    public function test_static_layout_returns_valid_json(string $class, string $method): void
    {
        $json = $class::$method();

        $decoded = json_decode($json, true);
        $this->assertSame(JSON_ERROR_NONE, json_last_error(), "{$class}::{$method} no es JSON valido");
        $this->assertIsArray($decoded);
        $this->assertNotEmpty($decoded);
    }

    public function test_license_seats_layout_without_checkbox(): void
    {
        $decoded = json_decode(\App\Presenters\LicensePresenter::dataTableLayoutSeats(false), true);
        $this->assertIsArray($decoded);
    }

    public function test_license_seats_checked_out_layout_with_hidden_fields(): void
    {
        $decoded = json_decode(\App\Presenters\LicensePresenter::dataTableLayoutSeatsCheckedOutToAssets(['checkbox']), true);
        $this->assertIsArray($decoded);
    }

    public function test_custom_field_visibility_icons(): void
    {
        $field = CustomField::factory()->create();

        $this->assertIsArray(\App\Presenters\CustomFieldPresenter::visibilityIconsArray($field));
        $this->assertIsString(\App\Presenters\CustomFieldPresenter::visibilityIcons($field));
    }
}
