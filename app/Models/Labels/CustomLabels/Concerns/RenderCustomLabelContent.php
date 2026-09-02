<?php

namespace App\Models\Labels\CustomLabels\Concerns;

trait RenderCustomLabelContent
{
    protected function boxesOverlap(?array $a, ?array $b): bool
    {
        if (!$a || !$b) {
            return false;
        }

        return !(
            $b['x'] >= $a['x'] + $a['w'] ||
            $a['x'] >= $b['x'] + $b['w'] ||
            $b['y'] >= $a['y'] + $a['h'] ||
            $a['y'] >= $b['y'] + $b['h']
        );
    }

    protected function clampBox(array $box, array $container): array
    {
        $box['w'] = min(max(0, $box['w']), $container['w']);
        $box['h'] = min(max(0, $box['h']), $container['h']);

        $box['x'] = max($container['x1'], min($box['x'], $container['x2'] - $box['w']));
        $box['y'] = max($container['y1'], min($box['y'], $container['y2'] - $box['h']));

        return $box;
    }

    protected function anchorBox(array $container, float $w, float $h, string $hAlign, string $vAlign): array
    {
        $w = min($w, $container['w']);
        $h = min($h, $container['h']);

        $x = match (strtoupper($hAlign)) {
            'R' => $container['x2'] - $w,
            'C' => $container['x1'] + (($container['w'] - $w) / 2),
            default => $container['x1'],
        };

        $y = match (strtoupper($vAlign)) {
            'B' => $container['y2'] - $h,
            'C' => $container['y1'] + (($container['h'] - $h) / 2),
            default => $container['y1'],
        };

        return $this->clampBox([
            'x' => $x,
            'y' => $y,
            'w' => $w,
            'h' => $h,
        ], $container);
    }

    protected function render1DBarcode($pdf, $record, array $layout): void
    {
        if (
            empty($layout['barcode1d']) ||
            !$record->has('barcode1d') ||
            !$this->getSupport1DBarcode()
        ) {
            return;
        }

        static::write1DBarcode(
            $pdf,
            $record->get('barcode1d')->content,
            $record->get('barcode1d')->type,
            $layout['barcode1d']['x'],
            $layout['barcode1d']['y'],
            $layout['barcode1d']['w'],
            $layout['barcode1d']['h']
        );
    }

    protected function renderLogo($pdf, $record, array $layout): void
    {
        if (
            empty($layout['logo']) ||
            !$record->has('logo') ||
            !$this->getSupportLogo()
        ) {
            return;
        }

        static::writeImage(
            $pdf,
            $record->get('logo'),
            $layout['logo']['x'],
            $layout['logo']['y'],
            $layout['logo']['w'],
            $layout['logo']['h'],
            $this->getLogoHAlign(),
            $this->getLogoVAlign(),
            300,
            true,
            false,
            0
        );
    }

    protected function render2DBarcode($pdf, $record, array $layout): void
    {
        if (
            empty($layout['barcode2d']) ||
            !$record->has('barcode2d') ||
            !$this->getSupport2DBarcode()
        ) {
            return;
        }

        static::write2DBarcode(
            $pdf,
            $record->get('barcode2d')->content,
            $record->get('barcode2d')->type,
            $layout['barcode2d']['x'],
            $layout['barcode2d']['y'],
            $layout['barcode2d']['w'],
            $layout['barcode2d']['h']
        );
    }

    protected function renderBlockTag($pdf, $record, array $layout): void
    {
        if (
            empty($layout['tag']) ||
            !$record->has('tag') ||
            !$this->getSupportAssetTag()
        ) {
            return;
        }

        static::writeText(
            $pdf,
            $record->get('tag'),
            $layout['tag']['x'],
            $layout['tag']['y'],
            $this->getTagFont(),
            'B',
            $layout['tag']['font_size'] ?? $this->getTagSize(),
            $this->getTagAlignment(),
            $layout['tag']['w'],
            $layout['tag']['h'],
            true,
            0,
            $layout['tag']['spacing'] ?? 0.3
        );
    }

    protected function renderStackedTextBlock($pdf, $record, array $layout): void
    {
        if (!empty($layout['title'])) {
            static::writeText(
                $pdf,
                $record->get('title'),
                $layout['title']['x'],
                $layout['title']['y'],
                $this->getTitleFont(),
                '',
                $layout['title']['font_size'],
                'L',
                $layout['title']['w'],
                $layout['title']['h'],
                true,
                0
            );
        }

        if (empty($layout['fields'])) {
            return;
        }

        $y = $layout['fields']['start_y'];

        foreach ($layout['fields']['fields'] as $field) {
            $rowHeight = max(
                $layout['fields']['label_size'],
                $layout['fields']['field_size']
            );

            if ($y + $rowHeight > $layout['fields']['bottom_limit']) {
                break;
            }

            $label = $field['label'] ?? '';
            $value = $field['value'] ?? '';

            if (is_string($label) && trim($label) !== '') {
                $label = rtrim($label, ':') . ':';
            }

            if ($label !== '') {
                static::writeText(
                    $pdf,
                    $label,
                    $layout['fields']['start_x'],
                    $y,
                    $this->getFieldLabelFont(),
                    '',
                    $layout['fields']['label_size'],
                    'L',
                    $layout['fields']['label_width'],
                    $layout['fields']['label_size'],
                    true,
                    0
                );
            }

            if ($layout['fields']['value_width'] > 0) {
                static::writeText(
                    $pdf,
                    $value,
                    $layout['fields']['value_x'],
                    $y,
                    $this->getFieldValueFont(),
                    'B',
                    $layout['fields']['field_size'],
                    'L',
                    $layout['fields']['value_width'],
                    $layout['fields']['field_size'],
                    true,
                    0
                );
            }

            $y += $layout['fields']['row_advance'];
        }
    }

    protected function renderVerticalStackedTextBlock($pdf, $record, array $layout): void
    {
        if (!empty($layout['title'])) {
            static::writeText(
                $pdf,
                $record->get('title'),
                $layout['title']['x'],
                $layout['title']['y'],
                $this->getTitleFont(),
                'B',
                $layout['title']['font_size'],
                'L',
                $layout['title']['w'],
                $layout['title']['h'],
                true,
                0
            );
        }
        
        if (empty($layout['fields'])) {
            return;
        }

        $y = $layout['fields']['start_y'];

        foreach ($layout['fields']['fields'] as $field) {
            $label = $field['label'] ?? '';
            $value = $field['value'] ?? '';

            if ($label !== '') {
                static::writeText(
                    $pdf,
                    $label,
                    $layout['text']['x1'],
                    $y,
                    $this->getFieldLabelFont(),
                    '',
                    $layout['fields']['label_size'],
                    'L',
                    $layout['text']['w'],
                    $layout['fields']['label_size'],
                    true,
                    0
                );

                $y += $layout['fields']['label_size'] + $this->getLabelMargin();
            }

            static::writeText(
                $pdf,
                $value,
                $layout['text']['x1'],
                $y,
                $this->getFieldValueFont(),
                'B',
                $layout['fields']['field_size'],
                'L',
                $layout['text']['w'],
                $layout['fields']['field_size'],
                true,
                0,
                0.01
            );

            $y += $layout['fields']['field_size'] + $this->getFieldMargin();
        }
    }
    protected function resolveTextBox(array $body, array $boxes): array
    {
        $leftEdge = $body['x1'];
        $rightEdge = $body['x2'];
        $topEdge = $body['y1'];
        $bottomEdge = $body['y2'];
        $barcode2DMargin = method_exists($this, 'getBarcode2DMargin')
            ? $this->getBarcode2DMargin()
            : 0;
        foreach ($boxes as $box) {
            if (!$box) {
                continue;
            }

            $isLeftAnchored = abs($box['x'] - $body['x1']) < 0.01;
            $isRightAnchored = abs(($box['x'] + $box['w']) - $body['x2']) < 0.01;
            $isTopAnchored = abs($box['y'] - $body['y1']) < 0.01;
            $isBottomAnchored = abs(($box['y'] + $box['h']) - $body['y2']) < 0.01;

            // Reserve enough spacing for either a logo or a QR code.
            $sideMargin = max(
                $this->getLogoMargin(),
                $barcode2DMargin
            );


            if ($isLeftAnchored) {
                $leftEdge = max($leftEdge, $box['x'] + $box['w'] + $sideMargin);
            }

            if ($isRightAnchored) {
                $rightEdge = min($rightEdge, $box['x'] - $sideMargin);
            }

            if ($isTopAnchored && $box['w'] > ($body['w'] * 0.6)) {
                $topEdge = max($topEdge, $box['y'] + $box['h'] + $this->getTitleMargin());
            }

            if ($isBottomAnchored && $box['w'] > ($body['w'] * 0.6)) {
                $bottomEdge = min($bottomEdge, $box['y'] - $this->getFieldMargin());
            }
        }

        $rightEdge = max($rightEdge, $leftEdge);
        $bottomEdge = max($bottomEdge, $topEdge);

        return [
            'x1' => $leftEdge,
            'y1' => $topEdge,
            'x2' => $rightEdge,
            'y2' => $bottomEdge,
            'w' => max(0, $rightEdge - $leftEdge),
            'h' => max(0, $bottomEdge - $topEdge),
        ];
    }

    protected function resolveLogoBox($record, array $body): ?array
    {
        if (!$record->has('logo') || !$this->getSupportLogo()) {
            return null;
        }

        $width = min($this->getLogoMaxWidth(), $body['w']);

        return $this->anchorBox(
            $body,
            $width,
            $body['h'],
            $this->getLogoHAlign(),
            $this->getLogoVAlign()
        );
    }

    protected function resolve2DBarcodeBox($record, array $body, ?array $logoBox = null): ?array
    {
        if (!$record->has('barcode2d') || !$this->getSupport2DBarcode()) {
            return null;
        }

        $size = $this->calculate2DBarcodeSize($record, $body);
        $size = min($size, $body['w'], $body['h']);

        $box = $this->anchorBox(
            $body,
            $size,
            $size,
            $this->getBarcode2DHAlign(),
            $this->getBarcode2DVAlign()
        );

        if ($this->boxesOverlap($box, $logoBox)) {
            $altHAlign = strtoupper($this->getBarcode2DHAlign()) === 'R' ? 'L' : 'R';

            $altBox = $this->anchorBox(
                $body,
                $size,
                $size,
                $altHAlign,
                $this->getBarcode2DVAlign()
            );

            if (!$this->boxesOverlap($altBox, $logoBox)) {
                $box = $altBox;
            }
        }

        return $box;
    }

    protected function resolveTagBox($record, array $body, ?array $barcode2dBox = null, ?array $logoBox = null): ?array
    {
        if (!$record->has('tag') || !$this->getSupportAssetTag()) {
            return null;
        }

        $tagHeight = max(0, $this->getTagSize());

        if ($barcode2dBox && ((method_exists($this, 'getTagPositionMode') && $this->getTagPositionMode() === 'under_barcode')
                || strtoupper($this->getTagAlignment()) === strtoupper($this->getBarcode2DHAlign()))) {
            $box = [
                'x' => $barcode2dBox['x'],
                'y' => $barcode2dBox['y'] + $barcode2dBox['h'],
                'w' => $barcode2dBox['w'],
                'h' => $tagHeight,
            ];

            $box['x'] += $this->getTagOffsetX();
            $box['y'] += $this->getTagOffsetY();

            return $this->clampBox($box, $body);
        }

        $tagWidth = $this->calculateTagWidth($record, $body, $barcode2dBox);
        $tagAlign = strtoupper($this->getTagAlignment());

        $x = $tagAlign === 'R'
            ? $body['x2'] - $tagWidth
            : $body['x1'];

        $box = [
            'x' => $x,
            'y' => $body['y2'] - $tagHeight,
            'w' => $tagWidth,
            'h' => $tagHeight,
        ];

        $box['x'] += $this->getTagOffsetX();
        $box['y'] += $this->getTagOffsetY();

        return $this->clampBox($box, $body);
    }

    protected function calculate2DBarcodeSize($record, array $container): float
    {
        $maxHeight = $container['h'];

        if (method_exists($this, 'getTagPositionMode') && $this->getTagPositionMode() === 'under_barcode') {
            $maxHeight = max(0, ($container['h'] - $this->getTagSize() * .35) - .9);
        }
        return min(
            $this->get2DBarcodeSize(),
            $container['w'],
            $maxHeight,
        );
    }

    protected function calculateTagWidth($record, array $body, ?array $barcode2dBox = null): float
    {
        if (method_exists($this, 'getTagPositionMode') && $barcode2dBox && $this->getTagPositionMode() === 'under_barcode') {
            return $barcode2dBox['w'];
        }

        return min($body['w'] * 0.35, $body['w']);
    }

    protected function oppositeHAlign(string $align): string
    {
        return strtoupper($align) === 'L' ? 'R' : 'L';
    }

    protected function syncLogoAnd2DBarcodeHAlign(?string $changedKey = null): void
    {
        if (method_exists($this, 'getLogoPlacement') && $this->getLogoPlacement() === 'text_column') {
            return;
        }

        if (!$this->getSupportLogo() || !$this->getSupport2DBarcode()) {
            return;
        }

        if ($changedKey === 'barcode2D_h_align') {
            $this->logoHAlign = $this->oppositeHAlign($this->barcode2DHAlign);
            return;
        }

        if ($changedKey === 'logo_h_align') {
            $this->barcode2DHAlign = $this->oppositeHAlign($this->logoHAlign);
        }
    }
}