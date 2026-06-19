<?php

namespace Tests\Unit\Labels;

use App\Models\Labels\Label;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use TCPDF;

/**
 * Cubre los helpers finales de Label: writeText (squash/border/align/bold) y
 * writeImage (resize/stretch/align), llamandolos directamente con un TCPDF real.
 */
class LabelWriteHelpersTest extends TestCase
{
    private function label(): Label
    {
        return new class extends Label
        {
            public function getUnit() { return 'in'; }
            public function getWidth() { return 4.0; }
            public function getHeight() { return 2.0; }
            public function getMarginTop() { return 0.0; }
            public function getMarginBottom() { return 0.0; }
            public function getMarginLeft() { return 0.0; }
            public function getMarginRight() { return 0.0; }
            public function getSupportAssetTag() { return true; }
            public function getSupport1DBarcode() { return true; }
            public function getSupport2DBarcode() { return true; }
            public function getSupportFields() { return 4; }
            public function getSupportLogo() { return true; }
            public function getSupportTitle() { return true; }
            public function preparePDF(TCPDF $pdf) {}
            public function write(TCPDF $pdf, \Illuminate\Support\Collection $record) {}
        };
    }

    private function pdf(): TCPDF
    {
        $pdf = new TCPDF('L', 'in', [0 => 4.0, 1 => 2.0]);
        $pdf->AddPage();
        $pdf->SetFont('freesans', '', 10);

        return $pdf;
    }

    public static function textCaseProvider(): array
    {
        return [
            'left'   => ['Texto normal', 'L', false, 0],
            'right'  => ['Texto derecha', 'R', false, 0],
            'center' => ['Centrado', 'C', false, 0],
            'squash' => ['Texto muy largo que se aplasta para caber', 'L', true, 0],
            'border' => ['Con borde', 'L', false, 1],
            'bold'   => ['Texto con **negrita** dentro', 'L', false, 0],
        ];
    }

    #[DataProvider('textCaseProvider')]
    public function test_write_text_variants(string $text, string $align, bool $squash, int $border): void
    {
        $pdf = $this->pdf();

        $this->label()->writeText($pdf, $text, 0.1, 0.1, 'freesans', '', 8, $align, 2.0, 0.2, $squash, $border, 0);

        $this->assertTrue(true); // ejecuta sin excepcion
    }

    public function test_write_text_empty(): void
    {
        $this->label()->writeText($this->pdf(), '', 0, 0);
        $this->assertTrue(true);
    }

    public function test_write_image_empty_returns_zero(): void
    {
        $this->assertSame([0, 0], $this->label()->writeImage($this->pdf(), '', 0, 0));
    }

    public static function imageCaseProvider(): array
    {
        return [
            'scale'       => ['L', 'T', false, false],
            'resize'      => ['C', 'C', true, false],
            'stretch'     => ['R', 'B', true, true],
        ];
    }

    #[DataProvider('imageCaseProvider')]
    public function test_write_image_variants(string $halign, string $valign, bool $resize, bool $stretch): void
    {
        $image = public_path('img/demo/logo.png');
        $pdf = $this->pdf();

        $result = $this->label()->writeImage($pdf, $image, 0.0, 0.0, 1.0, 0.5, $halign, $valign, 300, $resize, $stretch, 0);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function test_write_image_with_border(): void
    {
        $image = public_path('img/demo/logo.png');

        $result = $this->label()->writeImage($this->pdf(), $image, 0.0, 0.0, 1.0, 0.5, 'L', 'T', 300, false, false, 1);

        $this->assertIsArray($result);
    }
}
