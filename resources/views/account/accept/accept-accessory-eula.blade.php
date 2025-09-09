<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        body {
            font-family:'Dejavu Sans', Arial, Helvetica, sans-serif;
            font-size: 11px;
        }
    </style>
</head>
<body>

@if ($logo)
    <center>
        <img src="{{ $logo }}">
        <p>{{$company_name}}</p>
    </center>
@endif
<br>

<p>
    {{ trans('general.date') }}: {{ date($date_settings) }} <br>
    {{ trans('general.asset_model') }}: {{ $item_model }}<br>
    {{ trans('general.quantity') }}: {{ $qty }}
</p>

@if ($eula)
    <hr>
    {!!  $eula !!}
    <hr>
@endif


<p>
    {{ trans('general.assigned_date') }}: {{$check_out_date}}<br>
    {{ trans('general.assignee') }}: {{$assigned_to}}<br>
    {{ trans('general.accepted_date') }}: {{$accepted_date}}
</p>


@if ($signature!='')
    <img src="{{ $signature }}" style="max-width: 600px; border-bottom: black solid 1px;">
@endif
</body>
</html>
