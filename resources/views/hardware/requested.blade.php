@extends('layouts/default')

@section('title0')
  {{ trans('admin/hardware/general.requested') }}
  {{ trans('general.assets') }}
@stop

{{-- Page title --}}
@section('title')
    @yield('title0')  @parent
@stop

{{-- Page content --}}
@section('content')

<div class="row"><!-- .row -->
    <div class="col-md-12"><!-- .col-md-12 -->
        <div class="box box-default"><!-- .box -->
            <div class="box-body"><!-- .bow-body -->
                <div class="row"><!-- .row -->
                    <div class="col-md-12"><!-- col-md-12 -->

                        <table
                            data-toolbar="#toolbar"
                            class="table table-striped snipe-table"
                            id="requestedAssets"
                            data-id-table="requestedAssets"
                            data-cookie-id-table="requestedAssets"
                            data-export-options='{
                            "fileName": "export-assetrequests-{{ date('Y-m-d') }}",
                            "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                        }'>
                            <thead>
                                <tr role="row">
                                    <th class="col-md-1">{{ trans('general.image') }}</th>
                                    <th class="col-md-2">{{ trans('general.name') }}</th>
                                    <th class="col-md-2" data-sortable="true">{{ trans('admin/hardware/table.location') }}</th>
                                    <th class="col-md-2" data-sortable="true">{{ trans('admin/hardware/form.expected_checkin') }}</th>
                                    <th class="col-md-3" data-sortable="true">{{ trans('admin/hardware/table.requesting_user') }}</th>
                                    <th class="col-md-2">{{ trans('admin/hardware/table.requested_date') }}</th>
                                    <th class="col-md-1">{{ trans('button.actions') }}</th>
                                    <th class="col-md-1">{{ trans('general.checkout') }}</th>
                                </tr>
                            </thead>
                            <tbody>
				@foreach ($requestedItems as $request)
				@if (!$request->requestable)
				@continue
				@endif

				@php
				$me = auth()->user();
				$reqUser = $request->requestingUser();

				if ($reqUser && $me && (int)$reqUser->id === (int)$me->id) {
				    $skip = true;
				} else {
				    $skip = false;
				}

				$asset = $request->requestable;

				$assignedType = $asset->assigned_to_type ?? ($asset->assigned_type ?? null);
				$assignedId   = (int)($asset->assigned_to ?? 0);

				$myUserId     = (int)($me->id ?? 0);
				$myLocationId = (int)($me->location_id ?? 0);

				$isHoldingAsUser = ($assignedType === \App\Models\User::class) && ($assignedId === $myUserId);

				$isHoldingAsMyLocation =
				    $myLocationId &&
				    ($assignedType === \App\Models\Location::class) &&
				    ($assignedId === $myLocationId);

				$isPrivileged = $me && $me->groups()
				->whereIn('name', ['IT GMI','Manager Archive','Warehouse','Archivist','Archivists','Admin'])
				->exists();

				$reqLocName  = $request->location() ? $request->location()->name : '';
				$isInArchive = trim(strtolower($reqLocName)) === 'archive';

				$canDoCheckout =
				$isHoldingAsUser ||
				$isHoldingAsMyLocation ||
				($isInArchive && $isPrivileged);

				if (!$canDoCheckout) {
				$skip = true;
				}

				$inTransitId = \App\Models\Statuslabel::where('name', 'In Transit')->value('id');
				@endphp

				@if ($skip)
				@continue
				@endif
                        </table>

                    </div> <!-- /.col-md-12 -->
                </div> <!-- /.row -->
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div> <!-- .col-md-12 -->
</div> <!-- .row -->
@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', [
        'exportFile' => 'requested-export',
        'search' => true,
        'clientSearch' => true,
    ])

@stop
