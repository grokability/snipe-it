<?php

namespace App\Models\Labels\CustomLabels;

class PreviewTapeLabel extends CustomTapeLabel
{
    protected float $width = 50.0;
    protected float $height = 24.0;
    protected float $labelGap = 0.0;
    protected bool $supportAssetTag = true;
    protected bool $support1DBarcode = true;
    protected bool $support2DBarcode = true;
    protected int $supportFields = 5;
    protected bool $supportLogo = true;
    protected bool $supportTitle = true;
    protected float $barcodeSize = 6.0;
    protected float $barcodeMargin = 1.0;

    protected float $barcode2DSize = 10.0;
    protected string $barcode2DHAlign = 'L';
    protected string $barcode2DVAlign = 'T';

    protected float $tagSize = 5.5;
    protected string $tagFont = 'freemono';
    protected string $tagAlignment = 'L';

    protected float $titleSize = 8.0;
    protected float $titleMargin = 1.0;
    protected string $titleFont = 'freesans';

    protected float $labelSize = 5.0;
    protected float $labelMargin = 1.0;
    protected string $fieldLabelFont = 'freesans';

    protected float $fieldSize = 5.0;
    protected float $fieldMargin = 1.0;
    protected string $fieldValueFont = 'freemono';

    protected float $logoMaxWidth = 12.0;
    protected float $logoMargin = 2.0;
    protected string $logoHAlign = 'R';
    protected string $logoVAlign = 'T';

    protected string $barcode1DVAlign = 'M';
    protected string $barcode1DPlacement = 'inline';
    protected string $barcode2DPlacement = 'inline';
    protected string $logoPlacement = 'inline';

    protected float $textSizeMod = 1.0;
    protected float $textAreaOffsetY = 0.0;
    protected string $textRenderMode = 'block';

    public function __construct(
        float $width = 50.0,
        float $height = 24.0,
        float $labelGap = 0,
    )
    {
        $this->width = $width;
        $this->height = $height;
        $this->labelGap = $labelGap;
    }

}