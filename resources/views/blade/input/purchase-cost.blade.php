@use('App\Helpers\Helper')

@props([
    'label' => null,
    'item' => null,
    'currencyType' => null,
])

<div
    @class([
        'form-group',
        'has-error' => $errors->has('purchase_cost'),
    ])
>
    <label for="purchase_cost" class="col-md-3 control-label">
        {{ $label ?? trans('general.purchase_cost') }}
    </label>
    <div class="col-md-9">
        <div class="input-group col-md-5" style="padding-left: 0">
            <input
                class="form-control"
                type="text"
                name="purchase_cost"
                id="purchase_cost"
                aria-label="{{ $label ?? trans('general.purchase_cost') }}"
                value="{{ old('purchase_cost', Helper::formatCurrencyOutput($item->purchase_cost)) }}"
                maxlength="25"
                inputmode="decimal"
                pattern="[\d.,]+"
                data-msg-pattern="{{ trans('general.purchase_cost_invalid') }}"
            />
            <span class="input-group-addon">
                {{ $currencyType ?? $snipeSettings->default_currency }}
            </span>
        </div>
        <div class="col-md-9" style="padding-left: 0">
            {!! $errors->first('purchase_cost', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
            <p class="help-block">{{ trans('general.purchase_cost_format_help', ['format' => $snipeSettings->digit_separator]) }}</p>
        </div>
    </div>
</div>
