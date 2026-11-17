@extends('layouts/default')

@section('title')
    {{ trans('general.checkin') }}
    @parent
@stop

@section('content')
    <x-bulk-checkin-form
        :action_route="route('accessories.bulkcheckin.store')"
        :ids="$ids"
        :count="count($checkouts)"
        :return_url="$return_url"
    >
        <table class="table table-striped" style="margin-bottom: 25px; border-bottom: 1px solid light-dark(#eeeeee, #656464);">
            <thead>
                <tr>
                    <th>{{ trans('general.id') }}</th>
                    <th>{{ trans('general.accessory') }}</th>
                    <th>{{ trans('general.checked_out_to') }}</th>
                    <th>{{ trans('admin/hardware/table.checkout_date') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($checkouts as $checkout)
                    <tr>
                        <td>{{ $checkout->id }}</td>
                        <td>{{ $checkout->accessory?->name }}</td>
                        <td>{{ $checkout->assignedTo?->present()?->fullName ?? $checkout->assignedTo?->display_name ?? trans('general.deleted') }}</td>
                        <td>{{ \App\Helpers\Helper::getFormattedDateObject($checkout->created_at, 'datetime', false) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-bulk-checkin-form>
@stop
