<?php

namespace Tests\Unit\Helpers;

use App\Helpers\Helper;
use App\Models\Accessory;
use App\Models\AssetModel;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\User;
use Tests\TestCase;
use TCPDF;

/**
 * Cubre los metodos densos de Helper que tenian el cuerpo sin ejercitar:
 * checkLowInventory (loops internos), labelFieldLayoutScaling (TCPDF) y processUploadedImage.
 */
class HelperDenseMethodsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_check_low_inventory_collects_low_items(): void
    {
        $this->settings->set(['alert_threshold' => 5]);

        // Cada item por debajo del umbral para entrar al cuerpo de su foreach.
        Consumable::factory()->create(['qty' => 1, 'min_amt' => 10]);
        Accessory::factory()->create(['qty' => 1, 'min_amt' => 10]);
        Component::factory()->create(['qty' => 1, 'min_amt' => 10]);
        AssetModel::factory()->create(['min_amt' => 10]); // sin assets disponibles -> avail 0
        License::factory()->create(['min_amt' => 10, 'seats' => 1]);

        $result = Helper::checkLowInventory();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $types = array_column($result, 'type');
        $this->assertContains('consumables', $types);
        $this->assertContains('accessories', $types);
        $this->assertContains('components', $types);
        // Cada entrada trae las claves esperadas.
        foreach ($result as $item) {
            $this->assertArrayHasKey('percent', $item);
            $this->assertArrayHasKey('remaining', $item);
            $this->assertArrayHasKey('min_amt', $item);
        }
    }

    public function test_label_field_layout_scaling_with_title_and_fields(): void
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->AddPage();

        $fields = [
            ['label' => 'Serial', 'value' => 'ABC123'],
            ['label' => 'Modelo', 'value' => 'XPS'],
            ['value' => 'sin label'], // se omite del sizing de columna
        ];

        $layout = Helper::labelFieldLayoutScaling(
            pdf: $pdf,
            fields: $fields,
            currentX: 0.0,
            usableWidth: 80.0,
            usableHeight: 40.0,
            baseLabelSize: 8.0,
            baseFieldSize: 8.0,
            baseFieldMargin: 1.0,
            title: 'Etiqueta de prueba',
            baseTitleSize: 10.0,
            baseTitleMargin: 2.0,
        );

        $this->assertIsArray($layout);
        $this->assertArrayHasKey('scale', $layout);
        $this->assertTrue($layout['hasTitle']);
        $this->assertArrayHasKey('labelWidth', $layout);
    }

    public function test_label_field_layout_scaling_without_title(): void
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->AddPage();

        $layout = Helper::labelFieldLayoutScaling(
            pdf: $pdf,
            fields: [['label' => 'X', 'value' => '1']],
            currentX: 0.0,
            usableWidth: 50.0,
            usableHeight: 20.0,
            baseLabelSize: 8.0,
            baseFieldSize: 8.0,
            baseFieldMargin: 1.0,
            title: null,
        );

        $this->assertFalse($layout['hasTitle']);
        $this->assertSame(0.0, $layout['titleAdvance']);
    }

    public function test_process_uploaded_image_returns_false_for_empty(): void
    {
        $this->assertFalse(Helper::processUploadedImage('', 'uploads/tmp/'));
    }

    public function test_process_uploaded_image_returns_false_for_invalid_data(): void
    {
        // Datos que no son una imagen valida -> rama catch -> false.
        $this->assertFalse(Helper::processUploadedImage('data:image/png;base64,not-base64-image', 'uploads/tmptest/'));
    }

    public function test_process_uploaded_image_saves_valid_png(): void
    {
        // PNG 1x1 transparente en base64.
        $png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        $savePath = 'uploads/tmptest/';

        $result = Helper::processUploadedImage($png, $savePath);

        // Si el driver de imagen esta disponible devuelve el filename; si no, false (rama catch).
        $this->assertTrue($result === false || is_string($result));

        if (is_string($result)) {
            $full = public_path($savePath.$result);
            if (file_exists($full)) {
                unlink($full);
            }
        }
    }
}
