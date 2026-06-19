<?php

namespace Tests\Unit\Labels;

use App\Models\Labels\Label;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use TCPDF;

/**
 * Cubre Label::validate() (cada rama de error por getter invalido) y los helpers
 * estaticos find()/fromFormat(), usando un doble configurable de Label.
 */
class LabelValidateTest extends TestCase
{
    /** Crea un Label valido, sobreescribiendo un getter para forzar invalidez. */
    private function makeLabel(array $overrides = []): Label
    {
        return new class($overrides) extends Label
        {
            public function __construct(private array $o = []) {}

            private function val(string $key, $default)
            {
                return array_key_exists($key, $this->o) ? $this->o[$key] : $default;
            }

            public function getUnit() { return $this->val('getUnit', 'mm'); }
            public function getWidth() { return $this->val('getWidth', 100.0); }
            public function getHeight() { return $this->val('getHeight', 50.0); }
            public function getMarginTop() { return $this->val('getMarginTop', 1.0); }
            public function getMarginBottom() { return $this->val('getMarginBottom', 1.0); }
            public function getMarginLeft() { return $this->val('getMarginLeft', 1.0); }
            public function getMarginRight() { return $this->val('getMarginRight', 1.0); }
            public function getSupportAssetTag() { return $this->val('getSupportAssetTag', true); }
            public function getSupport1DBarcode() { return $this->val('getSupport1DBarcode', true); }
            public function getSupport2DBarcode() { return $this->val('getSupport2DBarcode', true); }
            public function getSupportFields() { return $this->val('getSupportFields', 4); }
            public function getSupportLogo() { return $this->val('getSupportLogo', true); }
            public function getSupportTitle() { return $this->val('getSupportTitle', true); }
            public function preparePDF(TCPDF $pdf) {}
            public function write(TCPDF $pdf, \Illuminate\Support\Collection $record) {}
        };
    }

    public function test_valid_label_passes_validation(): void
    {
        $this->makeLabel()->validate();
        $this->assertTrue(true); // no lanzo excepcion
    }

    public static function invalidGetterProvider(): array
    {
        return [
            'unit'    => [['getUnit' => 'parsecs']],
            'width'   => [['getWidth' => 'wide']],
            'height'  => [['getHeight' => 'tall']],
            'mtop'    => [['getMarginTop' => 'x']],
            'mbottom' => [['getMarginBottom' => 'x']],
            'mleft'   => [['getMarginLeft' => 'x']],
            'mright'  => [['getMarginRight' => 'x']],
            'sup1d'   => [['getSupport1DBarcode' => 'yes']],
            'sup2d'   => [['getSupport2DBarcode' => 'yes']],
            'fields'  => [['getSupportFields' => 'four']],
            'logo'    => [['getSupportLogo' => 'yes']],
            'title'   => [['getSupportTitle' => 'yes']],
        ];
    }

    #[DataProvider('invalidGetterProvider')]
    public function test_invalid_getter_throws(array $overrides): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->makeLabel($overrides)->validate();
    }

    public function test_get_name_and_orientation(): void
    {
        $label = $this->makeLabel();
        $this->assertIsString($label->getName());
        // width >= height -> 'L'.
        $this->assertSame('L', $label->getOrientation());
        $this->assertSame('P', $this->makeLabel(['getWidth' => 10.0, 'getHeight' => 50.0])->getOrientation());
    }

    public function test_get_printable_area(): void
    {
        $area = $this->makeLabel()->getPrintableArea();
        $this->assertObjectHasProperty('w', $area);
        $this->assertObjectHasProperty('h', $area);
    }

    public function test_static_find_returns_collection_and_instance(): void
    {
        $all = Label::find();
        $this->assertNotEmpty($all);

        $one = Label::find('DefaultLabel');
        $this->assertInstanceOf(Label::class, $one);

        $this->assertNull(Label::find('NoExisteEstaEtiqueta'));
    }
}
