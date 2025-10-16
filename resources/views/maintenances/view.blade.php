<?php
use Carbon\Carbon;
?>
@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/maintenances/general.view') }} {{ $maintenance->name }}
@parent
@stop

{{-- Page content --}}
@section('content')
  <div class="row">
    <div class="col-md-9">

      <div class="nav-tabs-custom">
        <ul class="nav nav-tabs hidden-print">

          <li class="active">
            <a href="#info" data-toggle="tab">
                            <span class="hidden-lg hidden-md">
                            <x-icon type="info-circle" class="fa-2x" />
                            </span>
              <span class="hidden-xs hidden-sm">{{ trans('admin/users/general.info') }}</span>
            </a>
          </li>

          <li>
            <a href="#files" data-toggle="tab">
                                <span class="hidden-lg hidden-md">
                                <x-icon type="files" class="fa-2x" />
                                </span>
              <span class="hidden-xs hidden-sm">{{ trans('general.file_uploads') }}
                {!! ($maintenance->uploads->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($maintenance->uploads->count()).'</span>' : '' !!}
                                </span>
            </a>
          </li>

          @can('update', $maintenance)
            <li class="pull-right">
              <a href="#" data-toggle="modal" data-target="#uploadFileModal">
                                <span class="hidden-lg hidden-xl hidden-md">
                                    <x-icon type="paperclip" class="fa-2x" />
                                </span>
                <span class="hidden-xs hidden-sm">
                                    <x-icon type="paperclip" />
                                    {{ trans('button.upload') }}
                                </span>
              </a>
            </li>
          @endcan
        </ul>
          <div class="tab-content">
            <div class="tab-pane active" id="info">
            <div class="row-new-striped">
              <div class="row">

                  <div class="col-md-3">
                    {{ trans('admin/maintenances/form.asset_maintenance_type') }}
                  </div>
                  <div class="col-md-9">
                    {{ $maintenance->asset_maintenance_type }}
                  </div>

              </div> <!-- /row -->

              <div class="row">
                <div class="col-md-3">
                  {{ trans('general.asset') }}
                </div>
                <div class="col-md-9">
                  <a href="{{ route('hardware.show', $maintenance->asset_id) }}">
                    {{ $maintenance->asset->present()->fullName }}
                  </a>
                </div>
              </div> <!-- /row -->

              @if ($maintenance->asset->model)
                <div class="row">
                  <div class="col-md-3">
                    {{ trans('general.asset_model') }}
                  </div>
                  <div class="col-md-9">
                    <a href="{{ route('models.show', $maintenance->asset->model_id) }}">
                      {{ $maintenance->asset->model->name }}
                    </a>
                  </div>
                </div> <!-- /row -->
              @endif

              @if ($maintenance->asset->company)
                <div class="row">
                  <div class="col-md-3">
                    {{ trans('general.company') }}
                  </div>
                  <div class="col-md-9">
                    <a href="{{ route('companies.show', $maintenance->asset->company_id) }}">
                      {{ $maintenance->asset->company->name }}
                    </a>
                  </div>
                </div> <!-- /row -->
              @endif


              @if ($maintenance->supplier)
              <div class="row">
                <div class="col-md-3">
                  {{ trans('general.supplier') }}
                </div>
                <div class="col-md-9">
                  <a href="{{ route('suppliers.show', $maintenance->supplier_id) }}">
                    {{ $maintenance->supplier->name }}
                  </a>
                </div>
              </div> <!-- /row -->
              @endif

              <div class="row">
                <div class="col-md-3">
                  {{ trans('admin/maintenances/form.start_date') }}
                </div>
                <div class="col-md-9">
                  {{ Helper::getFormattedDateObject($maintenance->start_date, 'date', false) }}
                </div>
              </div> <!-- /row -->

              <div class="row">
                <div class="col-md-3">
                  {{ trans('admin/maintenances/form.completion_date') }}
                </div>
                <div class="col-md-9">
                  @if ($maintenance->completion_date)
                    {{ Helper::getFormattedDateObject($maintenance->completion_date, 'date', false) }}
                  @else
                    {{ trans('admin/maintenances/message.asset_maintenance_incomplete') }}
                  @endif
                </div>
              </div> <!-- /row -->

              @if ($maintenance->url)
                <div class="row">
                  <div class="col-md-3">
                    {{ trans('general.url') }}
                  </div>
                  <div class="col-md-9">
                    <a href="{{ $maintenance->url }}">
                      {{ $maintenance->url }}
                      <x-icon type="external-link" />
                    </a>
                  </div>
                </div> <!-- /row -->
              @endif

              <div class="row">
                <div class="col-md-3">
                  {{ trans('admin/maintenances/form.asset_maintenance_time') }}
                </div>
                <div class="col-md-9">
                  {{ $maintenance->asset_maintenance_time }}
                </div>
              </div> <!-- /row -->

              @if ($maintenance->cost > 0)
              <div class="row">
                <div class="col-md-3">
                  {{ trans('admin/maintenances/form.cost') }}
                </div>
                <div class="col-md-9">
                  {{ \App\Models\Setting::getSettings()->default_currency .' '. Helper::formatCurrencyOutput($maintenance->cost) }}
                </div>
              </div> <!-- /row -->
              @endif

              <div class="row">
                <div class="col-md-3">
                  {{ trans('admin/maintenances/form.is_warranty') }}
                </div>
                <div class="col-md-9">
                  {{ $maintenance->is_warranty ? trans('admin/maintenances/message.warranty') : trans('admin/maintenances/message.not_warranty') }}
                </div>
              </div> <!-- /row -->

              @if ($maintenance->notes)
              <div class="row">
                <div class="col-md-3">
                  {{ trans('admin/maintenances/form.notes') }}
                </div>
                <div class="col-md-9">
                  {!! nl2br(Helper::parseEscapedMarkedownInline($maintenance->notes)) !!}
                </div>
              </div> <!-- /row -->
              @endif


            </div>
            </div><!-- /row-new-striped -->
            <div class="tab-pane" id="files">
              <div class="row">
                <div class="col-md-12">
                  <x-filestable object_type="maintenances" :object="$maintenance" />
                </div>
              </div>
            </div>
        </div><!-- /box-body -->
      </div><!-- /box -->

      </div> <!-- col-md-9  end -->
      <div class="col-md-3">

        @if ($maintenance->image!='')
          <div class="col-md-12 text-center" style="padding-bottom: 17px;">
            <img src="{{ Storage::disk('public')->url(app('maintenances_path').e($maintenance->image)) }}" class="img-responsive img-thumbnail" style="width:100%" alt="{{ $maintenance->name }}">
          </div>
        @endif

        <div class="col-md-12">

          <ul class="list-unstyled" style="line-height: 22px; padding-bottom: 20px;">

            @if ($maintenance->notes)
              <li>
                <strong>{{ trans('general.notes') }}</strong>:
                {!! nl2br(Helper::parseEscapedMarkedownInline($maintenance->notes)) !!}
              </li>
            @endif


          </ul>
      </div>

      @can('update', $maintenance)
        <div class="col-md-12">
          <a href="{{ route('maintenances.edit', [$maintenance->id]) }}" style="width: 100%;" class="btn btn-sm btn-warning btn-social">
            <x-icon type="edit" />
            {{ trans('general.update') }}
          </a>
        </div>
      @endcan
    </div>

    </div> <!-- row  end -->

  @can('assets.files', Asset::class)
    @include ('modals.upload-file', ['item_type' => 'maintenance', 'item_id' => $maintenance->id])
  @endcan
@stop

@section('moar_scripts')
  @include ('partials.bootstrap-table')
@stop

