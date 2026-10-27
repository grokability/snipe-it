@use('App\Helpers\Helper')

@props([
    'name' => 'purchase_cost',
    'label' => null,
    'item' => null,
    'currencyType' => null,
])

<div
    @class([
        'form-group',
        'has-error' => $errors->has($name),
    ])
>
    <label for="{{ $name }}" class="col-md-3 control-label">
        {{ $label ?? trans('general.purchase_cost') }}
    </label>
    <div class="col-md-9">
        <div class="input-group col-md-5" style="padding-left: 0">
            <input
                class="form-control"
                type="text"
                name="{{ $name }}"
                id="{{ $name }}"
                aria-label="{{ $label ?? trans('general.purchase_cost') }}"
                value="{{ old($name, Helper::formatCurrencyOutput($item->{$name} ?? null)) }}"
                maxlength="25"
                inputmode="decimal"
                pattern="[\d.,]+"
                data-msg-pattern="{{ trans('general.purchase_cost_invalid') }}"
            />
            <span class="input-group-addon" id="{{ $name }}_currency">
                {{ $currencyType ?? $snipeSettings->default_currency }}
            </span>
        </div>
        <div class="col-md-9" style="padding-left: 0">
            <x-form.error :name="$name" />
            <p class="help-block">{{ trans('general.purchase_cost_format_help', ['format' => $snipeSettings->digit_separator]) }}</p>
        </div>
    </div>
</div>
