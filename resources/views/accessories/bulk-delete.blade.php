@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.bulk_delete') }}
    @parent
@stop

{{-- Page content --}}
@section('content')
    <x-container class="col-md-8 col-md-offset-2">
        <p>{{ trans('general.bulk_delete_help', ['items' => trans('general.accessories')]) }}</p>

        <x-form route="{{ route('accessories.bulkdelete.store') }}">
            <x-box>
                <x-callout type="warning" icon="warning" live="assertive">
                    {{ trans_choice('general.bulk_delete_warn', count($accessories), ['count' => count($accessories), 'item' => trans('general.accessory'), 'items' => trans('general.accessories')]) }}
                </x-callout>

                <table class="table table-striped">
                    <tr>
                        <th></th>
                        <th>{{ trans('general.id') }}</th>
                        <th>{{ trans('general.name') }}</th>
                        <th>{{ trans('general.category') }}</th>
                        <th>{{ trans('general.location') }}</th>
                        <th>{{ trans('general.qty') }}</th>
                        <th>{{ trans('general.checked_out') }}</th>
                    </tr>

                    <tbody>
                        @foreach ($accessories as $accessory)
                            <tr @class(['bulk-action-incompatible' => ! $accessory->isDeletable()])>
                                <td>
                                    @if ($accessory->isDeletable())
                                        <input type="checkbox" name="ids[]" value="{{ $accessory->id }}" checked>
                                    @else
                                        <x-icon type="x" />
                                    @endif
                                </td>
                                <td>{{ $accessory->id }}</td>
                                <td>{{ $accessory->name }}</td>
                                <td>{{ $accessory->category?->name }}</td>
                                <td>{{ $accessory->location?->display_name }}</td>
                                <td>{{ $accessory->qty }}</td>
                                <td>{{ $accessory->checkouts_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <x-slot:customfooter>
                    <div class="box-footer text-right">
                        <a class="btn btn-link pull-left" href="{{ URL::previous() }}">{{ trans('button.cancel') }}</a>
                        <button type="submit" class="btn btn-success" id="submit-button">
                            <x-icon type="checkmark" /> {{ trans('button.delete') }}
                        </button>
                    </div>
                </x-slot:customfooter>
            </x-box>
        </x-form>
    </x-container>
@stop
