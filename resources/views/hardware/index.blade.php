@extends('layouts/default')

@section('title0')

  @php
      $requestStatusType = request()->input('status_type');
      $requestOrderNumber = request()->input('order_number');
      $requestCompanyId = request()->input('company_id');
      $requestStatusTypeId = request()->input('status_id');
  @endphp

  @if (is_scalar($requestCompanyId) && ($company instanceof \App\Models\Company))
    {{ $company->name }}
  @endif



  @if ($requestStatusType)
      @if ($requestStatusType=='Pending')
    {{ trans('general.pending') }}
      @elseif ($requestStatusType=='RTD')
    {{ trans('general.ready_to_deploy') }}
      @elseif ($requestStatusType=='Deployed')
    {{ trans('general.deployed') }}
      @elseif ($requestStatusType=='Undeployable')
    {{ trans('general.undeployable') }}
      @elseif ($requestStatusType=='Deployable')
    {{ trans('general.deployed') }}
      @elseif ($requestStatusType=='Requestable')
    {{ trans('admin/hardware/general.requestable') }}
      @elseif ($requestStatusType=='Archived')
    {{ trans('general.archived') }}
      @elseif ($requestStatusType=='Deleted')
    {{ ucfirst(trans('general.deleted')) }}
      @elseif ($requestStatusType=='byod')
    {{ strtoupper(trans('general.byod')) }}
  @endif
@else
{{ trans('general.all') }}
@endif
{{ trans('general.assets') }}

  @if (Request::has('order_number') && is_scalar($requestOrderNumber))
    : Order #{{ strval($requestOrderNumber) }}
  @endif
@stop

{{-- Page title --}}
@section('title')
@yield('title0')  @parent
@stop


{{-- Page content --}}
@section('content')
    <x-container>
        <x-box name="assets">
            <x-table.assets :route="route('api.assets.index',
                array(
                    'status_type' => is_scalar($requestStatusType) ? $requestStatusType : null,
                    'order_number' => is_scalar($requestOrderNumber) ? strval($requestOrderNumber) : null,
                    'company_id' => is_scalar($requestCompanyId) ? $requestCompanyId : null,
                    'status_id' => is_scalar($requestStatusTypeId) ? $requestStatusTypeId : null,
                ))"/>
        </x-box>
    </x-container>
@stop

@section('moar_scripts')
@include('partials.bootstrap-table')

@stop
