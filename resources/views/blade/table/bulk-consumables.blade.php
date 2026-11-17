@if (Gate::any(['update', 'delete'], \App\Models\Consumable::class))
    <x-table.bulk-actions
        name="consumables"
        :action_route="route('consumables.bulkedit.show')"
        model_name="consumable"
        :actions="array_filter([
            'edit' => (request('status') !== 'Deleted' && Gate::allows('update', \App\Models\Consumable::class))
                ? ['label' => trans('general.bulk_edit')] : null,
            'delete' => (request('status') !== 'Deleted' && Gate::allows('delete', \App\Models\Consumable::class))
                ? ['label' => trans('general.delete')] : null,
            'restore' => (request('status') === 'Deleted' && Gate::allows('delete', \App\Models\Consumable::class))
                ? ['label' => trans('general.restore')] : null,
        ])"
    />
@endif
