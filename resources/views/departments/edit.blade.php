@extends('layouts/default')

{{-- Page title --}}
@section('title')
    @if ($item->id)
        {{ trans('admin/departments/table.update') }}
    @else
        {{ trans('admin/departments/table.create') }}
    @endif
    @parent
@stop

{{-- Page content --}}
@section('content')

    <x-container class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-sm-12 col-sm-offset-0">

        <x-form :$item route="{{ ($item->id) ? route('departments.update', ['department' => $item->id]) : route('departments.store') }}">

            <x-box top_submit>
                @if ($item->id)
                    <x-slot:header>{{ $item->name }}</x-slot:header>
                @endif

                @if (\App\Models\Company::canManageUsersCompanies())
                    <x-input.company-select
                        :label="trans('general.company')"
                        name="company_id"
                        :selected="old('company_id', $item->company_id)"
                    />
                @else
                    <input id="hidden_company_id" type="hidden" name="company_id" value="{{ Auth::user()->company_id }}">
                @endif

                <x-form.row
                    :label="trans('admin/departments/table.name')"
                    :$item
                    name="name"
                />

                <x-form.row
                    :label="trans('admin/suppliers/table.phone')"
                    :$item
                    name="phone"
                    type="tel"
                    input_icon="phone"
                    input_group_addon="left"
                />

                <x-form.row
                    :label="trans('admin/suppliers/table.fax')"
                    :$item
                    name="fax"
                    type="tel"
                    input_icon="fax"
                    input_group_addon="left"
                    :maxlength="34"
                />

                <x-input.user-select
                    :label="trans('admin/users/table.manager')"
                    name="manager_id"
                    :selected="old('manager_id', $item->manager_id)"
                />

                <x-input.location-select
                    :label="trans('general.location')"
                    name="location_id"
                    :selected="old('location_id', $item->location_id)"
                />

                <x-input.image-upload :item="$item" :imagePath="app('departments_upload_path')" />

                <x-form.row
                    :label="trans('general.notes')"
                    :$item
                    name="notes"
                    type="textarea"
                    :rows="5"
                    :placeholder="trans('general.placeholders.notes')"
                />

                <fieldset name="color-preferences">
                    <x-form.legend help_text="{{ trans('general.tag_color_help') }}">
                        {{ trans('general.tag_color') }}
                    </x-form.legend>
                    <x-form.row
                        :label="trans('general.tag_color')"
                        :$item
                        name="tag_color"
                        type="colorpicker"
                    />
                </fieldset>

            </x-box>

        </x-form>

    </x-container>

@stop
