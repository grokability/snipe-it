<?php

namespace App\Models\Labels\CustomLabels\Concerns;

trait BuildsCustomLabelLayout
{
    protected function baseLayout($pa): array
    {
        return [
            'printable' => $this->boxFromPrintableArea($pa),
            'body' => $this->boxFromPrintableArea($pa),
            'barcode1d' => null,
            'barcode2d' => null,
            'logo' => null,
            'tag' => null,
            'text' => null,
            'title' => null,
            'fields' => null,
        ];
    }

    protected function boxFromPrintableArea($pa): array
    {
        return [
            'x1' => $pa->x1,
            'y1' => $pa->y1,
            'x2' => $pa->x2,
            'y2' => $pa->y2,
            'w' => $pa->w,
            'h' => $pa->h,
        ];
    }

    protected function resolveLayoutTitle($record): ?string
    {
        return $record->has('title') && $this->getSupportTitle()
            ? $record->get('title')
            : null;
    }

    protected function resolveLayoutFields($record): array
    {
        if (!$record->has('fields') || !$this->getSupportFields()) {
            return [];
        }

        return collect($record->get('fields'))
            ->take($this->getSupportFields())
            ->values()
            ->all();
    }

    protected function applyTextAreaConstraints(array $textBox): array
    {
        if ($this->getTextAreaWidth() !== null) {
            $textBox['w'] = min($this->getTextAreaWidth(), $textBox['w']);
            $textBox['x2'] = $textBox['x1'] + $textBox['w'];
        }

        if ($this->getTextAreaHeight() !== null) {
            $textBox['h'] = min($this->getTextAreaHeight(), $textBox['h']);
            $textBox['y2'] = $textBox['y1'] + $textBox['h'];
        }

        return $textBox;
    }

    protected function applySimpleTitleLayout(array $layout, ?string $title, float &$textY): array
    {
        if ($title === null || $title === '') {
            return $layout;
        }

        $x = $layout['text']['x1'] + $this->getTitleOffsetX();

        $layout['title'] = [
            'x' => $x,
            'y' => $textY,
            'w' => max(0, $layout['text']['x2'] - $x),
            'h' => $this->getTitleSize(),
            'font_size' => $this->getTitleSize(),
            'advance' => $this->getTitleSize() + $this->getTitleMargin(),
        ];

        $textY += $layout['title']['advance'];

        return $layout;
    }

    protected function makeFieldsLayout(
        array $textBox,
        array $fields,
        float $textY,
        float $bottomLimit,
        float $labelWidth,
        float $valueGap,
        float $labelSize,
        float $fieldSize,
        float $rowAdvance,
    ): array
    {
        $valueX = $textBox['x1'] + $labelWidth + $valueGap;

        return [
            'start_x' => $textBox['x1'],
            'start_y' => $textY,
            'bottom_limit' => $bottomLimit,
            'label_width' => $labelWidth,
            'value_x' => $valueX,
            'value_width' => max(0, $textBox['x2'] - $valueX),
            'label_size' => $labelSize,
            'field_size' => $fieldSize,
            'row_advance' => $rowAdvance,
            'fields' => $fields,
        ];
    }
}