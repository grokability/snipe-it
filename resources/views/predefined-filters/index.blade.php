@php
    use App\Presenters\PredefinedFilterPresenter;
@endphp

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
                    data-columns="{{ PredefinedFilterPresenter::dataTableLayout() }}"
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
            <p><i class="fas fa-check icon-white text-success"></i> <strong>{{ trans('admin/predefinedfilters/table.private') }}</strong>: {{ trans('admin/predefinedfilters/help.private') }}</p> {{-- TODO --}}
          </div>
      </div>

      <div class="box box-danger">
          <div class="box-body">
              <p><i class="fas fa-times text-red"></i> <strong>{{ trans('admin/predefinedfilters/table.public') }}</strong>: {{ trans('admin/predefinedfilters/help.public') }}</p> {{-- TODO --}}
          </div>
      </div>

  </div>

</div>
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table')

<script>
$(document).ready(function () { 
    Livewire.on('updatePredefinedFiltersModalEvent', () => {
        $('#predefinedFiltersTable').bootstrapTable('refresh');
    });

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
            Livewire.dispatch('openPredefinedFiltersModal', {
                action: 'edit',
                predefinedFilterData: {
                    name: data.name || '',
                    visibility: data.visibility || 'private',
                    permission_groups: data.permission_groups || []
                },
                predefinedFilterId: data.id 
            });
        });
    });
});
</script>
<livewire:partials.advancedSearch.modal/>

@php
    $layout = json_decode(PredefinedFilterPresenter::dataTableLayout());
@endphp
@stop
