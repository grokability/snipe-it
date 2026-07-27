@use('App\Models\Company', 'Company')
@use('Illuminate\Support\Arr', 'Arr')

{{-- onlyTopLevel / excludeId power the parent-company picker: the first
     disables sub-companies (they can't themselves become parents), the
     second hides the company being edited (so it can't be selected as its
     own parent). Both are read as data-* attributes by the js-data-ajax
     initializer in snipeit.js and forwarded to the
     /api/v1/companies/selectlist endpoint. --}}
@props([
    'label',
    'name',
    'selected' => null,
    'required' => false,
    'multiple' => false,
    'hideNewButton' => false,
    'onlyTopLevel' => false,
    'excludeId' => null,
])

<div
    @class([
        'form-group',
        'has-error' => $errors->has($name),
    ])
>
    <label for="{{ $name }}" class="col-md-3 control-label">{{ $label }}</label>
    <div class="col-md-7">
        <select
            class="js-data-ajax"
            data-endpoint="companies"
            data-placeholder="{{ trans('general.select_company') }}"
            @if ($onlyTopLevel) data-only-top-level="true" @endif
            @if ($excludeId) data-exclude-id="{{ $excludeId }}" @endif
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
            <div class="col-md-7 col-md-offset-3">
                <p class="help-block"><x-icon type="tip" /> {{ trans('general.fmcs_company_select_note') }}</p>
            </div>
        @endcannot
        @can('superadmin')
            <div class="col-md-7 col-md-offset-3">
                <p class="help-block"><x-icon type="tip" /> {{ trans('general.fmcs_company_select_superadmin_note') }}</p>
            </div>
        @endcan
    @endif

    <div class="col-md-8 col-md-offset-3"><x-form.error :name="$name" /></div>
</div>
