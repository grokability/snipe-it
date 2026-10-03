<?php

namespace App\Models\Labels\CustomLabels\Concerns;

trait HasCustomLabelSupports
{
    protected bool $supportAssetTag = true;
    protected bool $support1DBarcode = true;
    protected bool $support2DBarcode = false;
    protected int $supportFields = 1;
    protected bool $supportLogo = false;
    protected bool $supportTitle = false;

    public function getSupportAssetTag(): bool
    {
        return $this->supportAssetTag;
    }

    public function getSupport1DBarcode(): bool
    {
        return $this->support1DBarcode;
    }

    public function getSupport2DBarcode(): bool
    {
        return $this->support2DBarcode;
    }

    public function getSupportFields(): int
    {
        return $this->supportFields;
    }

    public function getSupportLogo(): bool
    {
        return $this->supportLogo;
    }

    public function getSupportTitle(): bool
    {
        return $this->supportTitle;
    }

    protected function hydrateSupports(array $supports): void
    {
        $this->supportFields = isset($supports['fields']) ? (int)$supports['fields'] : $this->supportFields;
        $this->supportAssetTag = (bool)($supports['asset_tag'] ?? $this->supportAssetTag);
        $this->support1DBarcode = (bool)($supports['barcode_1d'] ?? $this->support1DBarcode);
        $this->support2DBarcode = (bool)($supports['barcode_2d'] ?? $this->support2DBarcode);
        $this->supportLogo = (bool)($supports['logo'] ?? $this->supportLogo);
        $this->supportTitle = (bool)($supports['title'] ?? $this->supportTitle);
    }
}