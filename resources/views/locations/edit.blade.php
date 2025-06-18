@extends('layouts/edit-form', [
    'createText' => trans('admin/locations/table.create') ,
    'updateText' => trans('admin/locations/table.update'),
    'topSubmit' => true,
    'helpPosition' => 'right',
    'helpText' => trans('admin/locations/table.about_locations'),
    'formAction' => (isset($item->id)) ? route('locations.update', ['location' => $item->id]) : route('locations.store'),
])

{{-- Page content --}}
@section('inputFields')
@include ('partials.forms.edit.name', ['translated_name' => trans('admin/locations/table.name')])

<!-- parent -->
@include ('partials.forms.edit.location-select', ['translated_name' => trans('admin/locations/table.parent'), 'fieldname' => 'parent_id'])

<!-- Manager-->
@include ('partials.forms.edit.user-select', ['translated_name' => trans('admin/users/table.manager'), 'fieldname' => 'manager_id'])

<!-- Company -->
@include ('partials.forms.edit.company-select', ['translated_name' => trans('general.company'), 'fieldname' => 'company_id'])

@include ('partials.forms.edit.phone')
@include ('partials.forms.edit.fax')

<!-- Currency -->
<div class="form-group {{ $errors->has('currency') ? ' has-error' : '' }}">
    <label for="currency" class="col-md-3 control-label">
        {{ trans('admin/locations/table.currency') }}
    </label>
    <div class="col-md-7">
        <input class="form-control" style="width:100px" type="text" name="currency" aria-label="currency" id="currency" value="{{ old('currency', $item->currency) }}"{!!  (Helper::checkIfRequired($item, 'currency')) ? ' required' : '' !!} maxlength="3" />
        @error('currency')
        <span class="alert-msg">
            <x-icon type="x" />
            {{ $message }}
        </span>
        @enderror

    </div>
</div>

@include ('partials.forms.edit.address')

<!-- LDAP Search OU -->
@if ($snipeSettings->ldap_enabled == 1)
    <div class="form-group {{ $errors->has('ldap_ou') ? ' has-error' : '' }}">
        <label for="ldap_ou" class="col-md-3 control-label">
            {{ trans('admin/locations/table.ldap_ou') }}
        </label>
        <div class="col-md-7">
            <input class="form-control" type="text" name="ldap_ou" aria-label="ldap_ou" id="ldap_ou" value="{{ old('ldap_ou', $item->ldap_ou) }}"{!!  (Helper::checkIfRequired($item, 'ldap_ou')) ? ' required' : '' !!} maxlength="191" />
            @error('ldap_ou')
            <span class="alert-msg">
                <x-icon type="x" />
                {{ $message }}
        </span>
            @enderror
        </div>
    </div>
@endif


@include ('partials.forms.edit.image-upload', ['image_path' => app('locations_upload_path')])

<div class="form-group{!! $errors->has('notes') ? ' has-error' : '' !!}">
    <label for="notes" class="col-md-3 control-label">{{ trans('general.notes') }}</label>
    <div class="col-md-8">
        <x-input.textarea
                name="notes"
                id="notes"
                :value="old('notes', $item->notes)"
                placeholder="{{ trans('general.placeholders.notes') }}"
                aria-label="notes"
                rows="5"
        />
        {!! $errors->first('notes', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>
</div>

@stop

