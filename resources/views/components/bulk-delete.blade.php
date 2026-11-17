@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.bulk_delete') }}
    @parent
@stop

{{-- Page content --}}
@section('content')
    <x-container class="col-md-8 col-md-offset-2">
        <p>{{ trans('general.bulk_delete_help', ['items' => trans('general.components')]) }}</p>

        <x-form route="{{ route('components.bulkdelete.store') }}">
            <x-box>
                <x-callout type="warning" icon="warning" live="assertive">
                    {{ trans_choice('general.bulk_delete_warn', count($components), ['count' => count($components), 'item' => trans('general.component'), 'items' => trans('general.components')]) }}
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
                        @foreach ($components as $component)
                            <tr @class(['bulk-action-incompatible' => ! $component->isDeletable()])>
                                <td>
                                    @if ($component->isDeletable())
                                        <input type="checkbox" name="ids[]" value="{{ $component->id }}" checked>
                                    @else
                                        <x-icon type="x" />
                                    @endif
                                </td>
                                <td>{{ $component->id }}</td>
                                <td>{{ $component->name }}</td>
                                <td>{{ $component->category?->name }}</td>
                                <td>{{ $component->location?->display_name }}</td>
                                <td>{{ $component->qty }}</td>
                                <td>{{ $component->numCheckedOut() }}</td>
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
