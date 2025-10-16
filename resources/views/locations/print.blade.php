<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ trans('general.assigned_to', array('name' => $location->display_name)) }} </title>
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

<h2>
    @if ($assigned)
        {{ trans('general.assigned_to', array('name' => $location->display_name)) }}
    @else
        {{ trans('admin/locations/table.print_inventory') }} : {{ $location->display_name }}
    @endif
    </h2>
    @if ($location->parent)
    <strong>{{ trans('admin/locations/table.parent') }}:</strong> {{ $location->parent->display_name }}
@endif
<br>
@if ($location->company)
<b>{{ trans('admin/companies/table.name') }}:</b> {{ $location->company->display_name }}
<br>
@endif
@if ($location->manager)
<b>{{ trans('admin/users/table.manager') }}:</b> {{ $location->manager->display_name }}<br>
@endif
<b>{{ trans('general.date') }}:</b>  {{ \App\Helpers\Helper::getFormattedDateObject(now(), 'datetime', false) }}<br><br>

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

@if ($children->count() > 0)
    <br><br>
    <table class="inventory">
        <thead>
        <tr>
            <th colspan="10">{{ trans('general.child_locations') }}</th>
        </tr>
        </thead>
        <thead>
        <tr>
            <th style="width: 20px;"></th>
            <th>{{ trans('general.name') }}</th>
            <th>{{ trans('general.address') }}</th>
            <th>{{ trans('general.city') }}</th>
            <th>{{ trans('general.state') }}</th>
            <th>{{ trans('general.country') }}</th>
            <th>{{ trans('general.zip') }}</th>

        </tr>
        </thead>
        @php
            $counter = 1;
        @endphp

        @foreach ($children as $child)
            <tr>
                <td>{{ $counter }}</td>
                <td>{{ $child->name }}</td>
                <td>{{ $child->address }}</td>
                <td>{{ $child->city }}</td>
                <td>{{ $child->state }}</td>
                <td>{{ $child->country }}</td>
                <td>{{ $child->zip }}</td>
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
    <td>{{ ($asset->location) ? $asset->location->name : '' }}</td>
    <td>{{ \App\Helpers\Helper::getFormattedDateObject( $asset->last_checkout, 'datetime', false) }}</td>
    <td>{{ \App\Helpers\Helper::getFormattedDateObject( $asset->expected_checkin, 'datetime', false) }}</td>
    </tr>
        @php
            $counter++
        @endphp
@endforeach
</table>
@endif

@if ($assigned)
    @if ($assignedAssets->count() > 0)
        <br><br>
        <table class="inventory">
            <thead>
            <tr>
                <th colspan="10">{{ trans('admin/locations/message.assigned_assets') }}</th>
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

            @foreach ($assignedAssets as $asset)
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
                    <td>{{ ($asset->location) ? $asset->location->name : '' }}</td>
                    <td>{{ \App\Helpers\Helper::getFormattedDateObject( $asset->last_checkout, 'datetime', false) }}</td>
                    <td>{{ \App\Helpers\Helper::getFormattedDateObject( $asset->expected_checkin, 'datetime', false) }}</td>
                </tr>
                @php
                    $counter++
                @endphp
            @endforeach
        </table>
    @endif
@endif

@if ($accessories->count() > 0)
    <br><br>
    <table class="inventory">
        <thead>
        <tr>
            <th colspan="10">{{ trans('general.accessories') }}</th>
        </tr>
        </thead>
        <thead>
        <tr>
            <th style="width: 20px;"></th>
            <th style="width: 10%;">{{ trans('admin/locations/table.asset_name') }}</th>
            <th style="width: 10%;">{{ trans('admin/locations/table.asset_category') }}</th>
            <th style="width: 10%;">{{ trans('admin/locations/table.asset_manufacturer') }}</th>
            <th>{{ trans('admin/models/table.modelnumber') }}</th>
            <th style="width: 10%;">{{ trans('admin/locations/table.asset_location') }}</th>
        </tr>
        </thead>
        @php
            $counter = 1;
        @endphp

        @foreach ($accessories as $accessory)
            <tr>
                <td>{{ $counter }}</td>
                <td>{{ $accessory->name }}</td>
                <td>{{ ($accessory->category) ? $accessory->category->name : '' }}</td>
                <td>{{ ($accessory->manufacturer) ? $accessory->manufacturer->name : '' }}</td>
                <td>{{ $accessory->model_number }}</td>
                <td>{{ ($accessory->location) ? $accessory->location->name : '' }}</td>
            </tr>
            @php
                $counter++
            @endphp
        @endforeach
    </table>
@endif

@if ($assigned)
    @if ($assignedAccessories->count() > 0)
        <br><br>
        <table class="inventory">
            <thead>
            <tr>
                <th colspan="10">{{ trans('general.accessories_assigned') }}</th>
            </tr>
            </thead>
            <thead>
            <tr>
                <th style="width: 20px;"></th>
                <th style="width: 10%;">{{ trans('admin/locations/table.asset_name') }}</th>
                <th style="width: 10%;">{{ trans('admin/locations/table.asset_category') }}</th>
                <th style="width: 10%;">{{ trans('admin/locations/table.asset_manufacturer') }}</th>
                <th>{{ trans('admin/models/table.modelnumber') }}</th>
                <th style="width: 10%;">{{ trans('admin/locations/table.asset_location') }}</th>
            </tr>
            </thead>
            @php
                $counter = 1;
            @endphp

            @foreach ($assignedAccessories as $accessory)
                <tr>
                    <td>{{ $counter }}</td>
                    <td>{{ $accessory->name }}</td>
                    <td>{{ ($accessory->category) ? $accessory->category->name : '' }}</td>
                    <td>{{ ($accessory->manufacturer) ? $accessory->manufacturer->name : '' }}</td>
                    <td>{{ $accessory->model_number }}</td>
                    <td>{{ ($accessory->location) ? $accessory->location->name : '' }}</td>
                </tr>
                @php
                    $counter++
                @endphp
            @endforeach
        </table>
    @endif
@endif

@if ($consumables->count() > 0)
    <br><br>
    <table class="inventory">
        <thead>
        <tr>
            <th colspan="10">{{ trans('general.accessories') }}</th>
        </tr>
        </thead>
        <thead>
        <tr>
            <th style="width: 20px;"></th>
            <th>{{ trans('admin/locations/table.asset_name') }}</th>
            <th>{{ trans('general.qty') }}</th>
            <th>{{ trans('admin/locations/table.asset_category') }}</th>
            <th>{{ trans('admin/locations/table.asset_manufacturer') }}</th>
            <th>{{ trans('admin/models/table.modelnumber') }}</th>
        </tr>
        </thead>
        @php
            $counter = 1;
        @endphp

        @foreach ($consumables as $consumable)
            <tr>
                <td>{{ $counter }}</td>
                <td>{{ $consumable->name }}</td>
                <td>{{ $consumable->qty }}</td>
                <td>{{ ($consumable->category) ? $consumable->category->name : '' }}</td>
                <td>{{ ($consumable->manufacturer) ? $consumable->manufacturer->name : '' }}</td>
                <td>{{ $consumable->model_number }}</td>
            </tr>
            @php
                $counter++
            @endphp
        @endforeach
    </table>
@endif

@if ($components->count() > 0)
    <br><br>
    <table class="inventory">
        <thead>
        <tr>
            <th colspan="10">{{ trans('general.components') }}</th>
        </tr>
        </thead>
        <thead>
        <tr>
            <th style="width: 20px;"></th>
            <th>{{ trans('admin/locations/table.asset_name') }}</th>
            <th>{{ trans('general.qty') }}</th>
            <th>{{ trans('admin/locations/table.asset_category') }}</th>
            <th>{{ trans('admin/locations/table.asset_manufacturer') }}</th>
            <th>{{ trans('admin/models/table.modelnumber') }}</th>
        </tr>
        </thead>
        @php
            $counter = 1;
        @endphp

        @foreach ($components as $component)
            <tr>
                <td>{{ $counter }}</td>
                <td>{{ $component->name }}</td>
                <td>{{ $component->qty }}</td>
                <td>{{ ($component->category) ? $component->category->name : '' }}</td>
                <td>{{ ($component->manufacturer) ? $component->manufacturer->name : '' }}</td>
                <td>{{ $component->model_number }}</td>
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
