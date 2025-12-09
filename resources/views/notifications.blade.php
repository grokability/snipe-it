@if ($errors->any())
<div class="col-md-12" id="error-notification">
    <div class="alert alert-danger fade in">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-exclamation-triangle faa-pulse animated"></i>
        <strong>{{ trans('general.notification_error') }}:</strong>
         {{ trans('general.notification_error_hint') }}
    </div>
</div>

@endif


@if ($message = session()->get('status'))
    <div class="col-md-12" id="success-notification">
        <div class="alert alert-success fade in">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check faa-pulse animated"></i>
            <strong>{{ trans('general.notification_success') }}: </strong>
            {{ $message }}
        </div>
    </div>
@endif


@if ($message = session()->get('success'))
<div class="col-md-12" id="success-notification">
    <div class="alert alert-success fade in">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-check faa-pulse animated"></i>
        <strong>{{ trans('general.notification_success') }}: </strong>
        {{ $message }}
    </div>
</div>
@include ('partials.confetti-js')
@endif


@if ($message = session()->get('success-unescaped'))
    <div class="col-md-12" id="success-notification">
        <div class="alert alert-success fade in">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check faa-pulse animated"></i>
            <strong>{{ trans('general.notification_success') }}: </strong>
            {!!  $message !!}
        </div>
    </div>
    @include ('partials.confetti-js')
@endif


@if ($assets = session()->get('assets'))
    @foreach ($assets as $asset)
        <div class="col-md-12" id="multi-error-notification">
            <div class="alert alert-info fade in">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-info-circle faa-pulse animated"></i>
                <strong>{{ trans('general.asset_information') }}:</strong>
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

            </div>
        </div>
    @endforeach
@endif


@if ($consumables = session()->get('consumables'))
    @foreach ($consumables as $consumable)
        <div class="col-md-12" id="success-notification">
            <div class="alert alert-info fade in">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-info-circle faa-pulse animated"></i>
                <strong>{{ trans('general.consumable_information') }}: </strong>
                <ul><li><b>{{ trans('general.consumable_name') }}</b> {{ $consumable->name }}</li></ul>
            </div>
        </div>
    @endforeach
@endif


@if ($accessories = session()->get('accessories'))
    @foreach ($accessories as $accessory)
        <div class="col-md-12">
            <div class="alert alert-info fade in">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-info-circle faa-pulse animated"></i>
                <strong>{{ trans('general.accessory_information') }}:</strong>
                <ul><li><b>{{ trans('general.accessory_name') }}</b> {{ $accessory->name }}</li></ul>
            </div>
        </div>
    @endforeach
@endif


@if ($message = session()->get('error'))
<div class="col-md-12">
    <div class="alert alert alert-danger fade in">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-exclamation-triangle faa-pulse animated"></i>
        <strong>{{ trans('general.error') }}: </strong>
        {{ $message }}
    </div>
</div>
@endif


@if ($messages = session()->get('error_messages'))
@foreach ($messages as $message)        
<div class="col-md-12">
    <div class="alert alert alert-danger fade in">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-exclamation-triangle faa-pulse animated"></i>
        <strong>{{ trans('general.notification_error') }}: </strong>
        {{ $message }}
    </div>
</div>
@endforeach
@endif


@if ($messages = session()->get('bulk_asset_errors'))
<div class="col-md-12">
    <div class="alert alert alert-danger fade in">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-exclamation-triangle faa-pulse animated"></i>
        <strong>{{ trans('general.notification_error') }}: </strong>
       {{ trans('general.notification_bulk_error_hint') }}
            @foreach($messages as $key => $message)
                @for ($x = 0; $x < count($message); $x++)
                <ul>
                    <li>{{ $message[$x] }}</li>
                </ul>
            @endfor
            @endforeach
    </div>
</div>
@endif

@if ($messages = session()->get('multi_error_messages'))
    <div class="col-md-12">
        <div class="alert alert alert-warning fade in">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-triangle faa-pulse animated"></i>
            <strong>{{ trans('general.notification_error') }}: </strong>
            <ul>
                @foreach(array_splice($messages, 0,3) as $key => $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
            <details>
                <summary>{{ trans('general.show_all') }}</summary>
                <ul>
                @foreach(array_splice($messages, 3) as $key => $message)
                  <li>{{ $message }}</li>
                @endforeach
                </ul>
            </details>
        </div>
    </div>
@endif


@if ($message = session()->get('warning'))
<div class="col-md-12">
    <div class="alert alert-warning fade in">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-exclamation-triangle faa-pulse animated"></i>
        <strong>{{ trans('general.notification_warning') }}: </strong>
        {{ $message }}
    </div>
</div>
@endif


@if ($message = session()->get('info'))
<div class="col-md-12">
    <div class="alert alert-info fade in">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-info-circle faa-pulse animated"></i>
        <strong>{{ trans('general.notification_info') }}: </strong>
        {{ $message }}
    </div>
</div>
@endif
