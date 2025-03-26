<!doctype html>
<html lang="en">

<head>
    <style type="text/css">
        .button {
            position: relative;
            margin-top: 5px;
            background-color: #0066af;;
            border: 2px;
            font-size: 18px;
            color: #FFFFFF;
            padding: 20px;
            width: 100px;
            text-align: center;
            text-decoration: none;
            overflow: hidden;
            cursor: pointer;
            }
        page[size="A4"] {
        background: white;
	width: 21cm;
        height: 29.7cm;
        display: block;
	margin: 0 auto;
        margin-bottom: 0.5cm;
        box-shadow: 0 0 0.5cm rgba(0,0,0,0.5);
        }
        @media print {
            .noPrint{
                display: none;
            }
            body, page[size="A4"] {
            margin: auto;
            box-shadow: 0;
        }
        }
        input {
            width: 100%;
            border: none;
            padding: 0px;
            float: left;
            border-color: transparent;
            font-family: "Calibri", Arial, sans-serif;
            font-size: 11pt;
        }
            .print-logo {
            display: inline;
            position: relative;
            margin-left: auto;
            margin-right: auto;
            height: 80%;
            float: left;
            top: 50%;
            transform: translateY(-50%);
        }
        .tg {
            border-collapse: collapse;
            border-spacing: 0;
            font-size: 11pt;
        }

        .tg td {
            border-color: black;
            border-style: solid;
            border-width: 1px;
            overflow: hidden;
            padding: 5px 10px;
            word-break: normal;
        }

        .tg th {
            border-color: black;
            border-style: transparent;
            border-width: 1px;
            font-weight: normal;
            overflow: hidden;
            padding: 5px 10px;
            word-break: normal;
        }

        .tg .tg-lboi {
            text-align: left;
            vertical-align: middle
        }

        .tg .tg-xogg {
            text-align: left;
            vertical-align: middle
        }

        .tg .tg-levo {
            text-align: left;
            vertical-align: top
        }

        .tg .tg-vhtn {
            text-align: center;
            vertical-align: middle
        }

        .tg .tg-81u1 {
            text-align: left;
            vertical-align: middle
        }

        .tg .tg-centar {
            text-align: center;
            vertical-align: middle
        }
        .address{
            position: relative;
            width: 200px;
            float: right;
            text-align: left;
            display: inline-block;
            top: 50%;
            transform: translateY(-50%);
        }
        body{
            width: 760px;
            font-family: "Calibri", Arial, sans-serif;
        }
        #header{
            height: 100px;
            width: 760px;
            border: 10px;
        }
    </style>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    @php
            $pun_naziv = $location->present()->fullName();
            $broj_rn = $location->present()->zip;
            $datum_kreiranja = $location->present()->created_at;
            $email_regex = "/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i";
            if (preg_match("/\d{7}/",$pun_naziv,$sifra_lista)){

            $sifra = $sifra_lista[0];
		}
		else{
		$sifra = "";
		}
            $ime_filijale = (string)str_replace($sifra,' ',$pun_naziv);

            $ostali_podaci = $location->present()->address2;
	    if (preg_match($email_regex, $ostali_podaci, $email_lista)){
            $email = $email_lista[0];
	    }
	    else{
	    $email = "";
		}
	    $telefoni = (string)str_replace($email,' ',$ostali_podaci);
    @endphp
    <title>{{ $broj_rn }} - {{ $ime_filijale }} </title>
</head>

<div id="radniNalog">
<body>
    <div id="header">
    @if ($snipeSettings->logo_print_assets=='1')
        @if ($snipeSettings->brand == '3')
            @if ($snipeSettings->logo!='')
                    <img class="print-logo" src="{{ url('/') }}/uploads/{{ $snipeSettings->logo }}">
            @endif
            <p class="address">{{ $snipeSettings->site_name }},{{ $snipeSettings->footer_text }}</p>
            @elseif ($snipeSettings->brand == '2')
                @if ($snipeSettings->logo!='')
                    <img class="print-logo" src="{{ url('/') }}/uploads/{{ $snipeSettings->logo }}">
                @endif
            @else
            <p class="address">{{ $snipeSettings->site_name }},{{ $snipeSettings->footer_text }}</p>
        @endif
    @endif
    </div>
    <table class="tg" style="undefined;table-layout: fixed; width: 740px">
        <colgroup>
            <col style="width: 105px">
            <col style="width: 108px">
            <col style="width: 85px">
            <col style="width: 85px">
            <col style="width: 135px">
            <col style="width: 60px">
            <col style="width: 80px">
        </colgroup>
        <tbody>
            <tr>
                <td class="tg-xogg">RADNI NALOG BR</td>
                <td class="tg-xogg">{{ $location->present()->zip }}</td>
                <td colspan="2"></td>
                <td>Datum kreiranja</td>
                <td class="tg-xogg" colspan="2">{{ \App\Helpers\Helper::getFormattedDateObject(($datum_kreiranja),'date', false) }}</td>
            </tr>
            <tr>
            @if ($parent)
                <td class="tg-xogg">Zastupnik</td>
                <td class="tg-xogg">Šifra</td>
                <td class="tg-xogg">{{ $parent->present()->address2}}</td>
                <td class="tg-xogg">Naziv</td>
                <td class="tg-xogg">{{ $parent->present()->fullName() }}</td>
                <td class="tg-xogg">Mesto</td>
                <td class="tg-xogg">{{ $parent->present()->city }}</td>
            </tr>
            @endif
            <tr>
                <td class="tg-81u1">Filijala</td>
                <td class="tg-81u1">Šifra</td>
                <td class="tg-81u1">{{ $sifra }}</td>
                <td class="tg-81u1">Naziv</td>
                <td class="tg-81u1" colspan="3">{{ $ime_filijale }}</td>
            </tr>
            <tr>
                <td class="tg-81u1">Adresa</td>
                <td class="tg-81u1" colspan="3">{{ $location->present()->address }}</td>
                <td class="tg-81u1">Mesto:</td>
                <td class="tg-81u1" colspan="2">{{ $location->present()->city }}</td>
            </tr>
            <tr>
                <td class="tg-81u1">e-mail Filijale</td>
		<td class="tg-81u1" colspan="3">{{ $email }}</td>
		<td class="tg-81u1" colspan="3">Tel: {{ $location->present()->phone ?: $telefoni }}</td>
            </tr>
            <tr>
                <td class="tg-vhtn" colspan="7">Spisak izdate opreme</td>
            </tr>


            <tr>
                <td class="tg-81u1">Redni broj</td>
                <td class="tg-81u1">Tip</td>
                <td class="tg-81u1" colspan="2">Model</td>
                <td class="tg-81u1" colspan="2">Serijski broj</td>
                <td class="tg-81u1" colspan="1">Datum</td>
            </tr>
            @if ($assets->count() > 0)
            @php
            $counter = 1;
            @endphp

            @foreach ($assets as $asset)
            @php
            if($snipeSettings->show_archived_in_list != 1 && $asset->assetstatus->archived == 1){
            continue;
            }
            @endphp
            <tr>
                <td class="tg-centar">{{ $counter }}</td>
                <td class="tg-centar">{{($asset->model->category) ? $asset->model->category->name : ''}}
                </td>
                <td class="tg-centar" colspan="2">{{($asset->model->manufacturer) ? $asset->model->manufacturer->name : ''}} {{($asset->model) ? $asset->model->model_number : ''}}</td>
                <td class="tg-centar" colspan="2">{{ $asset->serial }}</td>
                <td class="tg-centar" colspan="1">{{ \App\Helpers\Helper::getFormattedDateObject( $asset->last_checkout,
                    'date', false) }}</td>
            </tr>
            @php
                $counter++;
            @endphp
            @endforeach
            @endif
            <tr>
                <td class="tg-levo" colspan="7">Napomena : {{ $location->present()->state }}</td>
            </tr>
            @for ($i = 0; $i < 5; $i++)
            <tr>
            <td colspan="7">
                <input type="text"></input>
            </td>
            </tr>
            @endfor
            <tr>
                <td class="tg-centar" colspan="1">Radni nalog</td>
                <td class="tg-centar" colspan="2">Ime Prezime</td>
                <td class="tg-centar" colspan="1">Datum štampe</td>
                <td class="tg-centar" colspan="3">Potpis</td>
            <tr>
            <tr>
                <td class="tg-centar" colspan="1">Odobrio</td>
                <td class="tg-levo" colspan="2">Dragan Vasović</td>
                <td class="tg-centar" colspan="1">{{ \App\Helpers\Helper::getFormattedDateObject(now(),'date', false) }}</td>
                <td class="tg-levo" colspan="3"></td>
            </tr>
            <tr>
                <td class="tg-centar" colspan="1">Izdao</td>
                <td class="tg-levo" colspan="2">Dubravka Bjekić Vasović</td>
                <td class="tg-centar" colspan="1">{{ \App\Helpers\Helper::getFormattedDateObject(now(),'date', false) }}</td>
                <td class="tg-levo" colspan="3"></td>
            <tr>
                <td class="tg-centar" colspan="1">Pripremio</td>
                <td class="tg-levo" colspan="2">
                <input type="text" placeholder="Ime"></input>
                </td>
                <td class="tg-centar" colspan="1">{{ \App\Helpers\Helper::getFormattedDateObject(now(),'date', false) }}</td>
                <td class="tg-levo" colspan="3"></td>
            </tr>
            </tr>
            <tr>
                <td class="tg-centar" colspan="1">Menadžer</td>
                @if ($manager)
                    <td class="tg-levo" colspan="2">{{ $manager->present()->fullName() }}</td>
                @else
                    <td class="tg-levo" colspan="2"></td>
                @endif
                <td class="tg-centar" colspan="1"></td>
                <td class="tg-levo" colspan="3"></td>
            </tr>
            </tr>
        </tbody>
    </table>
    </div>
    <div class="noPrint"><button class="button" onclick="window.print()">Štampa</button></div>
