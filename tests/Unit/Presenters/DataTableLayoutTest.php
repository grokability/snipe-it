<?php

namespace Tests\Unit\Presenters;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Cubre el metodo estatico dataTableLayout() de todos los presenters que lo
 * implementan. Cada layout construye un array grande de columnas para
 * bootstrap-table; con una sola llamada se ejercita la mayor parte del archivo.
 */
class DataTableLayoutTest extends TestCase
{
    public static function presenterProvider(): array
    {
        return [
            ['App\Presenters\AccessoryPresenter'],
            ['App\Presenters\AssetAuditPresenter'],
            ['App\Presenters\AssetModelPresenter'],
            ['App\Presenters\AssetPresenter'],
            ['App\Presenters\CategoryPresenter'],
            ['App\Presenters\CompanyPresenter'],
            ['App\Presenters\ComponentPresenter'],
            ['App\Presenters\ConsumablePresenter'],
            ['App\Presenters\DepartmentPresenter'],
            ['App\Presenters\DepreciationPresenter'],
            ['App\Presenters\DepreciationReportPresenter'],
            ['App\Presenters\GroupPresenter'],
            ['App\Presenters\HistoryPresenter'],
            ['App\Presenters\LabelPresenter'],
            ['App\Presenters\LicensePresenter'],
            ['App\Presenters\LocationPresenter'],
            ['App\Presenters\MaintenanceTypePresenter'],
            ['App\Presenters\MaintenancesPresenter'],
            ['App\Presenters\ManufacturerPresenter'],
            ['App\Presenters\PredefinedKitPresenter'],
            ['App\Presenters\StatusLabelPresenter'],
            ['App\Presenters\SupplierPresenter'],
            ['App\Presenters\UploadedFilesPresenter'],
            ['App\Presenters\UserPresenter'],
        ];
    }

    #[DataProvider('presenterProvider')]
    public function test_data_table_layout_returns_valid_json(string $presenterClass): void
    {
        $json = $presenterClass::dataTableLayout();

        $this->assertIsString($json);

        $decoded = json_decode($json, true);

        $this->assertSame(JSON_ERROR_NONE, json_last_error(), "dataTableLayout de {$presenterClass} no es JSON valido");
        $this->assertIsArray($decoded);
        $this->assertNotEmpty($decoded, "dataTableLayout de {$presenterClass} esta vacio");

        // Cada columna del layout debe ser un array de definicion de columna.
        foreach ($decoded as $column) {
            $this->assertIsArray($column);
        }
    }
}
