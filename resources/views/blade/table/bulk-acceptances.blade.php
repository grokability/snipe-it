@props([
    'name' => 'unacceptedAcceptances',
])

{{-- Same gate as the page + API endpoint: canCheckoutAtLeastOneItemType.
     If you can hand an item out, you can bug the recipient about
     accepting it. --}}
@can('canCheckoutAtLeastOneItemType')
    <x-table.bulk-actions
        :name="$name"
        :action_route="route('unaccepted-acceptances.bulk-send-reminders')"
        model_name="checkout_acceptance"
    >
        <option value="send_reminder">{{ trans('admin/reports/general.send_reminder') }}</option>
    </x-table.bulk-actions>
@endcan
