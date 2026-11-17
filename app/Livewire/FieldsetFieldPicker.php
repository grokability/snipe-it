<?php

namespace App\Livewire;

use App\Models\CustomField;
use App\Models\CustomFieldset;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Two-panel picker for managing fields on a fieldset. Left panel
 * lists attached fields in order with drag reorder, a required-
 * toggle, a remove button, and bulk-select for batch remove or
 * toggle-required. It also carries the fieldset-name input, so
 * admins can rename the fieldset without bouncing out to the
 * fieldsets index. Right panel lists the unattached custom fields,
 * searchable and paginated.
 *
 * Authorization is checked on mount and re-checked on every mutating
 * action, since Livewire snapshots are replayable.
 */
class FieldsetFieldPicker extends Component
{
    use WithPagination;

    /**
     * Zero until the fieldset is persisted. `#[Locked]` to block
     * client-side snapshot swaps from pointing this component at a
     * different fieldset mid-session.
     */
    #[Locked]
    public int $fieldsetId = 0;

    public string $name = '';

    public string $search = '';

    /** @var array<int, int> */
    public array $selectedFieldIds = [];

    /** @var array<int, int> */
    public array $selectedAvailableIds = [];

    public int $perPage = 15;

    public function mount(?CustomFieldset $fieldset = null): void
    {
        // Two entry points. An existing fieldset (edit) authorizes
        // against the row itself. A fresh fieldset (create) authorizes
        // against the class, since there's nothing to update yet. The
        // create phase stays in-place: the component shows just the
        // name prompt, saves on submit, then re-renders the picker
        // against the brand-new row.
        if ($fieldset && $fieldset->exists) {
            $this->fieldsetId = (int) $fieldset->id;
            $this->name = (string) $fieldset->name;
        }

        // boot() enforces the gate on every request including this
        // one. Nothing phase-specific needed here beyond hydrating
        // the component's initial state.
    }

    /**
     * Runs on every request including hydration from a snapshot,
     * closes the snapshot-replay gap mount() alone can't cover. Every
     * mutating action method also re-authorizes, this is the belt to
     * their suspenders. The phase branch matches mount(): create
     * phase authorizes against the class, edit phase against the row.
     */
    /**
     * Per-request cache for the resolved CustomFieldset. Private so
     * Livewire doesn't serialize it into the snapshot. Reused by
     * boot(), fieldset(), every action, and render() in a single
     * request. Without this cache, each toggleRequired / attach /
     * detach / etc. hits the DB 3+ times for the same row (once in
     * boot's authorize, once in the action's own fieldset() call,
     * once in render's fieldset() call).
     */
    private ?CustomFieldset $resolvedFieldset = null;

    public function boot(): void
    {
        if ($this->fieldsetId === 0) {
            $this->authorize('create', CustomFieldset::class);

            return;
        }

        $this->authorize('update', $this->fieldset());
    }

    /**
     * Create-phase submit. Persists the new fieldset, flips the
     * component into edit mode, and triggers the auto-add sync so the
     * picker's left panel shows any `auto_add_to_fieldsets=1` fields
     * from the moment it renders.
     */
    public function createFieldset(): void
    {
        $this->authorize('create', CustomFieldset::class);

        // Explicit rules rather than reusing the model's $rules via
        // ValidatingTrait, because Livewire's validate() does not know
        // about the trait's injectUniqueIdentifier magic and we need
        // consistent shape with updatedName()'s rename path.
        $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('custom_fieldsets', 'name')],
        ]);

        $fieldset = new CustomFieldset(['name' => $this->name]);
        $fieldset->created_by = auth()->id();
        $fieldset->save();

        $autoAddIds = CustomField::where('auto_add_to_fieldsets', '=', '1')
            ->orderBy('name')
            ->pluck('id')
            ->all();
        if (! empty($autoAddIds)) {
            $pivot = [];
            $order = 1;
            foreach ($autoAddIds as $id) {
                $pivot[(int) $id] = ['order' => $order, 'required' => false];
                $order++;
            }
            $fieldset->fields()->sync($pivot);
        }

        $this->fieldsetId = (int) $fieldset->id;
        session()->flash('success', trans('admin/custom_fields/message.fieldset.create.success'));
    }

    /**
     * Livewire lifecycle hook, fires after $name is synced from
     * wire:model.blur. Only applies during the edit phase (fieldset
     * already persisted). Create phase waits for the explicit
     * createFieldset() submit so a half-typed name doesn't try to
     * save a new row on every keystroke.
     */
    public function updatedName(): void
    {
        if ($this->fieldsetId === 0) {
            return;
        }

        $fieldset = $this->fieldset();
        $this->authorize('update', $fieldset);

        $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('custom_fieldsets', 'name')->ignore($fieldset->id)],
        ]);

        if ($fieldset->name === $this->name) {
            return;
        }

        $fieldset->name = $this->name;
        $fieldset->save();

        session()->flash('success', trans('admin/custom_fields/general.fieldset_updated'));
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    protected function fieldset(): CustomFieldset
    {
        if ($this->resolvedFieldset === null || $this->resolvedFieldset->id !== $this->fieldsetId) {
            $this->resolvedFieldset = CustomFieldset::findOrFail($this->fieldsetId);
        }

        return $this->resolvedFieldset;
    }

    /**
     * After a pivot mutation (attach, detach, reorder, toggleRequired,
     * etc.), the cached fields relation on the resolved fieldset goes
     * stale. Drop it so the next access (typically render()'s magic
     * property load) triggers a fresh belongsToMany query instead of
     * handing back the pre-mutation collection.
     */
    protected function invalidateFieldsRelation(): void
    {
        $this->resolvedFieldset?->unsetRelation('fields');
    }

    public function attach(int $fieldId): void
    {
        $fieldset = $this->fieldset();
        $this->authorize('update', $fieldset);

        $attached = $fieldset->fields;
        $attachedIds = $attached->pluck('id')->map(fn ($x) => (int) $x)->all();
        if (in_array($fieldId, $attachedIds, true)) {
            return;
        }

        $nextOrder = ((int) $attached->max(fn ($f) => (int) ($f->pivot->order ?? 0))) + 1;
        $fieldset->fields()->attach($fieldId, ['order' => $nextOrder, 'required' => false]);
        $this->invalidateFieldsRelation();
    }

    public function detach(int $fieldId): void
    {
        $fieldset = $this->fieldset();
        $this->authorize('update', $fieldset);

        $fieldset->fields()->detach($fieldId);
        $this->invalidateFieldsRelation();
        $this->selectedFieldIds = array_values(array_diff($this->selectedFieldIds, [$fieldId]));
    }

    public function toggleRequired(int $fieldId): void
    {
        $fieldset = $this->fieldset();
        $this->authorize('update', $fieldset);

        $pivot = $fieldset->fields->firstWhere('id', $fieldId)?->pivot;
        if (! $pivot) {
            return;
        }

        $fieldset->fields()->updateExistingPivot($fieldId, [
            'required' => ! $pivot->required,
            'order' => $pivot->order,
        ]);
        $this->invalidateFieldsRelation();
    }

    public function bulkRemove(): void
    {
        $fieldset = $this->fieldset();
        $this->authorize('update', $fieldset);

        if (! empty($this->selectedFieldIds)) {
            $fieldset->fields()->detach($this->selectedFieldIds);
            $this->invalidateFieldsRelation();
        }
        $this->selectedFieldIds = [];
    }

    /**
     * Attach every currently-selected available field in one pass.
     * Preserves relative order by appending starting from the current
     * max.
     */
    public function bulkAttach(): void
    {
        $fieldset = $this->fieldset();
        $this->authorize('update', $fieldset);

        if (empty($this->selectedAvailableIds)) {
            return;
        }

        $attached = $fieldset->fields;
        $attachedIds = $attached->pluck('id')->map(fn ($x) => (int) $x)->all();

        $toAttach = array_values(array_diff(
            array_map('intval', $this->selectedAvailableIds),
            $attachedIds
        ));

        if (empty($toAttach)) {
            $this->selectedAvailableIds = [];

            return;
        }

        $nextOrder = ((int) $attached->max(fn ($f) => (int) ($f->pivot->order ?? 0))) + 1;
        $pivot = [];
        foreach ($toAttach as $id) {
            $pivot[$id] = ['order' => $nextOrder, 'required' => false];
            $nextOrder++;
        }
        $fieldset->fields()->syncWithoutDetaching($pivot);
        $this->invalidateFieldsRelation();

        $this->selectedAvailableIds = [];
    }

    /**
     * Header "select all" for the attached table. Toggles every
     * attached field id in/out of selectedFieldIds based on the
     * checked arg.
     */
    public function toggleSelectAllAttached(bool $checked): void
    {
        if (! $checked) {
            $this->selectedFieldIds = [];

            return;
        }

        $this->selectedFieldIds = $this->fieldset()->fields
            ->pluck('id')
            ->map(fn ($x) => (int) $x)
            ->all();
    }

    /**
     * Header "select all" for the available table. Adds every id on
     * the current page to selectedAvailableIds (additive across
     * pagination), or clears the selection for this page when
     * unchecked.
     *
     * @param  array<int, int|string>  $pageIds
     */
    public function toggleSelectAllAvailable(bool $checked, array $pageIds): void
    {
        $pageIds = array_map('intval', $pageIds);

        if (! $checked) {
            $this->selectedAvailableIds = array_values(array_diff(
                array_map('intval', $this->selectedAvailableIds),
                $pageIds
            ));

            return;
        }

        $this->selectedAvailableIds = array_values(array_unique(array_merge(
            array_map('intval', $this->selectedAvailableIds),
            $pageIds
        )));
    }

    public function bulkSetRequired(bool $required): void
    {
        $fieldset = $this->fieldset();
        $this->authorize('update', $fieldset);

        if (empty($this->selectedFieldIds)) {
            return;
        }

        $selected = array_map('intval', $this->selectedFieldIds);
        foreach ($fieldset->fields->whereIn('id', $selected) as $field) {
            $fieldset->fields()->updateExistingPivot($field->id, [
                'required' => $required,
                'order' => $field->pivot->order,
            ]);
        }
        $this->invalidateFieldsRelation();
    }

    /**
     * wire:sort handler. Called with the moved item's id + its new
     * zero-based position. We own the "shift the others" math because
     * Livewire just reports the drop event.
     */
    public function reorder(int $id, int $position): void
    {
        $fieldset = $this->fieldset();
        $this->authorize('update', $fieldset);

        // Lean on the relation's magic property so Laravel loads the
        // full belongsToMany select (including the aliased pivot
        // columns required by the relation's own orderBy('pivot_order')).
        $orderedPairs = [];
        foreach ($fieldset->fields as $field) {
            $orderedPairs[] = [
                'id' => (int) $field->id,
                'required' => (bool) $field->pivot->required,
            ];
        }

        $moved = null;
        foreach ($orderedPairs as $i => $pair) {
            if ($pair['id'] === $id) {
                $moved = $pair;
                array_splice($orderedPairs, $i, 1);
                break;
            }
        }
        if ($moved === null) {
            return;
        }

        $insertAt = max(0, min($position, count($orderedPairs)));
        array_splice($orderedPairs, $insertAt, 0, [$moved]);

        $pivot = [];
        foreach ($orderedPairs as $i => $pair) {
            $pivot[$pair['id']] = [
                'order' => $i + 1,
                'required' => $pair['required'],
            ];
        }
        $fieldset->fields()->sync($pivot);
        $this->invalidateFieldsRelation();
    }

    public function render()
    {
        // Create phase: the fieldset doesn't exist yet. Render the
        // name-prompt shell only. Picker surface wakes up after
        // createFieldset() flips fieldsetId non-zero.
        // I hate this UX, but it's not any different than the old fieldset-create flow,
        // and it avoids a whole lot of Livewire complexity around
        // "what if the user types a name and then clicks away before hitting create?"
        if ($this->fieldsetId === 0) {
            return view('livewire.fieldset-field-picker', [
                'available' => null,
                'attached' => null,
            ]);
        }

        $fieldset = $this->fieldset();

        // Magic property loads the belongsToMany with all pivot
        // columns aliased, satisfying the relation's own
        // orderBy('pivot_order'). Calling `fields()->pluck(col)`
        // directly rewrites the SELECT without the aliases, and the
        // trailing `ORDER BY pivot_order` then blows up on MySQL.
        $attachedFields = $fieldset->fields;
        $attachedIds = $attachedFields->pluck('id')->map(fn ($x) => (int) $x)->all();

        $available = CustomField::query()
            ->whereNotIn('id', $attachedIds)
            ->when($this->search !== '', function ($q) {
                $q->where(function ($qq) {
                    $qq->where('name', 'LIKE', '%'.$this->search.'%')
                        ->orWhere('db_column', 'LIKE', '%'.$this->search.'%');
                });
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.fieldset-field-picker', [
            'available' => $available,
            'attached' => $attachedFields,
        ]);
    }
}
