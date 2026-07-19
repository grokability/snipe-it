@if ($errors->any())
    <div class="col-md-12" id="error-notification">
        <x-alert type="danger" icon="warning" :title="trans('general.notification_error')" aria-live="assertive" aria-atomic="true">
            <button type="button" class="close" data-dismiss="alert" aria-label="{{ trans('general.close') }}"><span aria-hidden="true">&times;</span></button>
            {{ trans('general.notification_error_hint') }}
        </x-alert>
    </div>
@endif


@if ($message = session()->get('status'))
    <div class="col-md-12" id="success-notification">
        <x-alert type="success" icon="checkmark" :title="trans('general.notification_success')" role="status" aria-live="polite" aria-atomic="true">
            <button type="button" class="close" data-dismiss="alert" aria-label="{{ trans('general.close') }}"><span aria-hidden="true">&times;</span></button>
            {{ $message }}
        </x-alert>
    </div>
@endif


@if ($message = session()->get('success'))
    <div class="col-md-12" id="success-notification">
        <x-alert type="success" icon="checkmark" :title="trans('general.notification_success')" role="status" aria-live="polite" aria-atomic="true">
            <button type="button" class="close" data-dismiss="alert" aria-label="{{ trans('general.close') }}"><span aria-hidden="true">&times;</span></button>
            {{ $message }}
        </x-alert>
    </div>
    @include ('partials.confetti-js')
    @include ('partials.success-sound-js')
@endif


@if ($message = session()->get('success-unescaped'))
    <div class="col-md-12" id="success-notification">
        <x-alert type="success" icon="checkmark" :title="trans('general.notification_success')" role="status" aria-live="polite" aria-atomic="true">
            <button type="button" class="close" data-dismiss="alert" aria-label="{{ trans('general.close') }}"><span aria-hidden="true">&times;</span></button>
            {!! $message !!}
        </x-alert>
    </div>
    @include ('partials.confetti-js')
    @include ('partials.success-sound-js')
@endif


@if ($assets = session()->get('assets'))
    @foreach ($assets as $asset)
        <div class="col-md-12" id="multi-error-notification">
            <x-alert type="info" icon="info-circle" :title="trans('general.asset_information')" role="status" aria-live="polite" aria-atomic="true">
                <button type="button" class="close" data-dismiss="alert" aria-label="{{ trans('general.close') }}"><span aria-hidden="true">&times;</span></button>
                <ul>
                    @isset ($asset->model->name)
                        <li><b>{{ trans('general.model_name') }} </b> {{ $asset->model->name }}</li>
                    @endisset
                    @isset ($asset->name)
                        <li><b>{{ trans('general.asset_name') }} </b> {{ $asset->model->name }}</li>
                    @endisset
                    <li><b>{{ trans('general.asset_tag') }}</b> {{ $asset->asset_tag }}</li>
                    @isset ($asset->notes)
                        <li><b>{{ trans('general.notes') }}</b> {{ $asset->notes }}</li>
                    @endisset
                </ul>
            </x-alert>
        </div>
    @endforeach
@endif


@if ($consumables = session()->get('consumables'))
    @foreach ($consumables as $consumable)
        <div class="col-md-12" id="success-notification">
            <x-alert type="info" icon="info-circle" :title="trans('general.consumable_information')" role="status" aria-live="polite" aria-atomic="true">
                <button type="button" class="close" data-dismiss="alert" aria-label="{{ trans('general.close') }}"><span aria-hidden="true">&times;</span></button>
                <ul><li><b>{{ trans('general.consumable_name') }}</b> {{ $consumable->name }}</li></ul>
            </x-alert>
        </div>
    @endforeach
@endif


@if ($accessories = session()->get('accessories'))
    @foreach ($accessories as $accessory)
        <div class="col-md-12">
            <x-alert type="info" icon="info-circle" :title="trans('general.accessory_information')" role="status" aria-live="polite" aria-atomic="true">
                <button type="button" class="close" data-dismiss="alert" aria-label="{{ trans('general.close') }}"><span aria-hidden="true">&times;</span></button>
                <ul><li><b>{{ trans('general.accessory_name') }}</b> {{ $accessory->name }}</li></ul>
            </x-alert>
        </div>
    @endforeach
@endif


@if ($message = session()->get('error'))
    <div class="col-md-12">
        <x-alert type="danger" icon="warning" :title="trans('general.error')" aria-live="assertive" aria-atomic="true">
            <button type="button" class="close" data-dismiss="alert" aria-label="{{ trans('general.close') }}"><span aria-hidden="true">&times;</span></button>
            {{ $message }}
        </x-alert>
    </div>
@endif


@if ($messages = session()->get('error_messages'))
    @foreach ($messages as $message)
        <div class="col-md-12">
            <x-alert type="danger" icon="warning" :title="trans('general.notification_error')" aria-live="assertive" aria-atomic="true">
                <button type="button" class="close" data-dismiss="alert" aria-label="{{ trans('general.close') }}"><span aria-hidden="true">&times;</span></button>
                {{ $message }}
            </x-alert>
        </div>
    @endforeach
@endif


@if ($messages = session()->get('bulk_asset_errors'))
    <div class="col-md-12">
        <x-alert type="danger" icon="warning" :title="trans('general.notification_error')" aria-live="assertive" aria-atomic="true">
            <button type="button" class="close" data-dismiss="alert" aria-label="{{ trans('general.close') }}"><span aria-hidden="true">&times;</span></button>
            {{ trans('general.notification_bulk_error_hint') }}
            @foreach ($messages as $key => $message)
                @for ($x = 0; $x < count($message); $x++)
                    <ul>
                        <li>{{ $message[$x] }}</li>
                    </ul>
                @endfor
            @endforeach
        </x-alert>
    </div>
@endif


@if ($messages = session()->get('multi_error_messages'))
    <div class="col-md-12">
        <x-alert type="warning" icon="warning" :title="trans('general.notification_error')" aria-live="assertive" aria-atomic="true">
            <button type="button" class="close" data-dismiss="alert" aria-label="{{ trans('general.close') }}"><span aria-hidden="true">&times;</span></button>
            <ul>
                @foreach (array_splice($messages, 0, 3) as $key => $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
            <details>
                <summary>{{ trans('general.show_all') }}</summary>
                <ul>
                    @foreach (array_splice($messages, 3) as $key => $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </details>
        </x-alert>
    </div>
@endif


@if ($message = session()->get('warning'))
    <div class="col-md-12">
        <x-alert type="warning" icon="warning" :title="trans('general.notification_warning')" aria-live="assertive" aria-atomic="true">
            <button type="button" class="close" data-dismiss="alert" aria-label="{{ trans('general.close') }}"><span aria-hidden="true">&times;</span></button>
            {{ $message }}
        </x-alert>
    </div>
@endif


@if ($message = session()->get('info'))
    <div class="col-md-12">
        <x-alert type="info" icon="info-circle" :title="trans('general.notification_info')" role="status" aria-live="polite" aria-atomic="true">
            <button type="button" class="close" data-dismiss="alert" aria-label="{{ trans('general.close') }}"><span aria-hidden="true">&times;</span></button>
            {{ $message }}
        </x-alert>
    </div>
@endif
