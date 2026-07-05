@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/hardware/general.bulk_checkin') }}
@parent
@stop

{{-- Page content --}}
@section('content')

<style>
  .input-group {
    padding-left: 0px !important;
  }
</style>

<div class="row">
  <!-- left column -->
  <div class="col-md-7">
    <div class="box box-default">
      <div class="box-header with-border">
        <h2 class="box-title"> {{ trans('admin/hardware/form.tag') }} </h2>
      </div>
      <div class="box-body">
        <form class="form-horizontal" method="post" action="{{ route('hardware.bulkcheckin.store') }}" autocomplete="off">
          {{ csrf_field() }}

            @if ($removed_assets->isNotEmpty())
                <div class="box box-solid box-warning">
                    <div class="box-header with-border">
                        <span class="box-title col-xs-12">Warning</span>
                    </div>
                    <div class="box-body">
                        <p>{{ trans('general.unassigned_assets_removed') }}</p>
                        <ul>
                            @foreach($removed_assets as $removed_asset)
                                <li>
                                    <a href="{{ route('hardware.show', $removed_asset->id) }}">
                                        {{ $removed_asset->present()->fullName }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @include('partials.forms.edit.asset-select', [
                'translated_name'       => trans('general.assets'),
                'fieldname'             => 'selected_assets[]',
                'multiple'              => true,
                'required'              => true,
                'asset_status_type'     => 'Deployed',
                'select_id'             => 'assigned_assets_select',
                'asset_selector_div_id' => 'assets_to_checkin_div',
                'asset_ids'             => old('selected_assets'),
            ])

            <!-- Status -->
            <div class="form-group {{ $errors->has('status_id') ? 'error' : '' }}">
                <label for="status_id" class="col-md-3 control-label">
                    {{ trans('admin/hardware/form.status') }}
                </label>
                <div class="col-md-7">
                    <x-input.select
                            name="status_id"
                            :options="$statusLabel_list"
                            :selected="old('status_id', $status_id ?? null)"
                            style="width: 100%;"
                            aria-label="status_id"
                    />
                    {!! $errors->first('status_id', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                </div>
            </div>

            <x-input.location-select
                :label="trans('general.location')"
                name="location_id"
                :selected="old('location_id')"
            />

            <!-- Update actual location -->
            <div class="form-group">
                <div class="col-md-9 col-md-offset-3">
                    <label class="form-control">
                        <input name="update_default_location" type="radio" value="1" @checked(old('update_default_location', '1') == '1') aria-label="update_default_location" />
                        {{ trans('admin/hardware/form.asset_location') }}
                    </label>
                    <label class="form-control">
                        <input name="update_default_location" type="radio" value="0" @checked(old('update_default_location') === '0') aria-label="update_default_location" />
                        {{ trans('admin/hardware/form.asset_location_update_default_current') }}
                    </label>
                </div>
            </div>

            <!-- Checkin Date -->
            <div class="form-group {{ $errors->has('checkin_at') ? 'error' : '' }}">
                <label for="checkin_at" class="col-sm-3 control-label">
                    {{ trans('admin/hardware/form.checkin_date') }}
                </label>
                <div class="col-md-8">
                    <div class="input-group date col-md-5" data-provide="datepicker" data-date-format="yyyy-mm-dd" data-date-end-date="0d" data-date-clear-btn="true">
                        <input type="text" class="form-control" placeholder="{{ trans('general.select_date') }}" name="checkin_at" id="checkin_at" value="{{ old('checkin_at') }}">
                        <span class="input-group-addon"><x-icon type="calendar" /></span>
                    </div>
                    {!! $errors->first('checkin_at', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                </div>
            </div>

            <!-- Note -->
            <div class="form-group {{ $errors->has('note') ? 'error' : '' }}">
                <label for="note" class="col-sm-3 control-label">
                    {{ trans('general.notes') }}
                </label>
                <div class="col-md-8">
                    <textarea class="col-md-6 form-control" id="note" name="note">{{ old('note') }}</textarea>
                    {!! $errors->first('note', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                </div>
            </div>

            <!-- Checkin associated license seats -->
            <div class="form-group">
                <div class="col-md-9 col-md-offset-3">
                    <label class="form-control" for="checkin_licenses">
                        <input type="hidden" name="checkin_licenses" value="0" />
                        <input type="checkbox" value="1" name="checkin_licenses" id="checkin_licenses"
                            @checked(old('checkin_licenses', '1') == '1') />
                        {{ trans('admin/hardware/form.checkin_licenses') }}
                    </label>
                </div>
            </div>

            <!-- Checkin associated assets -->
            <div class="form-group">
                <div class="col-md-9 col-md-offset-3">
                    <label class="form-control" for="checkin_child_assets">
                        <input type="hidden" name="checkin_child_assets" value="0" />
                        <input type="checkbox" value="1" name="checkin_child_assets" id="checkin_child_assets"
                            @checked(old('checkin_child_assets', '1') == '1') />
                        {{ trans('admin/hardware/form.checkin_child_assets') }}
                    </label>
                </div>
            </div>

      </div> <!--./box-body-->
      <div class="box-footer">
        <a class="btn btn-link" href="{{ URL::previous() }}"> {{ trans('button.cancel') }}</a>
        <button type="submit" class="btn btn-success pull-right"><x-icon type="checkmark" /> {{ trans('admin/hardware/general.checkin_assets') }}</button>
      </div>
        </form>
    </div>
  </div> <!--/.col-md-7-->
</div>
@stop

@section('moar_scripts')
<script nonce="{{ csrf_token() }}">
    $(function () {
        $("form").submit(function() {
            $(this).find(":input").filter(function(){ return !this.value; }).attr("disabled", "disabled");
            return true;
        });
    });
</script>
@stop
