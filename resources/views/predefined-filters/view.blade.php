@extends('layouts/default')

@section('title')
    {{ trans('admin/predefinedFilters/table.title') }}
    @parent
@stop

@section('content')
<div class="row">
  <div class="col-md-9">
    <div class="box box-default">
      <div class="box-header with-border">
        <h3 class="box-title">{{ trans('general.details') }}</h3>
      </div>
      <div class="box-body no-padding">
        <table class="table table-striped snipe-table">
          <tbody>
            <tr>
              <th>{{ trans('general.name') }}</th>
              <td>{{ $filter->name }}</td>
            </tr>
            <tr>
              <th>{{ trans('admin/predefinedFilters/general.is_public') }}</th>
              <td>{{ $filter->is_public ? trans('general.yes') : trans('general.no') }}</td>
            </tr>
            <tr>
              <th>{{ trans('object_type') }}</th>
              <td>{{ $filter->object_type }}</td>
            </tr>
            <tr>
              <th>{{ trans('general.created_by') }}</th>
              <td>{{ $filter->createdBy->full_name ?? 'N/A' }}</td>
            </tr>
            <tr>
              <th>{{ trans('general.created_at') }}</th>
              <td>{{ $filter->created_at ? $filter->created_at->format('Y-m-d H:i') : '' }}</td>
            </tr>
            <tr>
              <th>{{ trans('general.updated_at') }}</th>
              <td>{{ $filter->updated_at ? $filter->updated_at->format('Y-m-d H:i') : '' }}</td>
            </tr>
            @if ($filter->filter_data)
            <tr>
              <th>{{ trans('filter_data') }}</th>
              <td>
                @php
                    $decodedFilterData = is_string($filter->filter_data)
                        ? json_decode($filter->filter_data, true)
                        : $filter->filter_data;
                @endphp

                <ul style="list-style:none; padding-left:0; margin:0;">
                  @foreach ($decodedFilterData as $key => $value)
                      @if (is_array($value))
                          <li><strong>{{ $key }}:</strong>
                              <ul style="list-style:disc; margin-left:20px;">
                                  @foreach ($value as $nestedKey => $nestedValue)
                                      <li><strong>{{ $nestedKey }}:</strong> {{ $nestedValue }}</li>
                                  @endforeach
                              </ul>
                          </li>
                      @else
                          <li><strong>{{ $key }}:</strong> {{ $value }}</li>
                      @endif
                  @endforeach
                </ul>
              </td>
            </tr>
            @endif
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    @if ($filter->userHasPermission($user, 'delete'))
      <div class="col-md-12 hidden-print" style="padding-top: 5px;">
        <button class="btn btn-sm btn-block btn-danger btn-social delete-asset" data-icon="fa fa-trash" data-toggle="modal" data-title="{{ trans('general.delete') }}" data-content="{{ trans('general.sure_to_delete_var', ['item' => $filter->name]) }}" data-target="#dataConfirmModal" onClick="return false;">
          <x-icon type="delete" />
            {{ trans('general.delete') }}
        </button>
      @else
        <a href="#" class="btn btn-block btn-sm btn-danger btn-social hidden-print disabled" data-tooltip="true"  data-placement="top" data-title="{{ trans('general.cannot_be_deleted') }}" onClick="return false;">
          <x-icon type="delete" />
            {{ trans('general.delete') }}
        </a>
      @endif
    </div>
  </div>
@stop
