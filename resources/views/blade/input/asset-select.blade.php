@use('App\Models\Asset', 'Asset')
@use('Illuminate\Support\Arr', 'Arr')

@props([
    'label',
    'name',
    'selected' => null,
    'required' => false,
    'multiple' => false,
    'assetStatusType' => null,
    'companyId' => null,
    'excludeId' => null,
])

<div
    @class([
        'form-group',
        'has-error' => $errors->has($name),
    ])
>
    <label for="{{ $name }}_select" class="col-md-3 control-label">{{ $label }}</label>
    <div class="col-md-7">
        <select
            class="js-data-ajax select2"
            data-endpoint="hardware"
            data-placeholder="{{ trans('general.select_asset') }}"
            name="{{ $name }}{{ $multiple ? '[]' : '' }}"
            id="{{ $name }}_select"
            style="width: 100%"
            aria-label="{{ $label }}"
            @required($required)
            @if ($multiple) multiple @endif
            @if ($assetStatusType) data-asset-status-type="{{ $assetStatusType }}" @endif
            @if ($companyId) data-company-id="{{ $companyId }}" @endif
            @if ($excludeId) data-exclude-id="{{ $excludeId }}" @endif
        >
            @unless ($multiple)
                <option value="" role="option">{{ trans('general.select_asset') }}</option>
            @endunless
            @if ($selected)
                @foreach (Arr::wrap($selected) as $value)
                    @php($asset = Asset::with('model')->find($value))
                    @if ($asset)
                        <option value="{{ $asset->id }}" selected="selected" role="option" aria-selected="true">
                            {{ $asset->present()->fullName }}
                        </option>
                    @endif
                @endforeach
            @endif
        </select>
    </div>

    @if ($snipeSettings->full_multiple_companies_support == '1')
        @cannot('superuser')
            <div class="col-md-7 col-md-offset-3">
                <p class="help-block"><x-icon type="tip" /> {{ trans('general.fmcs_select_note') }}</p>
            </div>
        @endcannot
    @endif

    <div class="col-md-8 col-md-offset-3"><x-form.error :name="$name" /></div>
</div>
