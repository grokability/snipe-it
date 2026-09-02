<?php

namespace App\Http\Transformers;

use App\Models\Labels\CustomUserLabel;
use App\Models\Labels\Label;
use App\Models\Labels\RectangleSheet;
use App\Models\Labels\Sheet;
use Illuminate\Support\Collection;

class LabelsTransformer
{
    public function transformLabels(Collection $labels, $total)
    {
        $array = [];
        foreach ($labels as $row) {
            $array[] = self::transformLabelRow($row);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    protected function transformLabelRow(array $row): array
    {
        return match ($row['source']) {
            'custom' => $this->transformCustomLabel($row['label']),
            default => $this->transformBaseLabel($row['label']),
        };
    }

    public function transformBaseLabel(Label $label): array
    {
        $array = [
            'name' => $label->getName(),
            'unit' => $label->getUnit(),

            'width' => number_format($label->getWidth(), 2),
            'height' => number_format($label->getHeight(), 2),

            'margin_top' => $label->getMarginTop(),
            'margin_bottom' => $label->getMarginBottom(),
            'margin_left' => $label->getMarginLeft(),
            'margin_right' => $label->getMarginRight(),

            'support_asset_tag' => $label->getSupportAssetTag(),
            'support_1d_barcode' => $label->getSupport1DBarcode(),
            'support_2d_barcode' => $label->getSupport2DBarcode(),
            'support_fields' => $label->getSupportFields(),
            'support_logo' => $label->getSupportLogo(),
            'support_title' => $label->getSupportTitle(),
        ];

        if ($label instanceof Sheet) {
            $array['sheet_info'] = [
                'label_width' => $label->getLabelWidth(),
                'label_height' => $label->getLabelHeight(),

                'label_margin_top' => $label->getLabelMarginTop(),
                'label_margin_bottom' => $label->getLabelMarginBottom(),
                'label_margin_left' => $label->getLabelMarginLeft(),
                'label_margin_right' => $label->getLabelMarginRight(),

                'labels_per_page' => $label->getLabelsPerPage(),
                'label_border' => $label->getLabelBorder(),
            ];
        }

        if ($label instanceof RectangleSheet) {
            $array['rectanglesheet_info'] = [
                'columns' => $label->getColumns(),
                'rows' => $label->getRows(),
                'column_spacing' => $label->getLabelColumnSpacing(),
                'row_spacing' => $label->getLabelRowSpacing(),
            ];
        }

        return $array;
    }

    protected function transformCustomLabel(CustomUserLabel $label): array
    {
        $snapshot = $label->config_snapshot ?? [];
        $dimensionPath = $label->type === 'tape'
            ? 'dimensions'
            : 'label';

        $width = (float)data_get($snapshot, "{$dimensionPath}.width", 0);
        $height = (float)data_get($snapshot, "{$dimensionPath}.height", 0);

        return [
            'custom_label_id' => $label->id,
            'name' => $label->name,
            'source' => 'custom',
            'source_label' => 'Custom',
            'base_label' => $label->base_label,
            'type' => $label->type,
            'config_snapshot' => $label->config_snapshot,
            'is_default' => $label->is_default,

            'unit' => 'mm',

            'width' => number_format($width, 2),
            'height' => number_format($height, 2),

            'margin_top' => data_get($snapshot, 'label.margin_top'),
            'margin_bottom' => data_get($snapshot, 'label.margin_bottom'),
            'margin_left' => data_get($snapshot, 'label.margin_left'),
            'margin_right' => data_get($snapshot, 'label.margin_right'),

            'support_asset_tag' => data_get($snapshot, 'supports.asset_tag'),
            'support_1d_barcode' => data_get($snapshot, 'supports.barcode_1d'),
            'support_2d_barcode' => data_get($snapshot, 'supports.barcode_2d'),
            'support_fields' => data_get($snapshot, 'supports.fields'),
            'support_logo' => data_get($snapshot, 'supports.logo'),
            'support_title' => data_get($snapshot, 'supports.title'),

            'sheet_info' => [
                'label_width' => $width,
                'label_height' => $height,
                'label_margin_top' => data_get($snapshot, 'label.padding_top'),
                'label_margin_bottom' => data_get($snapshot, 'label.padding_bottom'),
                'label_margin_left' => data_get($snapshot, 'label.padding_left'),
                'label_margin_right' => data_get($snapshot, 'label.padding_right'),
                'labels_per_page' => data_get($snapshot, 'grid.rows', 1) * data_get($snapshot, 'grid.columns', 1),
                'label_border' => data_get($snapshot, 'label.border'),
            ],

            'rectanglesheet_info' => [
                'columns' => data_get($snapshot, 'grid.columns'),
                'rows' => data_get($snapshot, 'grid.rows'),
                'column_spacing' => data_get($snapshot, 'grid.column_spacing'),
                'row_spacing' => data_get($snapshot, 'grid.row_spacing'),
            ],
        ];
    }
}
