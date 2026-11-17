@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.bulk_delete') }}
    @parent
@stop

{{-- Page content --}}
@section('content')
    <x-container class="col-md-8 col-md-offset-2">
        <p>{{ trans('general.bulk_delete_help', ['items' => trans('general.consumables')]) }}</p>

        <x-form route="{{ route('consumables.bulkdelete.store') }}">
            <x-box>
                <x-callout type="warning" icon="warning" live="assertive">
                    {{ trans_choice('general.bulk_delete_warn', count($consumables), ['count' => count($consumables), 'item' => trans('general.consumable'), 'items' => trans('general.consumables')]) }}
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
                        @foreach ($consumables as $consumable)
                            <tr @class(['bulk-action-incompatible' => ! $consumable->isDeletable()])>
                                <td>
                                    @if ($consumable->isDeletable())
                                        <input type="checkbox" name="ids[]" value="{{ $consumable->id }}" checked>
                                    @else
                                        <x-icon type="x" />
                                    @endif
                                </td>
                                <td>{{ $consumable->id }}</td>
                                <td>{{ $consumable->name }}</td>
                                <td>{{ $consumable->category?->name }}</td>
                                <td>{{ $consumable->location?->display_name }}</td>
                                <td>{{ $consumable->qty }}</td>
                                <td>{{ $consumable->consumables_users_count }}</td>
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
