@use('App\Models\User', 'User')

{{-- companyId scopes the ajax lookup to a specific company (via
     data-company-ids consumed by the js-data-ajax initializer in
     snipeit.js). excludeId hides a specific user from the list. Used
     by the users/edit page's manager picker so a user can't select
     themselves as their own manager. wrapperId and id default to
     values derived from the name prop, so a manager picker with
     name="manager_id" gets id="manager_id_select" and
     wrapperId="manager_id" automatically.  --}}
{{-- Auto-hides the "New" button when rendered inside an AJAX modal.
     See x-input.company-select for the pattern. --}}
@aware(['submitToSelect2' => false])

@props([
    'label',
    'name',
    'selected' => null,
    'required' => false,
    'hideNewButton' => false,
    'companyId' => null,
    'excludeId' => null,
    'wrapperId' => null,
    'id' => null,
    'style' => null,
])

@php
    $wrapperId = $wrapperId ?? $name;
    $id = $id ?? ($name.'_select');
    $hideNewButton = $hideNewButton || $submitToSelect2;
@endphp

<div
    id="{{ $wrapperId }}"
    @class([
        'form-group',
        'has-error' => $errors->has($name),
    ])
    @if ($style) style="{{ $style }}" @endif
>
    <label for="{{ $id }}" class="col-md-3 control-label">{{ $label }}</label>

    <div class="col-md-7">
        <select
            class="js-data-ajax"
            data-endpoint="users"
            data-placeholder="{{ trans('general.select_user') }}"
            @if ($companyId) data-company-ids="{{ $companyId }}" @endif
            @if ($excludeId) data-exclude-id="{{ $excludeId }}" @endif
            name="{{ $name }}"
            id="{{ $id }}"
            style="width: 100%"
            aria-label="{{ $name }}"
            @required($required)
        >
            @if ($selected)
                <option value="{{ $selected }}" selected="selected" role="option" aria-selected="true">
                    {{ User::find($selected)?->present()->fullName }}
                </option>
            @else
                <option value="" role="option">{{ trans('general.select_user') }}</option>
            @endif
        </select>
    </div>

    @unless ($hideNewButton)
        <div class="col-md-1 col-sm-1 text-left">
            @can('create', User::class)
                <x-button.modal-add type="user" :target="$id"/>
            @endcan
        </div>
    @endunless

    @if ($snipeSettings->full_multiple_companies_support == '1')
        @cannot('superuser')
            <div class="col-md-7 col-md-offset-3">
                <p class="help-block"><x-icon type="tip" /> {{ trans('general.fmcs_select_note') }}</p>
            </div>
        @endcannot
    @endif

    <div class="col-md-8 col-md-offset-3"><x-form.error :name="$name" /></div>
</div>
