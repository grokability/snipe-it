@extends('layouts/default')
@section('title')
Bulk Audit
@stop
@section('content')
<div class="row justify-content-center">
  <div class="col-md-8 col-lg-7">
    <form method="POST" action="{{ route('hardware.bulkaudit.store') }}" autocomplete="off">
      @csrf
      <div class="box box-default shadow-sm">
        <div class="box-header with-border bg-primary text-white">
          <h2 class="box-title" style="font-size: 1.5rem;">Bulk Audit</h2>
        </div>
        <div class="box-body p-4">
          <!-- Asset Tag (AJAX Multi-select) -->
          <div class="form-group row mb-3">
            <div class="col-md-9">
              @include('partials.forms.asset-multiselect', [
                'fieldname' => 'selected_assets[]',
                'asset_ids' => old('selected_assets'),
                'label' => trans('general.assets'),
                'placeholder' => trans('general.select_assets'),
              ])
            </div>
          </div>
          <!-- Location -->
          <div class="form-group row mb-3">
            <label for="location_id_location_select" class="col-md-3 col-form-label text-md-right">Location</label>
            <div class="col-md-8">
              <select class="form-control js-data-ajax select2" data-endpoint="locations" data-placeholder="Select a Location" name="location_id" id="location_id_location_select" style="width:100%;">
                <option value="">Select a Location</option>
                @foreach(\App\Models\Location::all() as $loc)
                  <option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-1 d-flex align-items-center">
              <a href="{{ route('modal.show', 'location') }}" data-toggle="modal" data-target="#createModal" data-select="location_id_location_select" class="btn btn-sm btn-primary">New</a>
            </div>
          </div>
          <!-- Update location -->
          <div class="form-group row mb-3">
            <div class="col-md-9 offset-md-3">
              <label class="form-control">
                <input type="checkbox" value="1" name="update_location" {{ old('update_location') ? 'checked' : '' }}>
                <span>Update Asset Location
                  <a href="#" class="text-dark-gray" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="" data-html="true" data-content="Checking this box will edit the asset record to reflect this new location. Leaving it unchecked will simply note the location in the audit log. Note that if this asset is checked out, it will not change the location of the person, asset or location it is checked out to." data-original-title="&lt;i class='far fa-life-ring'&gt;&lt;/i&gt; More Info">
                    <i class="far fa-life-ring" aria-hidden="true"></i></a></span>
              </label>
            </div>
          </div>
          <!-- Next Audit Date -->
          <div class="form-group row mb-3">
            <label for="next_audit_date" class="col-md-3 col-form-label text-md-right">Next Audit Date</label>
            <div class="col-md-9">
              <div class="input-group date" data-provide="datepicker" data-date-format="yyyy-mm-dd" data-date-clear-btn="true">
                <input type="text" class="form-control" placeholder="Next Audit Date" name="next_audit_date" id="next_audit_date" value="{{ old('next_audit_date', date('Y-m-d')) }}">
                <span class="input-group-addon"><i class="fas fa-calendar" aria-hidden="true"></i></span>
              </div>
            </div>
          </div>
          <!-- Note -->
          <div class="form-group row mb-3">
            <label for="note" class="col-md-3 col-form-label text-md-right">Notes</label>
            <div class="col-md-9">
              <textarea class="form-control" id="note" name="note" rows="3" placeholder="Enter any notes...">{{ old('note') }}</textarea>
            </div>
          </div>
        </div> <!--/.box-body-->
        <div class="box-footer bg-light p-3 d-flex justify-content-between align-items-center">
          <a class="btn btn-link" href="{{ URL::previous() }}">Cancel</a>
          <button type="submit" id="audit_button" class="btn btn-success px-4">
            <i class="fas fa-check icon-white" aria-hidden="true"></i> Audit
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
@stop
@section('moar_scripts')
<script nonce="{{ csrf_token() }}">
  $(function () {
    // JS for Select2 or asset multi-select if needed
  });
</script>
@stop
