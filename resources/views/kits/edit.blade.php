@extends('layouts/edit-form', [
    'createText' =>  trans('admin/kits/general.create'),
    'updateText' =>   trans('admin/kits/general.update'),
    'helpTitle' => trans('admin/kits/general.about_kits_title'),
    'helpText' => trans('admin/kits/general.about_kits_text'),
    'formAction' => (isset($item->id)) ? route('kits.update', ['kit' => $item->id]) : route('kits.store'),
])

{{-- Page content --}}
@section('inputFields')
@include ('partials.forms.edit.name', ['translated_name' => trans('general.name')])
@stop

@section('content')
@parent
{{-- Assets by model --}}
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><span>{{ trans('general.asset_models') }}</span></h3>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                <table
                data-cookie-id-table="kitModelsTable"
                data-columns="{{ \App\Presenters\PredefinedKitPresenter::dataTableModels() }}"


                data-side-pagination="server"



                data-sort-order="asc"
                data-sort-name="name"
                id="kitModelsTable"
                class="table table-striped snipe-table"
                data-url="{{ route('api.kits.models.index', $item->id) }}"
                data-export-options='{
                "fileName": "export-kit-models-{{ date('Y-m-d') }}",
                "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
                </table>
                <a href="{{ route('modal.show', ['type' => 'kit-model', 'itemId' => $item->id]) }}" data-refresh="kitModelsTable" data-toggle="modal" data-target="#createModal" class="btn btn-primary pull-right"><i class="fas fa-plus icon-white"></i> {{ trans('button.append')}}</a>
                </div>
            </div> <!--.box-body-->
        </div> <!-- /.box.box-default-->
    </div> <!-- .col-md-12-->
</div>
{{-- Licenses --}}
{{--<div class="row">--}}
{{--        <div class="col-md-12">--}}
{{--            <div class="box box-default">--}}
{{--                <div class="box-header with-border">--}}
{{--                    <h3 class="box-title">Licenses--}}{{-- TODO: trans --}}{{--</h3>--}}
{{--                </div>--}}
{{--                <div class="box-body">--}}
{{--                    <div class="table-responsive">--}}
{{--                    <table--}}
{{--                    data-cookie-id-table="kitLicensesTable"--}}
{{--                    data-columns="{{ \App\Presenters\PredefinedKitPresenter::dataTableLicenses() }}"--}}
{{--                    --}}
{{--                    --}}
{{--                    data-side-pagination="server"--}}
{{--                    --}}
{{--                    --}}
{{--                    --}}
{{--                    data-sort-order="asc"--}}
{{--                    data-sort-name="name"--}}
{{--                    id="kitLicensesTable"--}}
{{--                    class="table table-striped snipe-table"--}}
{{--                    data-url="{{ route('api.kits.licenses.index', $item->id) }}"--}}
{{--                    data-export-options='{--}}
{{--                    "fileName": "export-kit-models-{{ date('Y-m-d') }}",--}}
{{--                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]--}}
{{--                    }'>--}}
{{--                    </table>--}}
{{--                    <a href="{{ route('modal.show', [ 'type' => 'kit-license', 'itemId' => $item->id]) }}" data-refresh="kitLicensesTable" data-toggle="modal" data-target="#createModal" class="btn btn-primary pull-right"><i class="fas fa-plus icon-white"></i> Append--}}{{-- TODO: trans --}}{{--</a>--}}
{{--                    </div>--}}
{{--                </div> <!--.box-body-->--}}
{{--            </div> <!-- /.box.box-default-->--}}
{{--        </div> <!-- .col-md-12-->--}}
{{--    </div>--}}
{{-- Consumables --}}
{{--<div class="row">--}}
{{--        <div class="col-md-12">--}}
{{--            <div class="box box-default">--}}
{{--                <div class="box-header with-border">--}}
{{--                    <h3 class="box-title">Consumables--}}{{-- TODO: trans --}}{{--</h3>--}}
{{--                </div>--}}
{{--                <div class="box-body">--}}
{{--                    <div class="table-responsive">--}}
{{--                    <table--}}
{{--                    data-cookie-id-table="kitConsumablesTable"--}}
{{--                    data-columns="{{ \App\Presenters\PredefinedKitPresenter::dataTableConsumables() }}"--}}
{{--                    --}}
{{--                    --}}
{{--                    data-side-pagination="server"--}}
{{--                    --}}
{{--                    --}}
{{--                    --}}
{{--                    data-sort-order="asc"--}}
{{--                    data-sort-name="name"--}}
{{--                    id="kitConsumablesTable"--}}
{{--                    class="table table-striped snipe-table"--}}
{{--                    data-url="{{ route('api.kits.consumables.index', $item->id) }}"--}}
{{--                    data-export-options='{--}}
{{--                    "fileName": "export-kit-models-{{ date('Y-m-d') }}",--}}
{{--                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]--}}
{{--                    }'>--}}
{{--                    </table>--}}
{{--                    <a href="{{ route('modal.show', ['type' => 'kit-consumable', 'itemId' => $item->id]) }}" data-refresh="kitConsumablesTable" data-toggle="modal" data-target="#createModal" class="btn btn-primary pull-right"><i class="fas fa-plus icon-white"></i> Append--}}{{-- TODO: trans --}}{{--</a>--}}
{{--                    </div>--}}
{{--                </div> <!--.box-body-->--}}
{{--            </div> <!-- /.box.box-default-->--}}
{{--        </div> <!-- .col-md-12-->--}}
{{--    </div>--}}
{{-- Accessories --}}
{{--<div class="row">--}}
{{--        <div class="col-md-12">--}}
{{--            <div class="box box-default">--}}
{{--                <div class="box-header with-border">--}}
{{--                    <h3 class="box-title">Accessories--}}{{-- TODO: trans --}}{{--</h3>--}}
{{--                </div>--}}
{{--                <div class="box-body">--}}
{{--                    <div class="table-responsive">--}}
{{--                    <table--}}
{{--                    data-cookie-id-table="kitAccessoriesTable"--}}
{{--                    data-columns="{{ \App\Presenters\PredefinedKitPresenter::dataTableAccessories() }}"--}}
{{--                    --}}
{{--                    --}}
{{--                    data-side-pagination="server"--}}
{{--                    --}}
{{--                    --}}
{{--                    --}}
{{--                    data-sort-order="asc"--}}
{{--                    data-sort-name="name"--}}
{{--                    id="kitAccessoriesTable"--}}
{{--                    class="table table-striped snipe-table"--}}
{{--                    data-url="{{ route('api.kits.accessories.index', $item->id) }}"--}}
{{--                    data-export-options='{--}}
{{--                    "fileName": "export-kit-models-{{ date('Y-m-d') }}",--}}
{{--                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]--}}
{{--                    }'>--}}
{{--                    </table>--}}
{{--                    <a href="{{ route('modal.show', ['type' => 'kit-accessory', 'itemId' => $item->id]) }}" data-refresh="kitAccessoriesTable" data-toggle="modal" data-target="#createModal" class="btn btn-primary pull-right"><i class="fas fa-plus icon-white"></i> Append--}}{{-- TODO: trans --}}{{--</a>--}}
{{--                    </div>--}}
{{--                </div> <!--.box-body-->--}}
{{--            </div> <!-- /.box.box-default-->--}}
{{--        </div> <!-- .col-md-12-->--}}
{{--    </div>--}}
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table')
@stop
