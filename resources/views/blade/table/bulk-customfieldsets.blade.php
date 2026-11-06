@can('delete', \App\Models\CustomFieldset::class)
    <x-table.bulk-actions
        name="customFieldset"
        :action_route="route('fieldsets.bulk.delete')"
        model_name="custom_fieldsets"
        :actions="[
            'delete' => ['label' => trans('general.delete')],
        ]"
    />
@endcan
