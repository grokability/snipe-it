@extends('layouts/default')

@section('title0')

  @php
    $requestStatus = request()->input('status');
    $requestOrderNumber = request()->input('order_number');
    $requestCompanyId = request()->input('company_id');
    $requestStatusId = request()->input('status_id');
  @endphp

  @if (($requestCompanyId) && ($company))
    {{ $company->name }}
  @endif



@if ($requestStatus)
  @if ($requestStatus=='Pending')
    {{ trans('general.pending') }}
  @elseif ($requestStatus=='RTD')
    {{ trans('general.ready_to_deploy') }}
  @elseif ($requestStatus=='Deployed')
    {{ trans('general.deployed') }}
  @elseif ($requestStatus=='Undeployable')
    {{ trans('general.undeployable') }}
  @elseif ($requestStatus=='Deployable')
    {{ trans('general.deployed') }}
  @elseif ($requestStatus=='Requestable')
    {{ trans('admin/hardware/general.requestable') }}
  @elseif ($requestStatus=='Archived')
    {{ trans('general.archived') }}
  @elseif ($requestStatus=='Deleted')
    {{ ucfirst(trans('general.deleted')) }}
  @elseif ($requestStatus=='byod')
    {{ strtoupper(trans('general.byod')) }}
  @endif
@else
{{ trans('general.all') }}
@endif
{{ trans('general.assets') }}

  @if ($requestOrderNumber)
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
                    array('status' => e($requestStatus),
                    'order_number'=>e(strval($requestOrderNumber)),
                    'company_id'=>e($requestCompanyId),
                    'status_id'=>e($requestStatusId)))" />
        </x-box>
    </x-container>
@stop

@section('moar_scripts')
@include('partials.bootstrap-table')

@stop
