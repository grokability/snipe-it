<?php

namespace Tests\Unit\Labels;

use App\Models\Asset;
use App\Models\Setting;
use App\Models\User;
use App\View\Label as LabelView;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Renderiza el PDF de etiquetas a traves del motor real App\View\Label para cada
 * plantilla. Cada render ejecuta el write() del Tape/Sheet + la base Label
 * (writeText/writeImage/write1DBarcode/write2DBarcode) + View\Label::render.
 * Es el camino de mayor ROI para las clases de Labels (TCPDF corre en tests).
 */
class LabelRenderingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public static function templateProvider(): array
    {
        return array_map(fn ($t) => [$t], [
            'DefaultLabel',
            'Tapes\\Brother\\TZe_12mm_A',
            'Tapes\\Brother\\TZe_18mm_A',
            'Tapes\\Brother\\TZe_241',
            'Tapes\\Brother\\TZe_24mm_A',
            'Tapes\\Brother\\TZe_24mm_B',
            'Tapes\\Brother\\TZe_24mm_C',
            'Tapes\\Brother\\TZe_24mm_D',
            'Tapes\\Brother\\TZe_24mm_E',
            'Tapes\\Brother\\TZe_62mm_Landscape_A',
            'Tapes\\Dymo\\LabelWriter_11354',
            'Tapes\\Dymo\\LabelWriter_1933081',
            'Tapes\\Dymo\\LabelWriter_2112283',
            'Tapes\\Dymo\\LabelWriter_30252',
            'Tapes\\Generic\\Continuous_53mm_A',
            'Tapes\\Generic\\Continuous_Landscape_0_59in_A',
            'Tapes\\Generic\\Tape_53mm_A',
            'Sheets\\Avery\\L4736_A',
            'Sheets\\Avery\\L6009_A',
            'Sheets\\Avery\\L7162_A',
            'Sheets\\Avery\\L7162_B',
            'Sheets\\Avery\\L7163_A',
            'Sheets\\Avery\\_3490_A',
            'Sheets\\Avery\\_5267_A',
            'Sheets\\Avery\\_5520_A',
            'Sheets\\Avery\\_5520_B',
        ]);
    }

    private function configureLabelSettings(string $template, int $emptyRows = 0): void
    {
        $this->settings->set([
            'label2_enable' => 1,
            'label2_template' => $template,
            'label2_title' => '{COMPANY} Inventario',
            'label2_fields' => 'name;serial;model',
            'label2_1d_type' => 'C128',
            'label2_2d_type' => 'QRCODE',
            'label2_2d_target' => 'hardware_id',
            'label2_empty_row_count' => $emptyRows,
            'label2_asset_logo' => 0,
            'label_logo' => null,
        ]);
    }

    #[DataProvider('templateProvider')]
    public function test_renders_label_pdf_for_template(string $template): void
    {
        $this->configureLabelSettings($template);
        $assets = Asset::factory()->count(2)->create();

        ob_start();
        try {
            (new LabelView)
                ->with('settings', Setting::getSettings())
                ->with('assets', $assets)
                ->with('offset', 0)
                ->render();
            $output = ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            $this->fail("Render fallo para {$template}: ".$e->getMessage());
        }

        $this->assertStringStartsWith('%PDF', $output);
    }

    public function test_renders_with_empty_rows_and_2d_targets(): void
    {
        // Cubre la rama de empty_row_count y otros targets de barcode 2D.
        foreach (['plain_asset_id', 'plain_asset_tag', 'plain_serial_number', 'location'] as $target) {
            $this->configureLabelSettings('DefaultLabel', 2);
            $this->settings->set(['label2_2d_target' => $target]);
            $asset = Asset::factory()->create();

            ob_start();
            (new LabelView)
                ->with('settings', Setting::getSettings())
                ->with('assets', collect([$asset]))
                ->with('offset', 0)
                ->render();
            $output = ob_get_clean();

            $this->assertStringStartsWith('%PDF', $output);
        }
    }

    public function test_legacy_view_when_label2_disabled(): void
    {
        $this->settings->set(['label2_enable' => 0]);
        $assets = Asset::factory()->count(1)->create();

        $result = (new LabelView)
            ->with('settings', Setting::getSettings())
            ->with('assets', $assets)
            ->with('bulkedit', false)
            ->with('count', 1)
            ->render();

        // Con label2 deshabilitado, cae al view legacy (no PDF).
        $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $result);
    }
}
