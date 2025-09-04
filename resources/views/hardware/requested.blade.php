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

    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">

        <div class="table-responsive">
            <table
                    name="requestedAssets"
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

                        @if ($request->requestable)
                    <tr>
                            {{ csrf_field() }}
                            <td>
                                @if (($request->itemType() == "asset") && ($request->requestable))
                                    <a href="{{ $request->requestable->getImageUrl() }}" data-toggle="lightbox" data-type="image"><img src="{{ $request->requestable->getImageUrl() }}" style="max-height: {{ $snipeSettings->thumbnail_max_h }}px; width: auto;" class="img-responsive" alt="{{ $request->requestable->name }}"></a>
                                @elseif (($request->itemType() == "asset_model") && ($request->requestable))
                                        <a href="{{ config('app.url') }}/uploads/models/{{ $request->requestable->image }}" data-toggle="lightbox" data-type="image"><img src="{{ config('app.url') }}/uploads/models/{{ $request->requestable->image }}" style="max-height: {{ $snipeSettings->thumbnail_max_h }}px; width: auto;" class="img-responsive" alt="{{ $request->requestable->name }}"></a>
                                @endif


                            </td>
                            <td>

                            @if ($request->itemType() == "asset")
                            <a href="{{ config('app.url') }}/hardware/{{ $request->requestable->id }}">
                                {{ $request->name() }}
                            </a>
                            @elseif ($request->itemType() == "asset_model")
                                <a href="{{ config('app.url') }}/models/{{ $request->requestable->id }}">
                                    {{ $request->name() }}
                                </a>
                             @endif

                            </td>
                            @if ($request->location())
                            <td>{{ $request->location()->name }}</td>
                            @else
                            <td></td>
                            @endif

                            <td>
                            @if ($request->itemType() == "asset")
                                {{ App\Helpers\Helper::getFormattedDateObject($request->requestable->expected_checkin, 'datetime', false) }}
                            @endif
                            </td>
                            <td>
                                @if ($request->requestingUser() && !$request->requestingUser()->trashed())
                                <a href="{{ config('app.url') }}/users/{{ $request->requestingUser()->id }}">
                                    {{ $request->requestingUser()->display_name }}
                                </a>
                               @else
                                    (deleted user)
                                @endif
                            </td>
                            <td>{{ App\Helpers\Helper::getFormattedDateObject($request->created_at, 'datetime', false) }}</td>
                            <td>
                                <form
                                    method="POST"
                                    action="{{ route('account/request-item', [
                                        $request->itemType(),
                                        $request->requestable->id,
                                         true,
                                         $request->requestingUser()->id
                                    ]) }}"
                                    accept-charset="UTF-8"
                                >
                                    @csrf
                                    <button class="btn btn-warning btn-sm" data-tooltip="true" title="{{ trans('general.cancel_request') }}">{{ trans('button.cancel') }}</button>
                                </form>
                            </td>
                            <td>
                                @if ($request->itemType() == "asset")
                                    @if ($request->requestable->assigned_to=='')
                                        <a href="{{ config('app.url') }}/hardware/{{ $request->requestable->id }}/checkout" class="btn btn-sm bg-maroon" data-tooltip="true" title="{{ trans('general.checkout_user_tooltip') }}">{{ trans('general.checkout') }}</a>
                                        @else
                                        <a href="{{ config('app.url') }}/hardware/{{ $request->requestable->id }}/checkin" class="btn btn-sm bg-purple" data-tooltip="true" title="{{ trans('general.checkin_toolip') }}">{{ trans('general.checkin') }}</a>
                                    @endif

                                @endif
                            </td>


                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- .col-md-12> -->
</div> <!-- .row -->
@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', [
        'exportFile' => 'requested-export',
        'search' => true,
        'clientSearch' => true,
    ])

@stop
