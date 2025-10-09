@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ $model->name }}
    {{ ($model->model_number) ? '(#'.$model->model_number.')' : '' }}
@parent
@stop

@section('header_right')
    @can('update', \App\Models\AssetModel::class)
        <div class="btn-group pull-right">
            <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">{{ trans('button.actions') }}
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                @if ($model->deleted_at=='')
                    <li><a href="{{ route('models.edit', $model->id) }}">{{ trans('admin/models/table.edit') }}</a></li>
                    <li><a href="{{ route('models.clone.create', $model->id) }}">{{ trans('admin/models/table.clone') }}</a></li>
                    <li><a href="{{ route('hardware.create', ['model_id' => $model->id]) }}">{{ trans('admin/hardware/form.create') }}</a></li>
                @else
                    <li><a href="{{ route('models.restore.store', $model->id) }}">{{ trans('admin/models/general.restore') }}</a></li>
                @endif
            </ul>
        </div>
    @endcan
@stop

{{-- Page content --}}
@section('content')


<div class="row">

    @if ($model->deleted_at!='')
        <div class="col-md-12">
            <div class="callout callout-warning">
                <x-icon type="warning" />
                {{ trans('admin/models/general.deleted') }}
            </div>
        </div>
    @endif

    <div class="col-md-9">

        <div class="nav-tabs-custom">

            <ul class="nav nav-tabs">
                <li class="active">
                    <a href="#assets" data-toggle="tab">

                        <span class="hidden-lg hidden-md">
                          <i class="fas fa-barcode fa-2x"></i>
                        </span>
                        <span class="hidden-xs hidden-sm">
                            {{ trans('general.assets') }}
                            {!! ($model->assets()->AssetsForShow()->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($model->assets()->AssetsForShow()->count()).'</span>' : '' !!}
                        </span>
                    </a>
                </li>

                <li>
                    <a href="#files" data-toggle="tab">

                        <span class="hidden-lg hidden-md">
                          <i class="fas fa-barcode fa-2x"></i>
                        </span>
                        <span class="hidden-xs hidden-sm">
                            {{ trans('general.files') }}
                            {!! ($model->uploads->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($model->uploads->count()).'</span>' : '' !!}
                          </span>
                    </a>
                </li>
                <li class="pull-right">
                    <a href="#" data-toggle="modal" data-target="#uploadFileModal">
                        <x-icon type="paperclip" />
                        {{ trans('button.upload') }}
                    </a>
                </li>

            </ul>

            <div class="tab-content">
                <div class="tab-pane fade in active" id="assets">

                    @include('partials.asset-bulk-actions')

                    <table
                            data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                            data-show-columns-search="true"
                            data-cookie-id-table="assetListingTable"
                            data-id-table="assetListingTable"
                            data-side-pagination="server"
                            data-show-footer="true"
                            data-toolbar="#assetsBulkEditToolbar"
                            data-bulk-button-id="#bulkAssetEditButton"
                            data-bulk-form-id="#assetsBulkForm"
                            data-sort-order="asc"
                            id="assetListingTable"
                            data-url="{{ route('api.assets.index',['model_id'=> $model->id]) }}"
                            class="table table-striped snipe-table"
                            data-export-options='{
                "fileName": "export-models-{{ str_slug($model->name) }}-assets-{{ date('Y-m-d') }}",
                "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
                    </table>
                </div> <!-- /.tab-pane assets -->


                <div class="tab-pane fade" id="files">

                    <div class="row">
                        <div class="col-md-12">

                            <x-filestable object_type="models" :object="$model" />

                        </div> <!-- /.col-md-12 -->
                    </div> <!-- /.row -->

                </div>


            </div> <!-- /.tab-content -->
        </div>  <!-- /.nav-tabs-custom -->
    </div><!-- /. col-md-12 -->

    <div class="col-md-3">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <div class="box-heading">
                            <h2 class="box-title"> {{ trans('general.moreinfo') }}:</h2>
                        </div>
                    </div><!-- /.box-header -->
                    <div class="box-body">



                @if ($model->image)
                    <img src="{{ Storage::disk('public')->url(app('models_upload_path').e($model->image)) }}" class="img-responsive"></li>
                @endif


                <ul class="list-unstyled" style="line-height: 25px;">
                    @if ($model->category)
                        <li>
                            <strong>{{ trans('general.category') }}</strong>:
                            <a href="{{ route('categories.show', $model->category->id) }}">{{ $model->category->name }}</a>
                        </li>
                    @endif
                    @if ($model->deleted_at)
                        <li>
                            <strong>
                                <span class="text-danger">
                                {{ trans('general.deleted') }}:
                                {{ Helper::getFormattedDateObject($model->deleted_at, 'datetime', false) }}
                                </span>
                            </strong>

                        </li>
                    @endif

                    @if ($model->min_amt)
                        <li>
                            <strong>{{ trans('general.min_amt') }}</strong>:
                           {{$model->min_amt }}
                        </li>
                    @endif

                    @if ($model->manufacturer)
                        <li>
                            <strong>{{ trans('general.manufacturer') }}</strong>:
                            @can('view', \App\Models\Manufacturer::class)
                                <a href="{{ route('manufacturers.show', $model->manufacturer->id) }}">
                                    {{ $model->manufacturer->name }}
                                </a>
                            @else
                                {{ $model->manufacturer->name }}
                            @endcan
                        </li>

                        @if ($model->manufacturer->url)
                            <li>
                                <i class="fas fa-globe-americas"></i> <a href="{{ $model->manufacturer->url }}">{{ $model->manufacturer->url }}</a>
                            </li>
                        @endif

                        @if ($model->manufacturer->support_url)
                            <li>
                                <x-icon type="more-info" /> <a href="{{ $model->manufacturer->support_url }}">{{ $model->manufacturer->support_url }}</a>
                            </li>
                        @endif

                        @if ($model->manufacturer->support_phone)
                            <li>
                                <i class="fas fa-phone"></i>
                                <a href="tel:{{ $model->manufacturer->support_phone }}">{{ $model->manufacturer->support_phone }}</a>

                            </li>
                        @endif

                        @if ($model->manufacturer->support_email)
                            <li>
                                <i class="far fa-envelope"></i> <a href="mailto:{{ $model->manufacturer->support_email }}">{{ $model->manufacturer->support_email }}</a>
                            </li>
                        @endif
                    @endif
                    @if ($model->model_number)
                        <li>
                            <strong>{{ trans('general.model_no') }}</strong>:
                            {{ $model->model_number }}
                        </li>
                    @endif

                    @if ($model->depreciation)
                        <li>
                            <strong>{{ trans('general.depreciation') }}</strong>:
                            {{ $model->depreciation->name }} ({{ $model->depreciation->months.' '.trans('general.months')}})
                        </li>
                    @endif

                    @if ($model->eol)
                        <li>
                            <strong>{{ trans('general.eol') }}</strong>:
                            {{ $model->eol .' '. trans('general.months') }}
                        </li>
                    @endif

                    @if ($model->fieldset)
                        <li>
                            <strong>{{ trans('admin/models/general.fieldset') }}</strong>:
                            <a href="{{ route('fieldsets.show', $model->fieldset->id) }}">{{ $model->fieldset->name }}</a>
                        </li>
                    @endif

                    @if ($model->notes)
                        <li>
                            <strong>{{ trans('general.notes') }}</strong>:
                            {!! nl2br(Helper::parseEscapedMarkedownInline($model->notes)) !!}
                        </li>
                    @endif

                        @if ($model->created_at)
                            <li>
                                <strong>{{ trans('general.created_at') }}</strong>:
                                {{ Helper::getFormattedDateObject($model->created_at, 'datetime', false) }}
                            </li>
                        @endif

                        @if ($model->adminuser)
                            <li>
                                <strong>{{ trans('general.created_by') }}</strong>:
                                {{ $model->adminuser->display_name }}
                            </li>
                        @endif


                </ul>
                </div>
        </div>
        </div>
            @can('update', \App\Models\AssetModel::class)
            <div class="col-md-12" style="padding-bottom: 5px;">
                <a href="{{ ($model->deleted_at=='') ? route('models.edit', $model->id) : '#' }}" style="width: 100%;" class="btn btn-sm btn-warning btn-social hidden-print{{ ($model->deleted_at!='') ? ' disabled' : '' }}">
                    <x-icon type="edit" />
                    {{ trans('admin/models/table.edit') }}
                </a>
            </div>
            @endcan

            @can('create', \App\Models\AssetModel::class)
            <div class="col-md-12" style="padding-bottom: 5px;">
                <a href="{{ route('models.clone.create', $model->id) }}" style="width: 100%;" class="btn btn-sm btn-info btn-social hidden-print">
                    <x-icon type="clone" />
                    {{ trans('admin/models/table.clone') }}
                </a>
            </div>
            @endcan

            @can('delete', \App\Models\AssetModel::class)
                <div class="col-md-12" style="padding-top: 10px;">

                    @if ($model->deleted_at!='')
                        <form method="POST" action="{{ route('models.restore.store', $model->id) }}">
                            @csrf
                            <button style="width: 100%;" class="btn btn-sm btn-warning btn-social hidden-print">
                                <x-icon type="restore" />
                                {{ trans('button.restore') }}
                            </button>
                        </form>
                    @elseif ($model->assets()->count() > 0)
                        <button class="btn btn-block btn-sm btn-danger btn-social hidden-print disabled" data-tooltip="true"  data-placement="top" data-title="{{ trans('general.cannot_be_deleted') }}">
                            <x-icon type="delete" />
                            {{ trans('general.delete') }}
                        </button>
                    @else
                        <button class="btn btn-block btn-sm btn-danger btn-social delete-asset" data-toggle="modal" title="{{ trans('general.delete_what', ['item'=> trans('general.asset_model')]) }}" data-content="{{ trans('general.sure_to_delete_var', ['item' => $model->name]) }}" data-target="#dataConfirmModal" data-tooltip="true" data-icon="fa fa-trash" data-placement="top" data-title="{{ trans('general.delete_what', ['item'=> trans('general.asset_model')]) }}" onClick="return false;">
                            <x-icon type="delete" />
                            {{ trans('general.delete') }}
                        </button>
                </div>
                @endif
           @endcan

        </div>
</div> <!-- /.row -->

@can('update', \App\Models\AssetModel::class)
    @include ('modals.upload-file', ['item_type' => 'models', 'item_id' => $model->id])
@endcan
@stop

@section('moar_scripts')

    @include ('partials.bootstrap-table', ['exportFile' => 'manufacturer' . $model->name . '-export', 'search' => false])

@stop
