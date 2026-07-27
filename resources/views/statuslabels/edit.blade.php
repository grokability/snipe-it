@extends('layouts/default')

{{-- Page title --}}
@section('title')
    @if ($item->id)
        {{ trans('admin/statuslabels/table.update') }}
    @else
        {{ trans('admin/statuslabels/table.create') }}
    @endif
    @parent
@stop

{{-- Page content --}}
@section('content')
    <style>
        .input-group-addon {
            width: 30px;
        }
    </style>

    <x-container class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-sm-12 col-sm-offset-0">

        <x-form :$item route="{{ ($item->id) ? route('statuslabels.update', ['statuslabel' => $item->id]) : route('statuslabels.store') }}">

            <x-box top_submit>
                @if ($item->id)
                    <x-slot:header>{{ $item->name }}</x-slot:header>
                @endif

                <x-form.row
                    :label="trans('general.name')"
                    :$item
                    name="name"
                />

                <x-form.row
                    :label="trans('admin/statuslabels/table.status_type')"
                    name="statuslabel_types"
                    input_div_class="col-md-7 required"
                >
                    <x-slot:input>
                        <x-input.select
                            name="statuslabel_types"
                            :options="$statuslabel_types"
                            :selected="$item->getStatuslabelType()"
                            style="width: 100%; min-width:400px"
                            aria-label="statuslabel_types"
                        />
                    </x-slot:input>
                </x-form.row>

                <x-form.row
                    :label="trans('admin/statuslabels/table.color')"
                    :$item
                    name="color"
                    type="colorpicker"
                    default="#f4f4f4"
                />

                <x-form.row
                    :label="trans('general.notes')"
                    :$item
                    name="notes"
                    type="textarea"
                    :rows="5"
                />

                <x-form.checkbox-row
                    name="show_in_nav"
                    :label="trans('admin/statuslabels/table.show_in_nav')"
                    :item="$item"
                />

                <x-form.checkbox-row
                    name="default_label"
                    :label="trans('admin/statuslabels/table.default_label')"
                    :item="$item"
                    :help_text="trans('admin/statuslabels/table.default_label_help')"
                />

            </x-box>

        </x-form>

    </x-container>

@stop
