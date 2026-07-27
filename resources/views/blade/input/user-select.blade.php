@use('App\Models\User', 'User')

{{-- companyId scopes the ajax lookup to a specific company (via
     data-company-ids consumed by the js-data-ajax initializer in
     snipeit.js). excludeId hides a specific user from the list — used
     by the users/edit page's manager picker so a user can't select
     themselves as their own manager. wrapperId and id default to the
     legacy "assigned_user" / "assigned_user_select" values so pages
     with JS that toggles #assigned_user (asset checkout target
     picker) keep working after the migration off
     partials.forms.edit.user-select. --}}
@props([
    'label',
    'name',
    'selected' => null,
    'required' => false,
    'hideNewButton' => false,
    'companyId' => null,
    'excludeId' => null,
    'wrapperId' => 'assigned_user',
    'id' => 'assigned_user_select',
    'style' => null,
])

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
                <a
                    href="{{ route('modal.show', 'user') }}"
                    data-toggle="modal"
                    data-target="#createModal"
                    data-select="{{ $id }}"
                    class="btn btn-sm btn-theme"
                >{{ trans('button.new') }}</a>
            @endcan
        </div>
    @endunless

    @if ($snipeSettings->full_multiple_companies_support == '1')
        @cannot('superadmin')
            <div class="col-md-7 col-md-offset-3">
                <p class="help-block"><x-icon type="tip" /> {{ trans('general.fmcs_select_note') }}</p>
            </div>
        @endcannot
    @endif

    <div class="col-md-8 col-md-offset-3"><x-form.error :name="$name" /></div>
</div>
