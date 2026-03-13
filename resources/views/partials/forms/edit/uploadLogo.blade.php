<!-- {{ $logoVariable }}logo image upload -->

<div class="form-group">
    <div class="col-md-3 control-label{!! $errors->has($logoVariable) ? ' error' : '' !!}">
        <strong>
        {{ trans($logoLabel) }}
        </strong>
    </div>
    <div class="col-md-9">
        <label class="btn btn-theme{{ (config('app.lock_passwords')) ? ' disabled' : '' }}">
            {{ trans('button.select_file')  }}
            <input type="file" name="{{ $logoVariable }}" class="js-uploadFile" id="{{ $logoId }}"
                   accept="{{  $allowedTypes ?? "image/gif,image/jpeg,image/webp,image/png,image/svg,image/svg+xml,image/avif" }}"
                   data-maxsize="{{ $maxSize ?? Helper::file_upload_max_size() }}"
                   style="display:none; max-width: 90%"{{ (config('app.lock_passwords')) ? ' disabled' : '' }}>
        </label>

        <span class='label label-default' id="{{ $logoId }}-info"></span>

        {!! $errors->first($logoVariable, '<span class="alert-msg">:message</span>') !!}


        <p class="help-block" style="!important" id="{{ $logoId }}-status">
            {{ $helpBlock }}
        </p>

        @if (config('app.lock_passwords')===true)
            <p class="text-warning">
                <x-icon type="locked" />
                {{ trans('general.feature_disabled') }}</p>
        @endif
    </div>

    <div class="col-md-9 col-md-offset-3">

            @if (($setting->$logoVariable!='') && (Storage::disk('public')->exists(($logoPath ?? ''). $snipeSettings->$logoVariable)))
                <div class="pull-left {{ $previewClass ?? '' }}" style="padding-right: 20px;">
                    <a href="{{ Storage::disk('public')->url(e(($logoPath ?? '').$snipeSettings->$logoVariable)) }}"{!! ($logoVariable!='favicon') ? ' data-toggle="lightbox"' : '' !!} title="Existing logo">
                        <img style="height: 80px; padding-bottom: 5px;" alt="Current logo" src="{{ Storage::disk('public')->url(e(($logoPath ?? ''). $snipeSettings->$logoVariable)) }}">
                    </a>
                </div>
            @endif

            <div id="{{ $logoId }}-previewContainer" style="display: none;" class="{{ $previewClass ?? '' }}">
                <img id="{{ $logoId }}-imagePreview" style="height: 80px;" alt="Logo upload preview">
            </div>



    </div>
    @if (($setting->$logoVariable!='') && (Storage::disk('public')->exists(($logoPath ?? '').$snipeSettings->$logoVariable)))

    <div class="col-md-9 col-md-offset-3">
        <label id="{{ $logoId }}-deleteCheckbox" for="{{ $logoClearVariable }}" style="font-weight: normal" class="form-control">
            <input type="checkbox" name="{{ $logoClearVariable }}" value="1" @checked(old($logoClearVariable))>
            {{ trans('general.remove_current_image', ['type' => $logoLabel]) }}
        </label>
    </div>
    @endif



</div>







