@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/consumables/general.bulk_consume') }}
    @parent
@stop

{{-- Page content --}}
@section('content')
    <x-container class="col-md-8 col-md-offset-2">
        <x-form route="{{ route('consumables.bulkconsume.store') }}">
            <x-box>
                <x-callout type="warning" icon="warning" live="assertive">
                    {{ trans_choice('admin/consumables/general.bulk_consume_warn', $assignments->count(), ['count' => $assignments->count()]) }}
                </x-callout>

                <x-callout type="info" icon="tip" live="polite">
                    {{ trans('admin/consumables/general.bulk_consume_help') }}
                </x-callout>

                <table class="table table-striped" style="margin-bottom: 25px; border-bottom: 1px solid light-dark(#eeeeee, #656464);">
                    <thead>
                        <tr>
                            <th>{{ trans('general.name') }}</th>
                            <th>{{ trans('general.date') }}</th>
                            <th>{{ trans('general.notes') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assignments as $assignment)
                            <tr>
                                <td>{{ $assignment->user?->present()?->fullName ?? $assignment->user?->display_name ?? trans('general.deleted') }}</td>
                                <td>{{ \App\Helpers\Helper::getFormattedDateObject($assignment->created_at, 'datetime', false) }}</td>
                                <td>{{ $assignment->note }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @foreach ($ids as $id)
                    <input type="hidden" name="ids[]" value="{{ $id }}">
                @endforeach

                <x-slot:customfooter>
                    <div class="box-footer text-right">
                        <a class="btn btn-link pull-left" href="{{ route('consumables.show', $consumable) }}">{{ trans('button.cancel') }}</a>
                        <button type="submit" class="btn btn-success" id="submit-button">
                            <x-icon type="checkmark" /> {{ trans('admin/consumables/general.bulk_consume') }}
                        </button>
                    </div>
                </x-slot:customfooter>
            </x-box>
        </x-form>
    </x-container>
@stop
