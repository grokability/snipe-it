<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Labels\Label;
use App\Models\Labels\Sheet;
use App\Models\Labels\RectangleSheet;
use Illuminate\Support\Collection;

class LabelsTransformer
{
    public function transformLabels(Collection $labels, $total)
    {
        $array = [];
        foreach ($labels as $label) {
            $array[] = self::transformLabel($label);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformLabel(Label $label)
    {
        $array = [
            'name' => $label->getName(),
            'unit' => $label->getUnit(),

            'width'  => number_format($label->getWidth(), 2),
            'height' => number_format($label->getHeight(), 2),

            'margin_top'    => $label->getMarginTop(),
            'margin_bottom' => $label->getMarginBottom(),
            'margin_left'   => $label->getMarginLeft(),
            'margin_right'  => $label->getMarginRight(),

            'support_asset_tag'  => $label->getSupportAssetTag(),
            'support_1d_barcode' => $label->getSupport1DBarcode(),
            'support_2d_barcode' => $label->getSupport2DBarcode(),
            'support_fields'     => $label->getSupportFields(),
            'support_logo'       => $label->getSupportLogo(),
            'support_title'      => $label->getSupportTitle(),
        ];

        if ($label instanceof Sheet) {
            $array['sheet_info'] = [
                'label_width'  => $label->getLabelWidth(),
                'label_height' => $label->getLabelHeight(),

                'label_margin_top'    => $label->getLabelMarginTop(),
                'label_margin_bottom' => $label->getLabelMarginBottom(),
                'label_margin_left'   => $label->getLabelMarginLeft(),
                'label_margin_right'  => $label->getLabelMarginRight(),

                'labels_per_page' => $label->getLabelsPerPage(),
                'label_border' => $label->getLabelBorder(),
            ];
        }

        if ($label instanceof RectangleSheet) {
            $array['rectanglesheet_info'] = [
                'columns' => $label->getColumns(),
                'rows'    => $label->getRows(),
                'column_spacing' => $label->getLabelColumnSpacing(),
                'row_spacing'    => $label->getLabelRowSpacing(),
            ];
        }

        return $array;
    }
}
