@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/predefinedFilters/table.title') }} 
@parent
@stop

{{-- Page content --}}
@section('content')

<div class="row">
  <div class="col-md-9">
    <div class="box box-default">
      <div class="box-body">
            <table
                    data-columns="{{ \App\Presenters\PredefinedFilterPresenter::dataTableLayout() }}"
                    data-cookie-id-table="predefinedFiltersTable"
                    data-id-table="predefinedFiltersTable"
                    data-show-footer="false"
                    data-side-pagination="server"
                    data-sort-order="asc"
                    data-sort-name="name"
                    id="predefinedFiltersTable"
                    data-buttons="predefinedFiltersButtons"
                    class="table table-striped snipe-table"
                    data-url="{{ route('api.predefined-filters.index') }}"
                    data-export-options='{
                "fileName": "export-predefinedFilters-{{ date('Y-m-d') }}",
                "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
          </table>
      </div>
    </div>
  </div>
  <!-- side address column -->
  <div class="col-md-3">
    <h2>{{ trans('admin/predefinedfilters/table.about') }}</h2>

      <div class="box">
          <div class="box-body">
              <p>{!!  trans('admin/predefinedfilters/table.info') !!}</p> {{-- TODO --}}
          </div>
      </div>

      <div class="box box-success">
          <div class="box-body">
            <p><i class="fas fa-check icon-white text-success"></i> <strong>{{ trans('admin/predefinedfilters/table.private') }}</strong>: {{ trans('admin/predefinedfilters/message.help.private') }}</p> {{-- TODO --}}
          </div>
      </div>

      <div class="box box-danger">
          <div class="box-body">
              <p><i class="fas fa-times text-red"></i> <strong>{{ trans('admin/predefinedfilters/table.public') }}</strong>: {{ trans('admin/predefinedfilters/message.help.public') }}</p> {{-- TODO --}}
          </div>
      </div>

  </div>

</div>
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table')

<script>
$(document).ready(function () { 
  // TODO Ensure that L10n is complete after refactoring.
    $('#predefinedFiltersTable').on('click', '.btn-warning', function (e) {
    e.preventDefault();

    // Extract filter ID from href 
    const editLink = $(this).attr('href');
    const match = editLink.match(/predefined-filters\/(\d+)/);

    if (!match) {
        alert("Could not extract filter ID from the link.");
        return;
    }

    const filterId = match[1];

    // Generate URL to fetch filter data via Laravel route helper
    const showUrlTemplate = `{{ route('api.predefined-filters.show', ['id' => '__ID__']) }}`;
    const showUrl = showUrlTemplate.replace('__ID__', filterId);

    // Fetch the filter data from backend
    fetch(showUrl, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        }
    })
    .then(response => {
        if (!response.ok) throw new Error(`Failed to fetch filter data: ${response.statusText}`);
        return response.json();
    })
    .then(data => {
        // Extract name, visibility, and permissions
        const name = data.name || '';
        const visibility = data.visibility || 'private'; // default if not present
        const permissions = data.permission_groups || [];

        // this function returns a Promise
        return openFilterCreateUpdateModal(false, name, permissions, visibility);
    })
    .then(updatedInput => {
        // After modal closes, send updated data back to backend
        const updateUrlTemplate = `{{ route('api.predefined-filters.update', ['id' => '__ID__']) }}`;
        const updateUrl = updateUrlTemplate.replace('__ID__', filterId);

        // Prepare payload based on your backend expectations
        const payload = {
            name: updatedInput.name,
            filter_data: updatedInput.filter_data,   // make sure this exists in your modal return
            permissions: updatedInput.permissions,
            is_public: updatedInput.visibility === "public"
        };

        return fetch(updateUrl, {
            method: 'PUT',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                // Add 'Authorization' header if rebquired
            },
            body: JSON.stringify(payload)
        });
    })
    .then(response => {
        if (!response.ok) throw new Error(`Failed to update filter: ${response.statusText}`);
        //actual with alert 
        $('#predefinedFiltersTable').bootstrapTable('refresh');
        alert("Filter updated successfully.");
    })
    .catch(error => {
        console.error(error);
        alert("Error: " + error.message);
    });
});

});
</script>
@include('partials/advanced-search.modal')

@php
    $layout = json_decode(\App\Presenters\PredefinedFilterPresenter::dataTableLayout());
@endphp
@include('partials/advanced-search.search-inputs', ['layout' => $layout])
@stop
