<?php

namespace App\Livewire\Labels;

use Livewire\Component;
use App\Models\Labels\RectangleSheet;
use App\Models\Labels\DefaultLabel;
class NewLabelSetup extends Component
{
    public string $type = 'sheet';
    public string $pageSize = 'letter';
    public ?float $labelWidth = null;
    public ?float $labelHeight = null;
    public ?float $labelGap = null;
    public ?int $columns = null;
    public ?int $rows = null;

    public function getColumnsProperty(): int
    {
        if ($this->type !== 'sheet' || ! $this->labelWidth) {
            return 0;
        }

        $page = RectangleSheet::supportedPageSize($this->pageSize);

        return RectangleSheet::calculateGridCount($page['width'], $this->columns, 1.2);
    }
    public function getRowsProperty(): int
    {
        if ($this->type !== 'sheet' || ! $this->labelHeight) {
            return 0;
        }

        $page = RectangleSheet::supportedPageSize($this->pageSize);

        return RectangleSheet::calculateGridCount($page['height'], $this->rows, 1.2);
    }

    public function getLabelsPerSheetProperty(): int
    {
        return $this->columns * $this->rows;
    }
    public function render()
    {
        return view('livewire.labels.new-label-setup', [
            'pageSizes' => RectangleSheet::supportedPageSizes(),
        ]);
    }
}
