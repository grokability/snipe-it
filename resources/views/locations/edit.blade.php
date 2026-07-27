@extends('layouts/default')

{{-- Page title --}}
@section('title')
    @if ($item->id)
        {{ trans('admin/locations/table.update') }}
    @else
        {{ trans('admin/locations/table.create') }}
    @endif
    @parent
@stop

@push('js')
    <script nonce="{{ csrf_token() }}">
        $(function () {
            $('[name="company_id"]').on('select2:select select2:clear', function (e) {
                var companyId = $(this).val() || null;
                var $parentSelect = $('#parent_id_location_select');
                $parentSelect.data('company-id', companyId);
                $parentSelect.val(null).trigger('change');
            });
        });
    </script>
@endpush

{{-- Page content --}}
@section('content')

    <x-container class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-sm-12 col-sm-offset-0">

        <x-form :$item route="{{ ($item->id) ? route('locations.update', ['location' => $item->id]) : route('locations.store') }}">

            <x-box top_submit>
                @if ($item->id)
                    <x-slot:header>{{ $item->name }}</x-slot:header>
                @endif

                <x-input.company-select
                    :label="trans('general.company')"
                    name="company_id"
                    :selected="old('company_id', $item->company_id)"
                />

                <x-form.row
                    :label="trans('admin/locations/table.name')"
                    :$item
                    name="name"
                />

                <x-input.location-select
                    :label="trans('admin/locations/table.parent')"
                    name="parent_id"
                    :selected="old('parent_id', $item->parent_id)"
                    :companyId="$item->company_id"
                    id="parent_id_location_select"
                />

                <x-input.user-select
                    :label="trans('admin/users/table.manager')"
                    name="manager_id"
                    :selected="old('manager_id', $item->manager_id)"
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

                <x-form.row
                    :label="trans('admin/locations/table.currency')"
                    :$item
                    name="currency"
                    :maxlength="3"
                    input_div_class="col-md-2"
                />

                <x-form.address :item="$item" />

                @if ($snipeSettings->ldap_enabled == 1)
                    <x-form.row
                        :label="trans('admin/locations/table.ldap_ou')"
                        :$item
                        name="ldap_ou"
                    />
                @endif

                <x-input.image-upload :item="$item" :imagePath="app('locations_upload_path')" :clonedModel="$cloned_model ?? null" />

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
