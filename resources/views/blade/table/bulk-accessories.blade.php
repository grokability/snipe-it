@if (Gate::any(['update', 'delete'], \App\Models\Accessory::class))
    <x-table.bulk-actions
        name="accessories"
        :action_route="route('accessories.bulkedit.show')"
        model_name="accessory"
        :actions="array_filter([
            'edit' => (request('status') !== 'Deleted' && Gate::allows('update', \App\Models\Accessory::class))
                ? ['label' => trans('general.bulk_edit')] : null,
            'delete' => (request('status') !== 'Deleted' && Gate::allows('delete', \App\Models\Accessory::class))
                ? ['label' => trans('general.delete')] : null,
            'restore' => (request('status') === 'Deleted' && Gate::allows('delete', \App\Models\Accessory::class))
                ? ['label' => trans('general.restore')] : null,
        ])"
    />
@endif
