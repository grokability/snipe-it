@extends('layouts/edit-form', [
    'createText' => trans('admin/departments/table.create') ,
    'updateText' => trans('admin/departments/table.update'),
    'formAction' => (isset($item->id)) ? route('departments.update', ['department' => $item->id]) : route('departments.store'),
])

{{-- Page content --}}
@section('inputFields')

    @include ('partials.forms.edit.name', ['translated_name' => trans('admin/departments/table.name')])

    <!-- Company -->
    @if (\App\Models\Company::canManageUsersCompanies())
        @include ('partials.forms.edit.company-select', ['translated_name' => trans('general.company'), 'fieldname' => 'company_id'])
    @else
        <input id="hidden_company_id" type="hidden" name="company_id" value="{{ Auth::user()->company_id }}">
    @endif

    @include ('partials.forms.edit.phone')
    @include ('partials.forms.edit.fax')

    <!-- Manager -->
    @include ('partials.forms.edit.user-select', ['translated_name' => trans('admin/users/table.manager'), 'fieldname' => 'manager_id'])

    <!-- Location -->
    @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.location'), 'fieldname' => 'location_id'])
    @include ('partials.forms.edit.image-upload', ['image_path' => app('departments_upload_path')])


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

    <fieldset name="color-preferences">
        <x-form.legend help_text="{{ trans('general.tag_color_help') }}">
            {{ trans('general.tag_color') }}
        </x-form.legend>
        <!--  color -->
        <div class="form-group {{ $errors->has('tag_color') ? 'error' : '' }}">
            <label for="tag_color" class="col-md-3 control-label">
                {{ trans('general.tag_color') }}
            </label>
            <div class="col-md-9">
                <x-input.colorpicker :item="$item" id="color" :value="old('color', ($item->color ?? '#f4f4f4'))" name="tag_color" id="tag_color" />
                {!! $errors->first('tag_color', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
            </div>
        </div>
    </fieldset>


@stop

