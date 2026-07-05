@extends('layouts/default')

{{-- Page title --}}
@section('title')
    @if ($item->id)
        {{ trans('admin/companies/table.update') }}
    @else
        {{ trans('admin/companies/table.create') }}
    @endif
    @parent
@stop

{{-- Page content --}}
@section('content')

<x-container class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">

    <x-form :$item route="{{ isset($item->id) ? route('companies.update', ['company' => $item->id]) : route('companies.store') }}">

        <x-box>

            <x-form.row
                :label="trans('admin/companies/table.name')"
                :$item
                name="name"
            />

            @include('partials.forms.edit.company-select', [
                'translated_name' => trans('admin/companies/table.parent'),
                'fieldname' => 'parent_id',
                'only_top_level' => true,
                'exclude_id' => $item->id ?? null,
            ])

            <x-form.row
                :label="trans('admin/suppliers/table.phone')"
                :$item
                name="phone"
            />

            <x-form.row
                :label="trans('admin/suppliers/table.fax')"
                :$item
                name="fax"
            />

            <x-form.row
                :label="trans('admin/suppliers/table.email')"
                :$item
                name="email"
                type="email"
            />

            <x-form.row
                :label="trans('general.notes')"
                :$item
                name="notes"
                type="textarea"
                :placeholder="trans('general.placeholders.notes')"
            />

            @include ('partials.forms.edit.image-upload', ['image_path' => app('companies_upload_path')])

            <fieldset name="color-preferences">
                <x-form.legend help_text="{{ trans('general.tag_color_help') }}">
                    {{ trans('general.tag_color') }}
                </x-form.legend>

                <div class="form-group {{ $errors->has('tag_color') ? 'has-error' : '' }}">
                    <label for="tag_color" class="col-md-3 control-label">{{ trans('general.tag_color') }}</label>
                    <div class="col-md-9">
                        <x-input.colorpicker :item="$item" id="tag_color" :value="old('tag_color', ($item->tag_color ?? '#f4f4f4'))" name="tag_color" />
                        {!! $errors->first('tag_color', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                    </div>
                </div>
            </fieldset>

        </x-box>

    </x-form>

</x-container>

@stop
