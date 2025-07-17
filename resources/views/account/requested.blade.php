@extends('layouts/default')

{{-- Page title --}}
@section('title')
   {{ trans('general.requested_assets')}}
@stop

{{-- Account page content --}}
@section('content')

    <div class="row">
        <div class="col-md-12">

            <div class="box box-default">
                <div class="box-body">

                    <table

                            data-cookie-id-table="userRequests"
                            data-id-table="userRequests"
                            data-side-pagination="server"
                            data-sort-order="desc"
                            id="userRequests"
                            class="table table-striped snipe-table"
                            data-url="{{ route('api.assets.requested') }}"
                            data-export-options='{
                  "fileName": "my-requested-assets-{{ date('Y-m-d') }}",
                  "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
                        <thead>
                        <tr>
                            <th data-field="image" data-formatter="imageFormatter">{{ trans('general.image') }}</th>
                            <th data-field="name">{{ trans('general.item_name') }}</th>
                            <th data-field="type">{{ trans('general.type') }}</th>
                            <th data-field="qty">{{ trans('general.qty') }}</th>
                            <th data-field="location">{{ trans('admin/hardware/table.location') }}</th>
                            <th data-field="expected_checkin" data-formatter="dateDisplayFormatter"> {{ trans('admin/hardware/form.expected_checkin') }}</th>
                            <th data-field="request_date" data-formatter="dateDisplayFormatter"> {{ trans('general.requested_date') }}</th>

                            @foreach(\App\Models\CustomField::get() as $field)
                                @if (($field->field_encrypted=='0') && ($field->show_in_requestable_list=='1'))
                                    <th data-field="custom_fields.{{ $field->db_column }}">{{ $field->name }}</th>
                                @endif
                            @endforeach
                        </tr>
                        </thead>
                    </table>

                </div> <!-- .box-body -->
            </div> <!-- .box-default -->
        </div> <!-- .col-md-9 -->
    </div> <!-- .row-->

@stop
@section('moar_scripts')
    @include ('partials.bootstrap-table')
@stop
