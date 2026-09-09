<?php

namespace App\Livewire\Labels;

use App\Services\CustomLabelImportValidator;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImportLabel extends Component
{
    use WithFileUploads;

    public string $importMethod = 'json';

    public string $configSnapshot = '';

    public $configFile;

    public array $validationMessages = [];

    protected function rules(): array
    {
        $rules = [
            'importMethod' => ['required', 'in:json,text'],
        ];

        if ($this->importMethod === 'json') {
            $rules['configFile'] = [
                'required',
                'file',
                'mimes:json,txt',
            ];
        }

        if ($this->importMethod === 'text') {
            $rules['configSnapshot'] = [
                'required',
                'string',
            ];
        }

        return $rules;
    }
    public function setImportMethod(string $method)
    {
        $this->importMethod = $method;

        $this->resetValidation();
        $this->validationMessages = [];
    }
    protected function rawConfigJson(): ?string
    {
        return $this->importMethod === 'json'
            ? $this->configFile->get()
            : $this->configSnapshot;
    }
    public function import(CustomLabelImportValidator $importValidator)
    {
        $this->validate();

        $rawJson = $this->importMethod === 'json'
            ? $this->configFile->get()
            : $this->configSnapshot;

        try {
            $config = $importValidator->validate($rawJson);
        } catch (ValidationException $e) {
            $this->validationMessages = $e->validator
                ->errors()
                ->all();

            return;
        }

        session()->put('imported_label_config', $config);

        $this->redirectRoute('settings.labels.create', [
            'label' => $config['template'] ?? null,
            'import' => 1,
        ]);
    }

    public function render()
    {
        return view('livewire.labels.import-label');
    }
}