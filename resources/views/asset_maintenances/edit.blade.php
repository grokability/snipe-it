@extends('layouts/default')

{{-- Page title --}}
@section('title')
  @if ($item->id)
    {{ trans('admin/asset_maintenances/form.update') }}
  @else
    {{ trans('admin/asset_maintenances/form.create') }}
  @endif
  @parent
@stop


@section('header_right')
<a href="{{ URL::previous() }}" class="btn btn-primary pull-right">
  {{ trans('general.back') }}</a>
@stop


{{-- Page content --}}
@section('content')

<div class="row">
  <div class="col-md-9">
    @if ($item->id)
      <form class="form-horizontal" method="post" action="{{ route('maintenances.update', $item->id) }}" autocomplete="off">
      {{ method_field('PUT') }}
    @else
      <form class="form-horizontal" method="post" action="{{ route('maintenances.store') }}" autocomplete="off">
    @endif
    <!-- CSRF Token -->
    {{ csrf_field() }}

    <div class="box box-default">

        @if ($item->id)
          <div class="box-header with-border">
            <h2 class="box-title">
              {{ $item->title }}
            </h2>
          </div><!-- /.box-header -->
        @endif

      <div class="box-body">

        <!-- Title -->
        <div class="form-group {{ $errors->has('title') ? ' has-error' : '' }}">
          <label for="title" class="col-md-3 control-label">
            {{ trans('admin/asset_maintenances/form.title') }}
          </label>
          <div class="col-md-7">
            <input class="form-control" type="text" name="title" id="title" value="{{ old('title', $item->title) }}"{{  (Helper::checkIfRequired($item, 'title')) ? ' required' : '' }} />
            {!! $errors->first('title', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
          </div>
        </div>

        <!-- This is a new maintenance -->
        @if (!$item->id)


          @include ('partials.forms.edit.asset-select', [
            'translated_name' => trans('general.assets'),
            'fieldname' => 'selected_assets[]',
            'multiple' => true,
            'required' => true,
            'select_id' => 'assigned_assets_select',
            'asset_selector_div_id' => 'assets_for_maintenance_div',
            'asset_ids' => $item->id ? $item->asset()->pluck('id')->toArray() : old('selected_assets'),
            'asset_id' => $item->id ? $item->asset()->pluck('id')->toArray() : null
          ])
        @else

          @if ($item->asset->company)
            <div class="form-group">
              <label for="company" class="control-label col-md-3">
                {{ trans('general.company') }}
              </label>

              <div class="col-md-9">
                <p class="form-control-static">
                  {{  $item->asset->company->name }}
                </p>
              </div>
            </div>
          @endif

            <div class="form-group">
              <label for="asset" class="control-label col-md-3">
                {{ trans('general.asset') }}
              </label>

              <div class="col-md-9">
                <p class="form-control-static">
                  {{ $item->asset ? $item->asset->present()->fullName : '' }}
                </p>
              </div>
            </div>

            @if ($item->asset->location)
              <div class="form-group">
                <label for="location" class="control-label col-md-3">
                  {{ trans('general.location') }}
                </label>

                <div class="col-md-9">
                  <p class="form-control-static">
                    {{ $item->asset->location->name }}
                  </p>
                </div>
              </div>
            @endif

        @endif


        @include ('partials.forms.edit.maintenance_type')
        @include ('partials.forms.edit.supplier-select', ['translated_name' => trans('general.supplier'), 'fieldname' => 'supplier_id'])


        <!-- Start Date -->
        <div class="form-group {{ $errors->has('start_date') ? ' has-error' : '' }}">
          <label for="start_date" class="col-md-3 control-label">
            {{ trans('admin/asset_maintenances/form.start_date') }}
          </label>

          <div class="col-md-4">
            <x-input.datepicker
                    name="start_date"
                    :value="old('start_date', $item->start_date)"
                    placeholder="{{ trans('general.select_date') }}"
                    required="{{ Helper::checkIfRequired($item, 'start_date') }}"
            />
            {!! $errors->first('start_date', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
          </div>
        </div>



        <!-- Completion Date -->
        <div class="form-group {{ $errors->has('completion_date') ? ' has-error' : '' }}">
          <label for="start_date" class="col-md-3 control-label">{{ trans('admin/asset_maintenances/form.completion_date') }}</label>

          <div class="input-group col-md-4">
            <x-input.datepicker
                    name="completion_date"
                    :value="old('start_date', $item->completion_date)"
                    placeholder="{{ trans('general.select_date') }}"
                    required="Helper::checkIfRequired($item, 'completion_date')"
            />
            {!! $errors->first('completion_date', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
          </div>
        </div>

        <!-- Warranty -->
        <div class="form-group">
          <div class="col-sm-offset-3 col-sm-9">
              <label class="form-control">
                <input type="checkbox" value="1" name="is_warranty" id="is_warranty" {{ old('is_warranty', $item->is_warranty) == '1' ? ' checked="checked"' : '' }}>
                {{ trans('admin/asset_maintenances/form.is_warranty') }}
              </label>
          </div>
        </div>

        <!-- Asset Maintenance Cost -->
        <div class="form-group {{ $errors->has('cost') ? ' has-error' : '' }}">
          <label for="cost" class="col-md-3 control-label">{{ trans('admin/asset_maintenances/form.cost') }}</label>
          <div class="col-md-2">
            <div class="input-group">
              <span class="input-group-addon">
                @if (($item->asset) && ($item->asset->location) && ($item->asset->location->currency!=''))
                  {{ $item->asset->location->currency }}
                @else
                  {{ $snipeSettings->default_currency }}
                @endif
              </span>
              <input class="col-md-2 form-control" type="text" name="cost" id="cost" value="{{ old('cost', Helper::formatCurrencyOutput($item->cost)) }}" />
              {!! $errors->first('cost', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div class="form-group {{ $errors->has('notes') ? ' has-error' : '' }}">
          <label for="notes" class="col-md-3 control-label">{{ trans('admin/asset_maintenances/form.notes') }}</label>
          <div class="col-md-7">
            <textarea class="col-md-6 form-control" id="notes" name="notes">{{ old('notes', $item->notes) }}</textarea>
            {!! $errors->first('notes', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
          </div>
        </div>
      </div> <!-- .box-body -->

      <div class="box-footer text-right">
        <button type="submit" class="btn btn-success"><x-icon type="checkmark" /> {{ trans('general.save') }}</button>
      </div>
    </div> <!-- .box-default -->
    </form>
  </div>
</div>

@stop
