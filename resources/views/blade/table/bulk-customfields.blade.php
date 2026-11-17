@can('delete', \App\Models\CustomField::class)
    <x-table.bulk-actions
        name="customField"
        :action_route="route('fields.bulk.delete')"
        model_name="custom_fields"
        :actions="[
            'delete' => ['label' => trans('general.delete')],
        ]"
    />
@endcan
