@props([
    'action_route',
    'ids',
    'count',
    'return_url' => null,
])

<x-container class="col-md-8 col-md-offset-2">
    <x-form :route="$action_route">
        @if ($return_url)
            <input type="hidden" name="return_url" value="{{ $return_url }}">
        @endif

        <x-box>
            <x-slot:header>
                {{ trans_choice('general.bulk_checkin_warn', $count, ['count' => $count]) }}
            </x-slot:header>

            <x-callout type="info" icon="tip" live="polite">
                {{ trans('general.bulk_checkin_note_help') }}
            </x-callout>

            {{ $slot }}

            @foreach ($ids as $id)
                <input type="hidden" name="ids[]" value="{{ $id }}">
            @endforeach

            <x-form.row
                :label="trans('general.notes')"
                name="note"
                type="textarea"
            />

            <x-slot:customfooter>
                <div class="box-footer text-right">
                    <a class="btn btn-link pull-left" href="{{ $return_url ?? URL::previous() }}">{{ trans('button.cancel') }}</a>
                    <button type="submit" class="btn btn-success" id="submit-button">
                        <x-icon type="checkin" /> {{ trans('general.checkin') }}
                    </button>
                </div>
            </x-slot:customfooter>
        </x-box>
    </x-form>
</x-container>
