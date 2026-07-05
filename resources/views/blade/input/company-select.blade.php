@use('App\Models\Company', 'Company')
@use('Illuminate\Support\Arr', 'Arr')

@props([
    'label',
    'name',
    'selected' => null,
    'required' => false,
    'multiple' => false,
    'hideNewButton' => false,
])

<div
    @class([
        'form-group',
        'has-error' => $errors->has($name),
    ])
>
    <label for="{{ $name }}" class="col-md-3 control-label">{{ $label }}</label>
    <div class="col-md-6">
        <select
            class="js-data-ajax"
            data-endpoint="companies"
            data-placeholder="{{ trans('general.select_company') }}"
            name="{{ $name }}{{ $multiple ? '[]' : '' }}"
            id="{{ $name }}"
            style="width: 100%"
            aria-label="{{ $label }}"
            @required($required)
            @if ($multiple) multiple @endif
        >
            <option value=""></option>
            @if ($selected)
                @foreach(Arr::wrap($selected) as $value)
                    <option value="{{ $value }}" selected="selected" role="option" aria-selected="true">
                        {{ Company::find($value)?->name }}
                    </option>
                @endforeach
            @endif
        </select>
    </div>

    @unless($hideNewButton)
        <div class="col-md-1 col-sm-1 text-left">
            @can('create', Company::class)
                <a href="{{ route('modal.show', 'company') }}" data-toggle="modal" data-target="#createModal" data-select="{{ $name }}" class="btn btn-sm btn-theme">{{ trans('button.new') }}</a>
            @endcan
        </div>
    @endunless

    @if ($snipeSettings->full_multiple_companies_support == '1')
        @cannot('superadmin')
            <div class="col-md-6 col-md-offset-3">
                <p class="help-block"><x-icon type="tip" /> {{ trans('general.fmcs_company_select_note') }}</p>
            </div>
        @endcannot
        @can('superadmin')
            <div class="col-md-6 col-md-offset-3">
                <p class="help-block"><x-icon type="tip" /> {{ trans('general.fmcs_company_select_superadmin_note') }}</p>
            </div>
        @endcan
    @endif

    {!! $errors->first($name, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>') !!}
</div>
