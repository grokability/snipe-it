@php
    $pull = function(string $key) {
        return session()->has($key) ? session()->get($key) : null;
    };
@endphp

@if ($errors->any())
    <x-alert
        type="danger"
        :heading="trans('general.notification_error')"
        id="validation-errors"
    >
        {{ trans('general.notification_error_hint') }}
    </x-alert>
@endif

@if ($msg = $pull('status'))
    <x-alert type="success" :heading="trans('general.notification_success')" id="status-notification">
        {{ $msg }}
    </x-alert>
@endif

@if ($msg = $pull('success'))
    <x-alert type="success" :heading="trans('general.notification_success')" id="success-notification" confetti="true">
        {{ $msg }}
    </x-alert>
@endif

@if ($msg = $pull('success-unescaped'))
    <x-alert type="success" :heading="trans('general.notification_success')" id="success-unescaped-notification" html="true" confetti="true">
        {!! $msg !!}
    </x-alert>
@endif

@if ($assets = $pull('assets'))
    @foreach ($assets as $asset)
        <x-alert type="info" :heading="trans('general.asset_information')" html="true" :id="'asset-info-'.$loop->index">
            <ul style="margin:0;padding-left:18px;">
                @isset($asset->model->name)
                    <li><strong>{{ trans('general.model_name') }}</strong> {{ $asset->model->name }}</li>
                @endisset
                @isset($asset->name)
                    <li><strong>{{ trans('general.asset_name') }}</strong> {{ $asset->name }}</li>
                @endisset
                <li><strong>{{ trans('general.asset_tag') }}</strong> {{ $asset->asset_tag }}</li>
                @isset($asset->notes)
                    <li><strong>{{ trans('general.notes') }}</strong> {{ $asset->notes }}</li>
                @endisset
            </ul>
        </x-alert>
    @endforeach
@endif

@if ($consumables = $pull('consumables'))
    @foreach ($consumables as $consumable)
        <x-alert type="info" :heading="trans('general.consumable_information')" html="true" :id="'consumable-info-'.$loop->index">
            <ul style="margin:0;padding-left:18px;">
                <li><strong>{{ trans('general.consumable_name') }}</strong> {{ $consumable->name }}</li>
            </ul>
        </x-alert>
    @endforeach
@endif

@if ($accessories = $pull('accessories'))
    @foreach ($accessories as $accessory)
        <x-alert type="info" :heading="trans('general.accessory_information')" html="true" :id="'accessory-info-'.$loop->index">
            <ul style="margin:0;padding-left:18px;">
                <li><strong>{{ trans('general.accessory_name') }}</strong> {{ $accessory->name }}</li>
            </ul>
        </x-alert>
    @endforeach
@endif

@if ($msg = $pull('error'))
    <x-alert type="danger" :heading="trans('general.error')" id="error-notification">
        {{ $msg }}
    </x-alert>
@endif

@if ($messages = $pull('error_messages'))
    @foreach ($messages as $message)
        <x-alert type="danger" :heading="trans('general.notification_error')" :id="'error-msg-'.$loop->index">
            {{ $message }}
        </x-alert>
    @endforeach
@endif

@if ($bulk = $pull('bulk_asset_errors'))
    <x-alert type="danger" :heading="trans('general.notification_error')" html="true" id="bulk-asset-errors">
        {{ trans('general.notification_bulk_error_hint') }}
        @foreach ($bulk as $key => $set)
            @foreach ($set as $entry)
                <ul style="margin:0;padding-left:18px;">
                    <li>{{ $entry }}</li>
                </ul>
            @endforeach
        @endforeach
    </x-alert>
@endif

@if ($messages = session()->get('multi_error_messages'))
    <div class="col-md-12">
        <div class="alert alert-warning fade in">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-triangle faa-pulse animated"></i>
            <strong>{{ trans('general.notification_error') }}: </strong>
            <ul>
                @foreach(array_splice($messages, 0, 3) as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
            @if (count($messages) > 0)
                <details>
                    <summary>{{ trans('general.show_all') }}</summary>
                    <ul>
                        @foreach($messages as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </details>
            @endif
        </div>
    </div>
@endif

@if ($msg = $pull('warning'))
    <x-alert type="warning" :heading="trans('general.notification_warning')" id="warning-notification">
        {{ $msg }}
    </x-alert>
@endif

@if ($msg = $pull('info'))
    <x-alert type="info" :heading="trans('general.notification_info')" id="info-notification">
        {{ $msg }}
    </x-alert>
@endif
