<?php

namespace App\Livewire;

use App\Models\CustomFieldset;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CloneFieldset extends Component
{
    public bool $iconOnly = false;

    /**
     * When true, the component renders only its teleported modal, no
     * inline trigger link. Used by the custom fields index, where the
     * clone button lives in the bs-table AJAX actions column and opens
     * the matching modal by data-target rather than via an inline
     * trigger.
     */
    public bool $triggerless = false;

    #[Locked]
    public int $id;

    public string $name = '';

    public function render()
    {
        return view('livewire.clone-fieldset');
    }

    #[Computed]
    public function title(): string
    {
        return sprintf(
            '%s: %s',
            trans('admin/custom_fields/general.clone_fieldset'),
            CustomFieldset::find($this->id)?->name
        );
    }

    public function submit()
    {
        $original = CustomFieldset::findOrFail($this->id);

        $this->authorize('clone', $original);

        $this->validate((new CustomFieldset)->rules);

        $fieldset = DB::transaction(function () use ($original) {
            $fieldset = new CustomFieldset(['name' => $this->name]);
            $fieldset->created_by = auth()->id();
            $fieldset->save();

            $pivot = $original->fields->mapWithKeys(function ($field) {
                return [$field->id => $field->pivot->only(['order', 'required'])];
            });

            $fieldset
                ->fields()
                ->attach($pivot);

            return $fieldset;
        });

        session()->flash('success', trans('admin/custom_fields/message.fieldset.clone.success'));

        return $this->redirectRoute('fieldsets.show', [$fieldset->id]);
    }
}
