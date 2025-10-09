<?php
?>
@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.unaccepted_asset_report') }}
    @parent
@stop

@section('header_right')

    <div class="btn-toolbar" role="toolbar">
        <div class="btn-group mr-2" role="group">
            @if($showDeleted)
                <a href="{{ route('reports/unaccepted_assets') }}" class="btn btn-default" ><i class="fa fa-trash icon-white" aria-hidden="true"></i> {{ trans('general.hide_deleted') }}</a>
            @else
                <a href="{{ route('reports/unaccepted_assets', ['deleted' => 'deleted']) }}" class="btn btn-default" ><i class="fa fa-trash icon-white" aria-hidden="true"></i> {{ trans('general.show_deleted') }}</a>
            @endif
        </div>
        <div class="btn-group mr-2" role="group">
            <form method="POST" action="{{ route('reports/export/unaccepted_assets') }}" accept-charset="UTF-8" class="form-horizontal">
            {{csrf_field()}}
            <button type="submit" class="btn btn-default"><i class="fa fa-download icon-white" aria-hidden="true"></i> {{ trans('general.download_all') }}</button>
            </form>
        </div>
    </div>
@stop

{{-- Page content --}}
@section('content')

<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-body">
            <table
                data-cookie-id-table="unacceptedAssetsReport"
                data-id-table="unacceptedAssetsReport"
                data-side-pagination="client"
                data-sort-order="asc"
                data-sort-name="created_at"
                data-advanced-search="false"
                id="unacceptedAssetsReport"
                class="table table-striped snipe-table"
                data-export-options='{
                    "fileName": "maintenance-report-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                    }'>
            <thead>
              <tr role="row">
                <th class="col-sm-1" data-searchable="false" data-field="created_at"  data-sortable="true">{{ trans('general.date') }}</th>
                <th class="col-sm-1" data-sortable="true" >{{ trans('admin/companies/table.title') }}</th>
                <th class="col-sm-1" data-sortable="true" >{{ trans('general.category') }}</th>
                <th class="col-sm-1" data-sortable="true" >{{ trans('admin/hardware/form.model') }}</th>
                <th class="col-sm-1" data-sortable="true" >{{ trans('admin/hardware/form.name') }}</th>
                <th class="col-sm-1" data-sortable="true" >{{ trans('admin/hardware/table.asset_tag') }}</th>
                <th class="col-sm-1" data-sortable="true" >{{ trans('admin/hardware/table.checkoutto') }}</th>
                <th class="col-md-1"><span class="line"></span>{{ trans('table.actions') }}</th>
              </tr>
            </thead>
            <tbody>
              @if ($assetsForReport)
              @foreach ($assetsForReport as $item)
                  @if ($item['assetItem'])
                  <tr @if($item['acceptance']->trashed()) style="text-decoration: line-through" @endif>
                    <td>{{ Helper::getFormattedDateObject($item['acceptance']->created_at, 'datetime', false) }}</td>
                    <td>{{ ($item['assetItem']->company) ? $item['assetItem']->company->name : '' }}</td>
                    <td>{!! $item['assetItem']->model->category->present()->nameUrl() !!}</td>
                    <td>{!! $item['assetItem']->present()->modelUrl() !!}</td>
                    <td>{!! $item['assetItem']->present()->nameUrl() !!}</td>
                    <td>{{ $item['assetItem']->asset_tag }}</td>
                    <td @if($item['acceptance']->assignedTo === null || $item['acceptance']->assignedTo->trashed()) style="text-decoration: line-through" @endif>{!! ($item['acceptance']->assignedTo) ? $item['acceptance']->assignedTo->present()->nameUrl() : trans('admin/reports/general.deleted_user') !!}</td>
                    <td class="white-space: nowrap;">
                        <nobr>
                        @if(!$item['acceptance']->trashed())
                           <form method="post" class="white-space: nowrap;" action="{{ route('reports/unaccepted_assets_sent_reminder') }}">
                            @if (($item['acceptance']->assignedTo) && ($item['acceptance']->assignedTo->email))
                                @csrf
                               <input type="hidden" name="acceptance_id" value="{{ $item['acceptance']->id }}">
                                <button class="btn btn-sm btn-warning" data-tooltip="true" data-title="{{ trans('admin/reports/general.send_reminder') }}">
                                    <i class="fa fa-repeat" aria-hidden="true"></i>
                                </button>
                            @else
                                   <span data-tooltip="true" data-title="{{ trans('admin/reports/general.cannot_send_reminder') }}">
                                       <a class="btn btn-sm btn-warning disabled" href="#">
                                           <i class="fa fa-repeat" aria-hidden="true"></i>
                                       </a>
                                  </span>
                            @endif
                            <a href="{{ route('reports/unaccepted_assets_delete', ['acceptanceId' => $item['acceptance']->id]) }}" class="btn btn-sm btn-danger delete-asset" data-tooltip="true" data-toggle="modal" data-content="{{ trans('general.delete_confirm', ['item' =>trans('admin/reports/general.acceptance_request')]) }}" data-title="{{  trans('general.delete') }}" onClick="return false;"><i class="fa fa-trash"></i></a>
                           </form>
                        @endif

                        </nobr>
                    </td>
                  </tr>
                  @endif
              @endforeach
              @endif
            </tbody>
            <tfoot>
              <tr>
              </tr>
            </tfoot>
          </table>
      </div>
    </div>
  </div>
</div>

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')
@stop
