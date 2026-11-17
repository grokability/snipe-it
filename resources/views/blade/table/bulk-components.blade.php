@if (Gate::any(['update', 'delete'], \App\Models\Component::class))
    <x-table.bulk-actions
        name="components"
        :action_route="route('components.bulkedit.show')"
        model_name="component"
        :actions="array_filter([
            'edit' => (request('status') !== 'Deleted' && Gate::allows('update', \App\Models\Component::class))
                ? ['label' => trans('general.bulk_edit')] : null,
            'delete' => (request('status') !== 'Deleted' && Gate::allows('delete', \App\Models\Component::class))
                ? ['label' => trans('general.delete')] : null,
            'restore' => (request('status') === 'Deleted' && Gate::allows('delete', \App\Models\Component::class))
                ? ['label' => trans('general.restore')] : null,
        ])"
    />
@endif
