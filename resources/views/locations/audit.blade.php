@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.audit') }} {{ $location->name }}
    @parent
@stop

{{-- Page content --}}
@section('content')
<div class="container">
  <div class="row">
    <div class="col-md-12">
      <h1>{{ trans('general.assets_checked_out_count') }}</h1>

      <div class="panel panel-default">
        <div class="panel-body">

          <form method="POST" action="{{ route('locations.audit.store', $location) }}" autocomplete="off">
            @csrf

            {{-- ─── Add Missing Asset Picker ─── --}}
  <div class="row mb-3">
    <div class="col-12">
      <div class="d-flex">
        {{-- 1 Picker flexes to fill all available space --}}
        <div class="flex-fill">
          @include('partials.forms.edit.asset-select', [
            'translated_name'       => null,
            'fieldname'             => 'missing_asset_temp',
            'multiple'              => true,
            'asset_selector_div_id' => 'missing_assets_div',
            'select_id'             => 'missing_assets_select',
            'unselect'              => true,
            'asset_status_type'     => null,
          ])
        </div>

        {{-- 2 Button sits inline on the right --}}
        <div class="ms-2">
          <button id="addMissingBtn" type="button" class="btn btn-warning">
            <i class="fa fa-plus me-1"></i>{{ trans('button.add') }}
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="table-responsive mb-4">
  <table class="table table-bordered" id="auditTable">
    <thead>
      <tr>
        <th>{{ trans('general.type') }}</th>
        <th>{{ trans('general.manufacturer') }}</th>
        <th>{{ trans('general.model_no') }}</th>
        <th>{{ trans('general.serial_number') }}</th>
        <th>{{ trans('general.status') }}</th>
        <th class="text-center">{{ trans('general.checked_out') }}?</th>
      </tr>
    </thead>
    <tbody>
      @forelse($assets as $asset)
        <tr data-id="{{ $asset->id }}">
          <td>{{ $asset->model->category->name ?? 'N/A' }}</td>
          <td>{{ optional($asset->model->manufacturer)->name }}</td>
          <td>{{ $asset->model->model_number ?? '' }}</td>
          <td>{{ $asset->serial ?? 'N/A' }}</td>
          <td>{{ $asset->assetstatus->name ?? 'N/A' }}</td>
          <td style="text-align: center; vertical-align: middle;">
            <input style="margin: 0 auto;"
                   type="checkbox"
                   name="asset_ids[]"
                   value="{{ $asset->id }}"
                   checked>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="text-center">
            {{ trans('general.bad_data') }}
          </td>
        </tr>
      @endforelse
    </tbody>

    {{-- Missing‐asset rows will be injected here by your JS --}}
    <tbody id="dynamicMissingRows">
      {{-- Example of how your JS should append each missing row:
      <tr class="table-warning" data-id="{{ $id }}">
        <td colspan="6"
            class="d-flex justify-content-center align-items-center">
          {{ $text }} 
          <button type="button"
                  class="btn btn-sm btn-outline-danger remove-missing ms-2"
                  data-id="{{ $id }}"
                  title="{{ trans('button.remove') }}">
            <i class="fa fa-trash"></i>
          </button>
        </td>
      </tr>
      --}}
    </tbody>
  </table>
</div>

            {{-- Hidden inputs for missing asset IDs --}}
            <div id="missing_inputs"></div>

            {{-- Email Body --}}
            <div class="form-group mt-4">
              <label class="d-block">{{ $auditUser->email }}</label>
              <label for="email-body">{{ trans('general.notes') }}</label>
              <textarea id="email-body" name="email_body" class="form-control" rows="6"></textarea>
            </div>

            <button type="submit" class="btn btn-primary mt-3">
              {{ trans('general.send_email') }}
            </button>

          </form>

        </div>
      </div>
    </div>
  </div>
</div>
@stop

@section('moar_scripts')
    @include('partials/assets-assigned')

    {{-- Summernote CSS & JS --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-bs4.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-bs4.min.js"></script>

    <script nonce="{{ csrf_token() }}">
    $(function() {
        // Initialize Summernote
        $('#email-body').summernote({
            placeholder: '',
            tabsize: 2,
            height: 200,
            toolbar: [
                ['style',['style']],
                ['font',['bold','italic','underline','clear']],
                ['fontsize',['fontsize']],
                ['color',['color']],
                ['para',['ul','ol','paragraph']],
                ['insert',['link','picture']],
                ['view',['fullscreen','codeview','help']]
            ]
        });

        // Add Missing Asset button click
        $('#addMissingBtn').on('click', function(){
        var $sel   = $('#missing_assets_div select');
        var picks  = $sel.select2('data');   // Array of { id, text, … }

        if (!picks.length) return;

        picks.forEach(function(item){
            var id   = item.id;
            var text = item.text;

            // skip duplicates
            if ($('#missing_inputs input[value="'+id+'"]').length) return;

            // 1) append the warning row
            $('#dynamicMissingRows').append(
            '<tr class="table-warning" data-id="'+id+'">'+
                '<td colspan="5" class="d-flex justify-content-center align-items-center">'+
                $('<div>').text(text).html()+'</td>'
                +'<td class="text-center">'+
                '<button type="button" '+
                        'class="btn btn-danger btn-sm delete-asset" '+
                        'data-id="'+id+'" '+
                        'title="{{ trans("button.remove") }}">'+
                    '<i class="fa fa-trash"></i>'+
                '</button>'+
                '</td>'+
            '</tr>'
            );

            // 2) add hidden input
            $('#missing_inputs').append(
            '<input type="hidden" name="missing_assets[]" value="'+id+'">'
            );
        });

        // 3) clear all selections
        $sel.val(null).trigger('change');
        });

        // Remove missing asset row
        $('#dynamicMissingRows').on('click', '.remove-missing', function() {
            var id = $(this).data('id');
            $('#dynamicMissingRows').find('tr[data-id="' + id + '"]').remove();
            $('#missing_inputs').find('input[value="' + id + '"]').remove();
        });

        // On submit: append checked asset list
        $('form').on('submit', function() {
            var assetDetails = '<ul>';
            $('input[name="asset_ids[]"]:checked').each(function() {
                var $td = $(this).closest('tr').children('td');
                assetDetails += '<li><strong>' + $td.eq(0).text().trim() + '</strong> – '
                             + $td.eq(1).text().trim() + ' – '
                             + $td.eq(3).text().trim() + ' – '
                             + $td.eq(4).text().trim() + '</li>';
            });
            assetDetails += '</ul>';
            var current = $('#email-body').summernote('code');
            $('#email-body').summernote('code', current + assetDetails);
        });
    });
    </script>
@endsection
