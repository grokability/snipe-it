@extends('layouts/default')

{{-- Web site Title --}}
@section('title')
{{ trans('admin/groups/titles.group_management') }}
@parent
@stop

@section('header_right')
<a href="{{ route('groups.create') }}" class="btn btn-primary text-right"> {{ trans('general.create') }}</a>
<a href="{{ route('settings.index') }}" class="btn btn-default text-right">{{ trans('general.back') }}</a>
@stop


{{-- Content --}}
@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-body">
        <div class="table-responsive">

            <table
                data-cookie-id-table="groupsTable"
                data-side-pagination="server"
                data-sort-order="asc"
                data-sort-name="name"
                id="groupsTable"
                data-buttons="groupButtons"
                class="table table-striped snipe-table"
                data-url="{{ route('api.groups.index') }}"
                data-export-options='{
        "fileName": "export-groups-{{ date('Y-m-d') }}",
            "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
            }'>

            <thead>
              <tr>
               <th data-switchable="true" data-sortable="true" data-field="id" data-visible="false">{{ trans('general.id') }}</th>
               <th data-switchable="true" data-sortable="true" data-field="name" data-formatter="groupsAdminLinkFormatter" data-visible="true">{{ trans('admin/groups/table.name') }}</th>
                  <th data-switchable="true" data-sortable="true" data-field="users_count" data-visible="true"><x-icon type="user" /><span class="sr-only">{{ trans('admin/groups/table.users') }}</span></th>
                  <th data-switchable="true" data-sortable="true" data-field="notes" data-visible="true">{{ trans('general.notes') }}</th>
                  <th data-switchable="true" data-sortable="true" data-field="created_at" data-visible="true" data-formatter="dateDisplayFormatter">{{ trans('general.created_at') }}</th>
               <th data-switchable="false" data-searchable="false" data-sortable="true" data-field="created_by"  data-formatter="usersLinkObjFormatter">{{ trans('general.created_by') }}</th>
               <th data-switchable="false" data-searchable="false" data-sortable="false" data-field="actions"   data-formatter="groupsActionsFormatter">{{ trans('table.actions') }}</th>

              </tr>
            </thead>
          </table>
        </div>
      </div> <!--.box-body-->
    </div> <!-- /.box.box-default-->
  </div> <!-- .col-md-12-->
</div>
@stop
@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'groups-export', 'search' => true])
@stop
