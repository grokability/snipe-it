<?php

namespace App\Models\Labels\CustomLabels;

use App\Helpers\Helper;
use App\Models\Labels\CustomLabels\Concerns\BuildsCustomLabelLayout;
use App\Models\Labels\CustomLabels\Concerns\HasCustomLabelContentProperties;
use App\Models\Labels\CustomLabels\Concerns\HasCustomLabelEditorConfig;
use App\Models\Labels\CustomLabels\Concerns\HasCustomLabelEditorSections;
use App\Models\Labels\CustomLabels\Concerns\HasCustomLabelSupports;
use App\Models\Labels\CustomLabels\Concerns\RenderCustomLabelContent;
use App\Models\Labels\CustomLabels\Concerns\SeedsCustomLabelFromTemplate;
use App\Models\Labels\RectangleSheet;
use TCPDF;

abstract class CustomSheetLabel extends RectangleSheet
{
    use HasCustomLabelContentProperties;
    use RenderCustomLabelContent;
    use HasCustomLabelEditorConfig {
        getContentEditorConfig as getBaseContentEditorConfig;
    }
    use HasCustomLabelEditorSections;
    use HasCustomLabelSupports;
    use SeedsCustomLabelFromTemplate;
    use BuildsCustomLabelLayout;
    protected array $editorConfig = [];

    protected string $unit = 'mm';

    /*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/
    protected ?float $pageWidth = 210.0;
    protected ?float $pageHeight = 297.0;
    protected ?float $pageMarginTop = 0.0;
    protected ?float $pageMarginRight = 0.0;
    protected ?float $pageMarginBottom = 0.0;
    protected ?float $pageMarginLeft = 0.0;

    /*
    |--------------------------------------------------------------------------
    | Grid
    |--------------------------------------------------------------------------
    */
    protected int $rows = 9;
    protected int $columns = 3;
    protected float $labelRowSpacing = 0.0;
    protected float $labelColumnSpacing = 0.0;

    /*
    |--------------------------------------------------------------------------
    | Label
    |--------------------------------------------------------------------------
    */
    protected ?float $labelWidth = 50.0;
    protected ?float $labelHeight = 25.0;
    protected float $labelMarginTop = 0.0;
    protected float $labelMarginRight = 0.0;
    protected float $labelMarginBottom = 0.0;
    protected float $labelMarginLeft = 0.0;

    /*
    |--------------------------------------------------------------------------
    | Sheet-only tag positioning
    |--------------------------------------------------------------------------
    */
    protected string $tagHAlign = 'R';
    protected string $tagVAlign = 'B';
    
    public function getUnit()
    {
        return $this->unit;
    }

    public function getPageWidth()
    {
        return $this->pageWidth;
    }

    public function getPageHeight()
    {
        return $this->pageHeight;
    }

    public function getPageMarginTop()
    {
        return $this->pageMarginTop;
    }

    public function getPageMarginRight()
    {
        return $this->pageMarginRight;
    }

    public function getPageMarginBottom()
    {
        return $this->pageMarginBottom;
    }

    public function getPageMarginLeft()
    {
        return $this->pageMarginLeft;
    }

    public function getRows()
    {
        return $this->rows;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getLabelWidth()
    {
        return $this->labelWidth;
    }

    public function getLabelHeight()
    {
        return $this->labelHeight;
    }

    public function getLabelRowSpacing()
    {
        return $this->labelRowSpacing;
    }

    public function getLabelColumnSpacing()
    {
        return $this->labelColumnSpacing;
    }

    public function getLabelMarginTop()
    {
        return $this->labelMarginTop;
    }

    public function getLabelMarginRight()
    {
        return $this->labelMarginRight;
    }

    public function getLabelMarginBottom()
    {
        return $this->labelMarginBottom;
    }

    public function getLabelMarginLeft()
    {
        return $this->labelMarginLeft;
    }

    public function getLabelBorder()
    {
        return 0;
    }

    public function getTagHAlign(): string
    {
        return $this->tagHAlign;
    }

    public function getTagVAlign(): string
    {
        return $this->tagVAlign;
    }

    public function getTextRenderMode(): string
    {
        return $this->textRenderMode;
    }

    public function preparePDF(TCPDF $pdf): void
    {
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
    }

    // The getContenEditorConfig trait shares a lot of common variables, but if sheet specific adjustments are required in the future they can be array_merged here.
    protected function getContentEditorConfig(): array
    {
        return $this->getBaseContentEditorConfig();
    }

    public function seedFromTemplate($template): static
    {
        $convert = $this->unitConverterFor($template);

        $this->unit = 'mm';

        $this->seedSheetMeasurements($template, $convert);
        $this->seedSheetGrid($template);

        $this->seedSupportsFromTemplate($template);
        $this->seedLegacyContentFromTemplate($template, $convert);
        $this->seedEditorContentFromTemplate($template, $convert);

        return $this;
    }

    public function applyEditorConfig(array $config): static
    {
        $this->editorConfig = $config;
        $this->hydrateFromEditorConfig($config);

        return $this;
    }

    protected function hydrateFromEditorConfig(array $config): void
    {
        $page = $config['page'] ?? [];
        $grid = $config['grid'] ?? [];
        $label = $config['label'] ?? [];
        $supports = $config['supports'] ?? [];
        $content = $config['content'] ?? [];

        $this->hydrateSheetPage($page);
        $this->hydrateSheetGrid($grid);
        $this->hydrateSheetLabel($label);
        $this->hydrateSupports($supports);
        $this->hydrateContent($content);
    }

    public function write($pdf, $record)
    {
        $pa = $this->getLabelPrintableArea();
        $layout = $this->buildLayout($pdf, $record, $pa);

        $this->render1DBarcode($pdf, $record, $layout);
        $this->renderLogo($pdf, $record, $layout);
        $this->render2DBarcode($pdf, $record, $layout);
        $this->renderBlockTag($pdf, $record, $layout);
        $this->renderTextBlock($pdf, $record, $layout);
    }

    protected function buildLayout($pdf, $record, $pa): array
    {
        $layout = $this->baseLayout($pa);

        /*
        |--------------------------------------------------------------------------
        | Reserve bottom strip for 1D barcode
        |--------------------------------------------------------------------------
        */
        if ($record->has('barcode1d') && $this->getSupport1DBarcode()) {
            $barcodeHeight = max(0, $this->getBarcodeSize());
            $barcodeMargin = max(0, $this->getBarcodeMargin());

            $barcodeHeight = min($barcodeHeight, $layout['body']['h']);

            $layout['barcode1d'] = [
                'x' => $layout['body']['x1'],
                'y' => $layout['body']['y2'] - $barcodeHeight,
                'w' => $layout['body']['w'],
                'h' => $barcodeHeight,
            ];

            $layout['body']['y2'] -= ($barcodeHeight + $barcodeMargin);
            $layout['body']['y2'] = max(
                $layout['body']['y1'],
                $layout['body']['y2']
            );

            $layout['body']['h'] = max(
                0,
                $layout['body']['y2'] - $layout['body']['y1']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Resolve title + fields
        |--------------------------------------------------------------------------
        */
        $title = $this->resolveLayoutTitle($record);
        $fields = $this->resolveLayoutFields($record);

        $titlePosition = $this->getTitlePosition();

        /*
        |--------------------------------------------------------------------------
        | Working body
        |--------------------------------------------------------------------------
        */
        $contentBody = $layout['body'];

        /*
        |--------------------------------------------------------------------------
        | Reserve top strip for title
        |--------------------------------------------------------------------------
        */
        if (
            $title !== null
            && $title !== ''
            && $titlePosition === 'top'
        ) {
            $titleSize = max(0, $this->getTitleSize());
            $titleAdvance = $titleSize + max(0, $this->getTitleMargin());

            /*
             * Title itself occupies the original top of the body.
             */
            $layout['title'] = [
                'x' => $layout['body']['x1'] + $this->getTitleOffsetX(),
                'y' => $layout['body']['y1'],
                'w' => max(
                    0,
                    $layout['body']['x2']
                    - ($layout['body']['x1'] + $this->getTitleOffsetX())
                ),
                'h' => $titleSize,
                'font_size' => $titleSize,
                'advance' => $titleAdvance,
            ];

            /*
             * Everything else begins below the title.
             */
            $contentBody['y1'] += $titleAdvance;
            $contentBody['y1'] = min(
                $contentBody['y1'],
                $contentBody['y2']
            );

            $contentBody['h'] = max(
                0,
                $contentBody['y2'] - $contentBody['y1']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Logo, 2D barcode, and tag
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | Use $contentBody here, not $layout['body'].
        |--------------------------------------------------------------------------
        */
        $logoBox = $this->resolveLogoBox(
            $record,
            $contentBody
        );

        $barcode2dBox = $this->resolve2DBarcodeBox(
            $record,
            $contentBody,
            $logoBox
        );

        $tagBox = $this->resolveTagBox(
            $record,
            $contentBody,
            $barcode2dBox,
            $logoBox
        );

        $layout['logo'] = $logoBox;
        $layout['barcode2d'] = $barcode2dBox;
        $layout['tag'] = $tagBox;

        /*
        |--------------------------------------------------------------------------
        | Derive remaining text box
        |--------------------------------------------------------------------------
        */
        $layout['text'] = $this->resolveTextBox(
            $contentBody,
            array_filter([
                $layout['logo'],
                $layout['barcode2d'],
                $layout['tag'],
            ])
        );

        $layout['text'] = $this->applyTextAreaConstraints(
            $layout['text']
        );

        $textY = $layout['text']['y1'];
        $bottomLimit = $layout['text']['y2'];
        $availableHeight = max(
            0,
            $bottomLimit - $textY
        );

        /*
        |--------------------------------------------------------------------------
        | Calculate field scaling
        |--------------------------------------------------------------------------
        |
        | If the title is on top, its space has already been reserved.
        | Do not make labelFieldLayoutScaling() reserve it again.
        |--------------------------------------------------------------------------
        */
        $scalingTitle = $titlePosition === 'top'
            ? null
            : $title;

        $fieldLayout = Helper::labelFieldLayoutScaling(
            $pdf,
            $fields,
            $layout['text']['x1'],
            $layout['text']['w'],
            $availableHeight,
            $this->getLabelSize(),
            $this->getFieldSize(),
            $this->getFieldMargin(),
            $scalingTitle,
            $this->getTitleSize(),
            $this->getTitleMargin(),
            $this->getLabelMargin(),
            $this->getFieldMargin()
        );

        /*
        |--------------------------------------------------------------------------
        | Normal / inline title
        |--------------------------------------------------------------------------
        */
        if (
            $titlePosition !== 'top'
            && $fieldLayout['hasTitle']
        ) {
            $x = $layout['text']['x1'] + $this->getTitleOffsetX();

            $layout['title'] = [
                'x' => $x,
                'y' => $textY,
                'w' => max(
                    0,
                    $layout['text']['x2'] - $x
                ),
                'h' => $fieldLayout['titleSize'],
                'font_size' => $fieldLayout['titleSize'],
                'advance' => $fieldLayout['titleAdvance'],
            ];

            $textY += $fieldLayout['titleAdvance'];
        }

        /*
        |--------------------------------------------------------------------------
        | Fields
        |--------------------------------------------------------------------------
        */
        $labelWidth = min(
            $fieldLayout['labelWidth'],
            $layout['text']['w'] * 0.45
        );

        $gap = max(
            0.8,
            $this->getFieldMargin()
            * max($fieldLayout['scale'], 0.5)
        );

        $layout['fields'] = $this->makeFieldsLayout(
            $layout['text'],
            $fields,
            $textY,
            $bottomLimit,
            $labelWidth,
            $gap,
            $fieldLayout['labelSize'],
            $fieldLayout['fieldSize'],
            $fieldLayout['rowAdvance'],
        );

        return $layout;
    }

    protected function renderTextBlock($pdf, $record, array $layout): void
    {
        if ($this->getTextRenderMode() === 'vertical_stack') {
            $this->renderVerticalStackedTextBlock($pdf, $record, $layout);
            return;
        }

        $this->renderStackedTextBlock($pdf, $record, $layout);
    }
}
