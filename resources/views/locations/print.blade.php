<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ trans('general.assigned_to', array('name' => $location->present()->fullName())) }} </title>
    <style>
        body {
            font-family: "Arial, Helvetica", sans-serif;
        }
        table.inventory {
            border: solid #000;
            border-width: 1px 1px 1px 1px;
            width: 100%;
        }

        @page {
            size: auto;
        }
        table.inventory th, table.inventory td {
            border: solid #000;
            border-width: 0 1px 1px 0;
            padding: 3px;
            font-size: 12px;
        }

        .print-logo {
            max-height: 40px;
        }

    </style>
</head>
<body>

@if ($snipeSettings->logo_print_assets=='1')
    @if ($snipeSettings->brand == '3')

        <h3>
        @if ($snipeSettings->acceptance_pdf_logo!='')
            <img class="print-logo" src="{{ config('app.url') }}/uploads/{{ $snipeSettings->acceptance_pdf_logo }}">
        @endif
        {{ $snipeSettings->site_name }}
        </h3>
    @elseif ($snipeSettings->brand == '2')
        @if ($snipeSettings->acceptance_pdf_logo!='')
            <img class="print-logo" src="{{ config('app.url') }}/uploads/{{ $snipeSettings->acceptance_pdf_logo }}">
        @endif
    @else
      <h3>{{ $snipeSettings->site_name }}</h3>
    @endif
@endif

<h2>{{ trans('general.assigned_to', array('name' => $location->present()->fullName())) }}</h2>
    @if ($parent)
        {{ $parent->present()->fullName() }}
    @endif
<br>
@if ($company)
    <b>{{ trans('admin/companies/table.name') }}:</b> {{ $company->present()->Name() }}</b>
<br>
@endif
@if ($manager)
    <b>{{ trans('general.manager') }}</b> {{ $manager->present()->fullName() }}<br>
@endif
<b>{{ trans('general.date') }}</b>  {{ \App\Helpers\Helper::getFormattedDateObject(now(), 'datetime', false) }}<br><br>

@if ($users->count() > 0)
    @php
        $counter = 1;
    @endphp
    <table class="inventory">
        <thead>
        <tr>
            <th colspan="6">{{ trans('general.users') }}</th>
        </tr>
        </thead>
        <thead>
            <tr>
            <th style="width: 5px;"></th>
            <th style="width: 25%;">{{ trans('general.company') }}</th>
            <th style="width: 25%;">{{ trans('admin/locations/table.user_name') }}</th>
            <th style="width: 10%;">{{ trans('general.employee_number') }}</th>
	        <th style="width: 20%;">{{ trans('admin/locations/table.department') }}</th>
		    <th style="width: 20%;">{{ trans('admin/locations/table.location') }}</th>
            </tr>
        </thead>
    @foreach ($users as $user)

        <tr>
        <td>{{ $counter }}</td>
        <td>{{ (($user) && ($user->company)) ? $user->company->name : '' }}</td>
        <td>{{ ($user)  ? $user->first_name .' '. $user->last_name : '' }}</td>
        <td>{{ ($user)  ? $user->employee_num : '' }}</td>
        <td>{{ (($user) && ($user->department)) ? $user->department->name : '' }}</td>
        <td>{{ (($user) && ($user->location)) ? $user->location->name : '' }}</td>
        </tr>
            @php
                $counter++
            @endphp
    @endforeach
    </table>
@endif



@if ($assets->count() > 0)
    <br><br>
    <table class="inventory">
        <thead>
        <tr>
            <th colspan="10">{{ trans('general.assets') }}</th>
        </tr>
        </thead>
        <thead>
            <tr>
            <th style="width: 20px;"></th>
            <th style="width: 10%;">{{ trans('admin/locations/table.asset_tag') }}</th>
            <th style="width: 10%;">{{ trans('admin/locations/table.asset_name') }}</th>
            <th style="width: 10%;">{{ trans('admin/locations/table.asset_category') }}</th>
	        <th style="width: 10%;">{{ trans('admin/locations/table.asset_manufacturer') }}</th>
            <th style="width: 15%;">{{ trans('admin/locations/table.asset_model') }}</th>
            <th style="width: 15%;">{{ trans('admin/locations/table.asset_serial') }}</th>
            <th style="width: 10%;">{{ trans('admin/locations/table.asset_location') }}</th>
            <th style="width: 10%;">{{ trans('admin/locations/table.asset_checked_out') }}</th>
            <th style="width: 10%;">{{ trans('admin/locations/table.asset_expected_checkin') }}</th>
            </tr>
        </thead>
		@php
        	$counter = 1;
    	@endphp
    	
    	@foreach ($assets as $asset)
            @php
                if($snipeSettings->show_archived_in_list != 1 && $asset->assetstatus?->archived == 1){
                    continue;
                }
            @endphp
        <tr>
        <td>{{ $counter }}</td>
        <td>{{ $asset->asset_tag }}</td>
        <td>{{ $asset->name }}</td>
        <td>{{ (($asset->model) && ($asset->model->category)) ? $asset->model->category->name : '' }}</td>
        <td>{{ (($asset->model) && ($asset->model->manufacturer)) ? $asset->model->manufacturer->name : '' }}</td>
        <td>{{ ($asset->model) ? $asset->model->name : '' }}</td>
        <td>{{ $asset->serial }}</td>
        <td>{{ $asset->location->name }}</td>
        <td>{{ \App\Helpers\Helper::getFormattedDateObject( $asset->last_checkout, 'datetime', false) }}</td>
        <td>{{ \App\Helpers\Helper::getFormattedDateObject( $asset->expected_checkin, 'datetime', false) }}</td>
        </tr>
            @php
                $counter++
            @endphp
    @endforeach
    </table>
@endif

<br>
<br>
<br>
<table>
    <tr>
        <td>{{ trans('admin/locations/table.signed_by_asset_auditor') }}</td>
        <td><br>------------------------------------------------------ &nbsp;&nbsp;&nbsp;<br></td>
        <td>{{ trans('admin/locations/table.date') }}</td>
        <td><br>------------------------------ &nbsp;&nbsp;&nbsp;<br></td>
    </tr>

    <tr>
        <td>{{ trans('admin/locations/table.signed_by_finance_auditor') }}</td>
        <td><br>------------------------------------------------------ &nbsp;&nbsp;&nbsp;<br></td>
        <td>{{ trans('admin/locations/table.date') }}</td>
        <td><br>------------------------------ &nbsp;&nbsp;&nbsp;<br></td>
    </tr>

    <tr>
        <td>{{ trans('admin/locations/table.signed_by_location_manager') }}</td>
        <td><br>------------------------------------------------------ &nbsp;&nbsp;&nbsp;<br></td>
        <td>{{ trans('admin/locations/table.date') }}</td>
        <td><br>------------------------------ &nbsp;&nbsp;&nbsp;<br></td>
    </tr>
</table>


</body>
</html>
