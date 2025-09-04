@extends('layouts/default')

{{-- Page title --}}
@section('title')
  {{ trans('admin/custom_fields/general.custom_fields') }}
@parent
@stop

@section('header_right')
<a href="{{ route('fields.index') }}" class="btn btn-primary pull-right">
        {{ trans('general.back') }}</a>
@stop

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-header with-border">
        <h2 class="box-title">{{ $custom_fieldset->name }} {{ trans('admin/custom_fields/general.fieldset') }}</h2>
        <div class="box-tools pull-right">
        </div>
      </div><!-- /.box-header -->
      <div class="box-body">
        <table
          name="fieldsets" id="sort" class="table table-responsive todo-list">
          <thead>
            <tr>
              {{-- Hide the sorting handle if we can't update the fieldset --}}
              @can('update', $custom_fieldset)
              <th class="col-md-1"><span class="sr-only">{{ trans('admin/custom_fields/general.reorder') }}</span></th>
              @endcan
              <th class="col-md-1" style="display: none;">{{ trans('admin/custom_fields/general.order') }}</th>
              <th class="col-md-3">{{ trans('admin/custom_fields/general.field_name') }}</th>
              <th class="col-md-2">{{ trans('admin/custom_fields/general.field_format') }}</th>
              <th class="col-md-2">{{ trans('admin/custom_fields/general.field_element') }}</th>
              <th class="col-md-1">{{ trans('admin/custom_fields/general.encrypted') }}</th>
              <th class="col-md-1">{{ trans('admin/custom_fields/general.required') }}</th>
              <th class="col-md-1"><span class="sr-only">{{ trans('button.remove') }}</span></th>
            </tr>
          </thead>
          <tbody>
            @foreach($custom_fieldset->fields as $field)
            <tr class="{{ Auth::user()->can('update', $custom_fieldset)?'cansort':'' }}" data-index="{{ $field->pivot->custom_field_id }}" id="item_{{ $field->pivot->custom_field_id }}">
              {{-- Hide the sorting handle if we can't update the fieldset --}}
              @can('update', $custom_fieldset)
              <td>
                <!-- drag handle -->
                <span class="handle">
                <i class="fas fa-ellipsis-v"></i>
                <i class="fas fa-ellipsis-v"></i>
                </span>
              </td>
              @endcan
              <td class="index" style="display: none;">{{$field->pivot->order + 1}}</td> {{--this +1 needs to exist to keep the first row from reverting to 0 if you edit/delete fields in/from a fielset--}}
              <td>{{$field->name}}</td>
              <td>{{$field->format}}</td>
              <td>{{$field->element}}</td>
              <td>{{ $field->field_encrypted=='1' ?  trans('general.yes') : trans('general.no') }}</td>
                <td>

                    @if ($field->pivot->required)
                    <form method="post" action="{{ route('fields.optional', [$custom_fieldset->id, $field->id]) }}">
                      @csrf 
                      <button type="submit" class="btn btn-link"><i class="fa fa-check text-success" aria-hidden="true"></i></button>
                      </form>

                    @else

                      <form method="post" action="{{ route('fields.required', [$custom_fieldset->id, $field->id]) }}">
                      @csrf 
                      <button type="submit" class="btn btn-link"><i class="fa fa-times text-danger" aria-hidden="true"></i></button>
                      </form>
                    @endif

                </td>
              <td>
                @can('update', $custom_fieldset)
                <form method="post" action="{{ route('fields.disassociate', [$field, $custom_fieldset->id]) }}">
                  @csrf 
                  <button type="submit" class="btn btn-sm btn-danger" data-tooltip="true" title="{{ trans('general.remove_customfield_association') }}"><i class="fa fa-minus icon-white" aria-hidden="true"></i></button>
                </form>
                @endcan
              </td>
            </tr>
            @endforeach
          </tbody>
          @can('update', $custom_fieldset)
          <tfoot>
            <tr>
              <td colspan="8">
                <form method="POST" action="{{ route('fieldsets.associate', $custom_fieldset->id) }}" accept-charset="UTF-8" class="form-inline" id="ordering">
                  @csrf

                <div class="form-group">
                  <label for="field_id" class="sr-only">
                    {{ trans('admin/custom-field/general.add_field_to_fieldset')}}
                  </label>
                  <x-input.select
                      name="field_id"
                      :options="$custom_fields_list"
                      style="min-width:400px"
                      aria-label="field_id"
                  />
                </div>

                <div class="form-group" style="display: none;">
                  <input aria-label="order" maxlength="3" size="3" name="order" type="text" value="{{ $maxid }}">
                  <label for="order">{{ trans('admin/custom_fields/general.order') }}</label>
                </div>

                <div class="checkbox-inline">
                    <label>
                      <input type="checkbox" name="required" value="on" @checked(old('required'))>
                      <span style="padding-left: 10px;">{{ trans('admin/custom_fields/general.required') }}</span>
                    </label>
                </div>

                <span style="padding-left: 10px;">
                  <button type="submit" class="btn btn-primary"> {{ trans('general.save') }}</button>
                </span>

                </form>

              </td>
            </tr>
          </tfoot>
          @endcan
        </table>
      </div> <!-- /.box-body-->
    </div> <!-- /.box.box-default-->
  </div> <!-- /.col-md-12-->
</div> <!--/.row-->

@stop

@push('js')
  @can('update', $custom_fieldset)

  <script nonce="{{ csrf_token() }}">
  var fixHelperModified = function(e, tr) {
      var $originals = tr.children();
      var $helper = tr.clone();
      $helper.children().each(function(index) {
          $(this).width($originals.eq(index).width())
      });
      return $helper;
  },
      updateIndex = function(e, ui) {
          $('td.index', ui.item.parent()).each(function (i) {
              $(this).html(i + 1);
              $.ajax({
                method: "POST",
                url: "{{ route('api.customfields.order', $custom_fieldset->id)  }}",
                headers: {
                    "X-Requested-With": 'XMLHttpRequest',
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                },
                data: $("#sort tbody").sortable('serialize', {
                }),

                success: function(data) {
                    //console.log('ajax fired');
                    // do some stuff here


                }
      	    });
          });
      };

  // this uses the jquery UI sortable method, NOT the query-dragtable library
  $("#sort tbody").sortable({
      helper: fixHelperModified,
      stop: updateIndex
  }).disableSelection();
</script>
  @endcan
@endpush
