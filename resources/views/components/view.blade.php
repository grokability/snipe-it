@extends('layouts/default')

{{-- Page title --}}
@section('title')

 {{ $component->name }}
 {{ trans('general.component') }}
@parent
@stop

{{-- Right header --}}
@section('header_right')
  @can('manage', $component)
    <div class="dropdown pull-right">
      <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
        {{ trans('button.actions') }}
          <span class="caret"></span>
      </button>
      
      <ul class="dropdown-menu pull-right" role="menu22">
        @if ($component->assigned_to != '')
          @can('checkin', $component)
          <li role="menuitem">
            <a href="{{ route('components.checkin.show', $component->id) }}">
              {{ trans('admin/components/general.checkin') }}
            </a>
          </li>
          @endcan
        @else
          @can('checkout', $component)
          <li role="menuitem">
            <a href="{{ route('components.checkout.show', $component->id)  }}">
              {{ trans('admin/components/general.checkout') }}
            </a>
          </li>
          @endcan
        @endif

        @can('update', $component)
        <li role="menuitem">
          <a href="{{ route('components.edit', $component->id) }}">
            {{ trans('admin/components/general.edit') }}
          </a>
        </li>
        @endcan
      </ul>
    </div>
  @endcan
@stop

{{-- Page content --}}
@section('content')
{{-- Page content --}}
<div class="row">
  <div class="col-md-9">

    <!-- Custom Tabs -->
    <div class="nav-tabs-custom">
      <ul class="nav nav-tabs hidden-print">

        <li class="active">
          <a href="#checkedout" data-toggle="tab">
            <span class="hidden-lg hidden-md">
            <x-icon type="info-circle" class="fa-2x" />
            </span>
            <span class="hidden-xs hidden-sm">{{ trans('admin/users/general.info') }}</span>
          </a>
        </li>

        <li>
          <a href="#history" data-toggle="tab">
                <span class="hidden-lg hidden-md">
                  <i class="fas fa-history fa-2x" aria-hidden="true"></i>
                </span>
            <span class="hidden-xs hidden-sm">
                  {{ trans('general.history') }}
                </span>
          </a>
        </li>

        @can('components.files', $component)
          <li>
            <a href="#files" data-toggle="tab">
            <span class="hidden-lg hidden-md">
            <i class="far fa-file fa-2x" aria-hidden="true"></i></span>
              <span class="hidden-xs hidden-sm">{{ trans('general.file_uploads') }}
                {!! ($component->uploads->count() > 0 ) ? '<span class="badge badge-secondary">'.number_format($component->uploads->count()).'</span>' : '' !!}
            </span>
            </a>
          </li>
        @endcan

        @can('components.files', $component)
          <li class="pull-right">
            <a href="#" data-toggle="modal" data-target="#uploadFileModal">
              <x-icon type="paperclip" /> {{ trans('button.upload') }}
            </a>
          </li>
        @endcan
      </ul>

      <div class="tab-content">

        <div class="tab-pane active" id="checkedout">
          <div class="table table-responsive">

            <table
                    data-cookie-id-table="componentsCheckedoutTable"
                    data-id-table="componentsCheckedoutTable"
                    data-side-pagination="server"
                    data-show-footer="true"
                    data-sort-order="asc"
                    data-sort-name="name"
                    id="componentsCheckedoutTable"
                    class="table table-striped snipe-table"
                    data-url="{{ route('api.components.assets', $component->id)}}"
                    data-export-options='{
                "fileName": "export-components-{{ str_slug($component->name) }}-checkedout-{{ date('Y-m-d') }}",
                "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
              <thead>
              <tr>
                <th data-searchable="false" data-sortable="false" data-field="name" data-formatter="hardwareLinkFormatter">
                  {{ trans('general.asset') }}
                </th>
                <th data-searchable="false" data-sortable="false" data-field="qty">
                  {{ trans('general.qty') }}
                </th>
                <th data-searchable="false" data-sortable="false" data-field="note">
                  {{ trans('general.notes') }}
                </th>
                <th data-searchable="false" data-sortable="false" data-field="created_at" data-formatter="dateDisplayFormatter">
                  {{ trans('general.date') }}
                </th>
                <th data-switchable="false" data-searchable="false" data-sortable="false" data-field="checkincheckout" data-formatter="componentsInOutFormatter">
                  {{ trans('general.checkin') }}/{{ trans('general.checkout') }}
                </th>
              </tr>
              </thead>
            </table>

          </div>
        </div> <!-- close tab-pane div -->

        <div class="tab-pane" id="history">
          <div class="table-responsive">

            <table
                    data-columns="{{ \App\Presenters\HistoryPresenter::dataTableLayout() }}"
                    class="table table-striped snipe-table"
                    id="componentHistory"
                    data-id-table="componentHistory"
                    data-side-pagination="server"
                    data-sort-order="desc"
                    data-sort-name="created_at"
                    data-export-options='{
                         "fileName": "export-component-{{  $component->id }}-history",
                         "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                       }'
                    data-url="{{ route('api.activity.index', ['item_id' => $component->id, 'item_type' => 'component']) }}"
                    data-cookie-id-table="componentHistory"
                    data-cookie="true">
            </table>
          </div>
        </div><!-- /.tab-pane -->


        @can('components.files', $component)
          <div class="tab-pane" id="files">
            <div class="row">
              <div class="col-md-12">
                <x-filestable object_type="components" :object="$component" />
              </div>
            </div>
          </div> <!-- /.tab-pane -->
        @endcan

      </div>
    </div>
  </div> <!-- .col-md-9-->


  <!-- side address column -->
  <div class="col-md-3">
    @if ($component->image!='')
      <div class="col-md-12 text-center" style="padding-bottom: 15px;">
        <a href="{{ Storage::disk('public')->url('components/'.e($component->image)) }}" data-toggle="lightbox" data-type="image">
          <img src="{{ Storage::disk('public')->url('components/'.e($component->image)) }}" class="img-responsive img-thumbnail" alt="{{ $component->name }}"></a>
      </div>

    @endif

    @if ($component->serial!='')
    <div class="col-md-12" style="padding-bottom: 5px;"><strong>{{ trans('admin/hardware/form.serial') }}: </strong>
    {{ $component->serial }} </div>
    @endif

    @if ($component->purchase_date)
    <div class="col-md-12" style="padding-bottom: 5px;"><strong>{{ trans('admin/components/general.date') }}: </strong>
    {{ $component->purchase_date }} </div>
    @endif

    @if ($component->purchase_cost)
    <div class="col-md-12" style="padding-bottom: 5px;"><strong>{{ trans('general.unit_cost') }}:</strong>
    {{ $snipeSettings->default_currency }}

    {{ Helper::formatCurrencyOutput($component->purchase_cost) }} </div>
    @endif

    @if ($component->purchase_cost)
        <div class="col-md-12" style="padding-bottom: 5px;"><strong>{{ trans('general.total_cost') }}:</strong>
            {{ $snipeSettings->default_currency }}

            {{ Helper::formatCurrencyOutput($component->totalCostSum()) }} </div>
    @endif

    @if ($component->order_number)
    <div class="col-md-12" style="padding-bottom: 5px;"><strong>{{ trans('general.order_number') }}:</strong>
    {{ $component->order_number }} </div>
    @endif

    @if ($component->notes)

      <div class="col-md-12">
        <strong>
          {{ trans('general.notes') }}
        </strong>
      </div>
      <div class="col-md-12">
        {!! nl2br(Helper::parseEscapedMarkedownInline($component->notes)) !!}
      </div>

    @endif

  @can('update', $component)
    <div class="col-md-12 hidden-print" style="padding-top: 5px;">
      <a href="{{ route('components.edit', $component->id) }}" class="btn btn-sm btn-warning btn-social btn-block hidden-print">
        <x-icon type="edit" />
        {{ trans('admin/components/general.edit') }}
      </a>
    </div>
  @endcan

  @can('checkout', $component)
    <div class="col-md-12 hidden-print" style="padding-top: 5px;">
            <a href="{{ route('components.checkout.show', $component->id)  }}" class="btn btn-sm bg-maroon btn-social btn-block hidden-print">
                 <x-icon type="checkout" />
              {{ trans('admin/components/general.checkout') }}
            </a>
    </div>
  @endcan

  @can('delete', $component)
        <div class="col-md-12 hidden-print" style="padding-top: 5px;">
          @if ($component->isDeletable())
              <button class="btn btn-sm btn-block btn-danger btn-social delete-asset" data-icon="fa fa-trash" data-toggle="modal" data-title="{{ trans('general.delete') }}" data-content="{{ trans('general.sure_to_delete_var', ['item' => $component->name]) }}" data-target="#dataConfirmModal" onClick="return false;">
              <x-icon type="delete" />
              {{ trans('general.delete') }}
            </button>
          @else
            <a href="#" class="btn btn-block btn-sm btn-danger btn-social hidden-print disabled" data-tooltip="true"  data-placement="top" data-title="{{ trans('general.cannot_be_deleted') }}" onClick="return false;">
              <x-icon type="delete" />
              {{ trans('general.delete') }}
            </a>
          @endif
  @endcan


</div>
</div> <!-- .row-->

@can('components.files', Component::class)
  @include ('modals.upload-file', ['item_type' => 'component', 'item_id' => $component->id])
@endcan
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'component' . $component->name . '-export', 'search' => false])
@stop
