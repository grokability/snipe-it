@extends('layouts/default')

@section('title')
    {{ trans('general.checkin') }}
    @parent
@stop

@section('content')
    <x-bulk-checkin-form
        :action_route="route('components.bulkcheckin.store')"
        :ids="$ids"
        :count="count($assignments)"
        :return_url="$return_url"
    >
        <table class="table table-striped" style="margin-bottom: 25px; border-bottom: 1px solid light-dark(#eeeeee, #656464);">
            <thead>
                <tr>
                    <th>{{ trans('general.id') }}</th>
                    <th>{{ trans('general.component') }}</th>
                    <th>{{ trans('general.asset') }}</th>
                    <th>{{ trans('general.qty') }}</th>
                    <th>{{ trans('admin/hardware/table.checkout_date') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($assignments as $assignment)
                    <tr>
                        <td>{{ $assignment->id }}</td>
                        <td>{{ $assignment->component?->name }}</td>
                        <td>{{ $assignment->resolvedAsset?->present()?->fullName ?? trans('general.deleted') }}</td>
                        <td>{{ (int) $assignment->assigned_qty }}</td>
                        <td>{{ \App\Helpers\Helper::getFormattedDateObject($assignment->created_at, 'datetime', false) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-bulk-checkin-form>
@stop
