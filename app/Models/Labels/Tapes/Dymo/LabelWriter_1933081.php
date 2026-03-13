<?php

namespace App\Models\Labels\Tapes\Dymo;


use App\Helpers\Helper;

class LabelWriter_1933081 extends LabelWriter
{
    private const BARCODE_MARGIN =   1.80;
    private const TAG_SIZE       =   2.80;
    private const TITLE_SIZE     =   2.80;
    private const TITLE_MARGIN   =   0.50;
    private const LABEL_SIZE     =   2.80;
    private const LABEL_MARGIN   = - 0.35;
    private const FIELD_SIZE     =   2.80;
    private const FIELD_MARGIN   =   0.15;

    public function getUnit()
    {
        return 'mm'; 
    }
    public function getWidth()
    {
        return 89; 
    }
    public function getHeight()
    {
        return 25; 
    }
    public function getSupportAssetTag()
    {
        return true; 
    }
    public function getSupport1DBarcode()
    {
        return true; 
    }
    public function getSupport2DBarcode()
    {
        return true; 
    }
    public function getSupportFields()
    {
        return 5; 
    }
    public function getSupportLogo()
    {
        return false; 
    }
    public function getSupportTitle()
    {
        return true; 
    }

    public function preparePDF($pdf)
    {
    }

    public function write($pdf, $record)
    {
        $pa = $this->getPrintableArea();

        $currentX = $pa->x1;
        $currentY = $pa->y1;
        $usableWidth = $pa->w;

        $barcodeSize = $pa->h - self::TAG_SIZE;

        if ($record->has('barcode2d')) {
            static::writeText(
                $pdf, $record->get('tag'),
                $pa->x1, $pa->y2 - self::TAG_SIZE,
                'freesans', 'b', self::TAG_SIZE, 'C',
                $barcodeSize, self::TAG_SIZE, true, 0
            );
            static::write2DBarcode(
                $pdf, $record->get('barcode2d')->content, $record->get('barcode2d')->type,
                $currentX, $currentY,
                $barcodeSize, $barcodeSize
            );
            $currentX += $barcodeSize + self::BARCODE_MARGIN;
            $usableWidth -= $barcodeSize + self::BARCODE_MARGIN;
        }

        $title = $record->has('title') ? $record->get('title') : null;
        $fields = $record->get('fields');
        $maxFields = $this->getSupportFields();
        $fields = collect($fields);
        if ($title) {
            $maxFields = max(0, $maxFields - 1); // title consumes one row’s worth of space
        }

        $fields = $fields->take($maxFields)->values();

        $usableHeight = $pa->h
            - self::TAG_SIZE           // bottom tag text
            - self::BARCODE_MARGIN;    // gap between fields and 1D

        $field_layout = Helper::labelFieldLayoutScaling(
            pdf: $pdf,
            fields: $fields,
            currentX: $currentX,
            usableWidth: $usableWidth,
            usableHeight: $usableHeight,
            baseLabelSize: self::LABEL_SIZE,
            baseFieldSize: self::FIELD_SIZE,
            baseFieldMargin: self::FIELD_MARGIN,
            title: $title,
            baseTitleSize: self::TITLE_SIZE,
            baseTitleMargin: self::TITLE_MARGIN,
            baseLabelPadding: 1.5,
            baseGap: 1.5,
            maxScale: 1.8,
            labelFont: 'freesans',
        );

        if ($field_layout['hasTitle']) {
            static::writeText(
                $pdf, $title,
                $currentX, $currentY,
                'freesans', 'b', $field_layout['titleSize'], 'L',
                $usableWidth, $field_layout['titleSize'], true, 0
            );
            $currentY += $field_layout['titleAdvance'];
        }
        foreach ($fields as $field) {
            $rawLabel = $field['label'] ?? null;
            $value    = (string)($field['value'] ?? '');

            // No label: value takes the whole row
            if (!is_string($rawLabel) || trim($rawLabel) === '') {
                static::writeText(
                    $pdf, $value,
                    $currentX, $currentY,
                    'freemono', 'B', $field_layout['fieldSize'], 'L',
                    $usableWidth, $field_layout['rowAdvance'], true, 0, 0.01
                );

                $currentY += $field_layout['rowAdvance'];
                continue;
            }

            $labelText = rtrim($field['label'], ':') . ':';

            static::writeText(
                $pdf, $labelText,
                $currentX, $currentY,
                'freesans', '', $field_layout['labelSize'], 'L',
                $field_layout['labelWidth'], $field_layout['rowAdvance'], true,
            );

            static::writeText(
                $pdf, $field['value'],
                $field_layout['valueX'], $currentY,
                'freemono', 'B', $field_layout['fieldSize'], 'L',
                $field_layout['valueWidth'], $field_layout['rowAdvance'], true, 0, 0.01
            );
            $currentY += $field_layout['rowAdvance'];;
        }

        if ($record->has('barcode1d')) {
            static::write1DBarcode(
                $pdf, $record->get('barcode1d')->content, $record->get('barcode1d')->type,
                $currentX, $barcodeSize + self::BARCODE_MARGIN, $usableWidth - self::TAG_SIZE, self::TAG_SIZE
            );
        }
    }

}
