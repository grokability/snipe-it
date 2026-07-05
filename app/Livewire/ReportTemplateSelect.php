<?php

namespace App\Livewire;

use App\Models\ReportTemplate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ReportTemplateSelect extends Component
{
    public $type;

    public function render()
    {
        return view('livewire.report-template-select');
    }

    #[Computed]
    public function templates()
    {
        return ReportTemplate::query()
            ->when($this->type, fn ($query) => $query->where('type', $this->type))
            ->orderBy('name')
            ->get();
    }

    protected function rules()
    {
        return [
            'type' => Rule::in(['asset', 'component']),
        ];
    }
}
