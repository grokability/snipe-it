@extends('layouts/default', [
    'helpText' => trans('admin/custom_fields/general.about_fieldsets_text'),
    'helpPosition' => 'right',
])


{{-- Page title --}}
@section('title')
  {{ trans('admin/custom_fields/general.manage') }} {{ trans('admin/custom_fields/general.custom_fields') }}
@parent
@stop

@section('content')

@can('view', \App\Models\CustomFieldset::class)
<div class="row">
  <div class="col-md-12">
    <div class="box box-default">

      <div class="box-header with-border">
        <h2 class="box-title">{{ trans('admin/custom_fields/general.fieldsets') }}</h2>
        <div class="box-tools pull-right">
          @can('create', \App\Models\CustomFieldset::class)
          <a href="{{ route('fieldsets.create') }}" class="btn btn-sm btn-primary" data-tooltip="true" title="{{ trans('admin/custom_fields/general.create_fieldset_title') }}">{{ trans('admin/custom_fields/general.create_fieldset') }}</a>
          @endcan
        </div>
      </div><!-- /.box-header -->

      <div class="box-body">
        <table
                data-cookie-id-table="customFieldsetsTable"
                data-id-table="customFieldsetsTable"
                data-side-pagination="client"
                data-sort-order="asc"
                data-sort-name="name"
                data-advanced-search="false"
                id="customFieldsetTable"
                data-buttons="customFieldsetButtons"
                class="table table-striped snipe-table"
                data-export-options='{
                "fileName": "export-fieldsets-{{ date('Y-m-d') }}",
                "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
          <thead>
            <tr>
              <th>{{ trans('general.name') }}</th>
              <th>{{ trans('admin/custom_fields/general.qty_fields') }}</th>
              <th>{{ trans('admin/custom_fields/general.used_by_models') }}</th>
              <th>{{ trans('table.actions') }}</th>
            </tr>
          </thead>

          @if(isset($custom_fieldsets))
          <tbody>
            @foreach($custom_fieldsets AS $fieldset)
            <tr>
              <td>
                {{ link_to_route("fieldsets.show",$fieldset->name,['fieldset' => $fieldset->id]) }}
              </td>
              <td>
                {{ $fieldset->fields->count() }}
              </td>
              <td>
                @foreach($fieldset->models as $model)
                  <a href="{{ route('models.show', $model->id) }}" class="label label-default">{{ $model->name }}{{ ($model->model_number) ? ' ('.$model->model_number.')' : '' }}</a>

                @endforeach
              </td>
              <td>

                <nobr>

                @can('update', $fieldset)

                  <a href="{{ route('fieldsets.show', ['fieldset' => $fieldset->id]) }}" data-tooltip="true" title="{{ trans('general.edit_fieldset') }}">
                    <button type="submit" class="btn btn-info btn-sm">
                      <i class="fa-regular fa-rectangle-list"></i>
                    </button>
                  </a>

                  <a href="{{ route('fieldsets.edit', $fieldset->id) }}" class="btn btn-warning btn-sm" data-tooltip="true" title="{{ trans('general.update') }}">
                    <i class="fas fa-pencil-alt" aria-hidden="true"></i>
                    <span class="sr-only">{{ trans('button.edit') }}</span>
                  </a>
                @endcan

                @can('delete', $fieldset)

                  @if($fieldset->models->count() > 0)
                  <button type="submit" class="btn btn-danger btn-sm disabled" data-tooltip="true" title="{{ trans('general.cannot_be_deleted') }}" disabled><i class="fas fa-trash"></i></button>
                  @else
                  <a type="submit" href="{{ route('fieldsets.destroy', $fieldset) }}" class="btn btn-danger btn-sm delete-asset" data-tooltip="true" title="{{ trans('general.delete') }}" data-toggle="modal" data-title="{{ trans('general.delete') }}" data-content="{{ trans('general.sure_to_delete_var', ['item' => $fieldset->name]) }}" data-icon="fa fa-trash" data-target="#dataConfirmModal" onClick="return false;"><i class="fas fa-trash"></i></a>
                  @endif
                @endcan
                  </nobr>
              </td>
            </tr>
            @endforeach
          </tbody>
          @endif
        </table>
      </div><!-- /.box-body -->
    </div><!-- /.box.box-default -->

  </div> <!-- .col-md-12-->


</div> <!-- .row-->
@endcan
@can('view', \App\Models\CustomField::class)
<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-header with-border">
        <h2 class="box-title">{{ trans('admin/custom_fields/general.custom_fields') }}</h2>
        <div class="box-tools pull-right">
          @can('create', \App\Models\CustomField::class)
          <a href="{{ route('fields.create') }}" class="btn btn-sm btn-primary" data-tooltip="true" title="{{ trans('admin/custom_fields/general.create_field_title') }}">{{ trans('admin/custom_fields/general.create_field') }}</a>
          @endcan
        </div>

      </div><!-- /.box-header -->
      <div class="box-body">

        <table
                data-cookie-id-table="customFieldsTable"
                data-id-table="customFieldsTable"
                data-side-pagination="client"
                data-sort-order="asc"
                data-sort-name="name"
                id="customFieldsTable"
                data-advanced-search="false"
                data-buttons="customFieldButtons"
                class="table table-striped snipe-table"
                data-export-options='{
                "fileName": "export-fields-{{ date('Y-m-d') }}",
                "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
          <thead>
            <tr>
              <th data-sortable="true" data-searchable="true">{{ trans('general.name') }}</th>
              <th data-sortable="true" data-searchable="true">{{ trans('admin/custom_fields/general.help_text')}}</th>
              <th data-sortable="true" data-visible="false">{{ trans('admin/custom_fields/general.db_field') }}</th>
              <th data-sortable="true" data-searchable="true">{{ trans('admin/custom_fields/general.field_format') }}</th>
              <th data-sortable="true" data-tooltip="{{ trans('admin/custom_fields/general.encrypted') }}"><i
                        class="fa fa-lock" aria-hidden="true"></i>
                <span class="hidden-xs hidden-sm hidden-md hidden-lg">{{ trans('admin/custom_fields/general.encrypted') }}</span>
              </th>
              <th data-sortable="true" class="text-center"
                  data-tooltip="{{ trans('admin/custom_fields/general.show_in_listview_short') }}"><i class="fa fa-list"
                                                                                                      aria-hidden="true"></i>
                <span class="hidden-xs hidden-sm hidden-md hidden-lg">{{ trans('admin/custom_fields/general.show_in_listview_short') }}</span>
              </th>
              <th data-visible="false" data-sortable="true" class="text-center"
                  data-tooltip="{{ trans('admin/custom_fields/general.display_in_user_view_table') }}"><i
                        class="fa fa-eye"
                        aria-hidden="true"><span
                          class="sr-only">{{ trans('admin/custom_fields/general.display_in_user_view_table') }}</span></i>
              </th>
              <th data-sortable="true" data-searchable="false" class="text-center"
                  data-tooltip="{{ trans('admin/custom_fields/general.show_in_email_short') }}"><i
                        class="fa fa-envelope" aria-hidden="true"><span
                          class="sr-only">{{ trans('admin/custom_fields/general.show_in_email_short') }}</span></i></th>

              <th data-sortable="true" data-searchable="false" class="text-center"
                  data-tooltip="{{ trans('admin/custom_fields/general.show_in_requestable_list_short') }}"><i
                        class="fa fa-laptop fa-fw" aria-hidden="true"><span
                          class="sr-only">{{ trans('admin/custom_fields/general.show_in_requestable_list_short') }}</span></i>
              </th>

              <th data-sortable="true" data-searchable="false" class="text-center"
                  data-tooltip="{{ trans('admin/custom_fields/general.unique') }}"><i
                        class="fa-solid fa-fingerprint"><span
                          class="sr-only">{{ trans('admin/custom_fields/general.unique') }}</span></i></th>


              <th data-sortable="true" data-visible="false" data-searchable="false" class="text-center"
                  data-tooltip="{{ trans('admin/custom_fields/general.display_checkin') }}">
                <x-icon type="checkin" />
                <span class="sr-only">
                    {{ trans('admin/custom_fields/general.display_checkin') }}
                  </span>
              </th>

              <th data-sortable="true" data-visible="false" data-searchable="false" class="text-center"
                  data-tooltip="{{ trans('admin/custom_fields/general.display_checkout') }}">
                <x-icon type="checkout" />
                <span class="sr-only">
                    {{ trans('admin/custom_fields/general.display_checkout') }}
                  </span>
              </th>

              <th data-sortable="true" data-visible="false" data-searchable="false" class="text-center"
                  data-tooltip="{{ trans('admin/custom_fields/general.display_audit') }}">
                <x-icon type="audit" />
                <span class="sr-only">
                    {{ trans('admin/custom_fields/general.display_audit') }}
                  </span>
              </th>


              <th data-sortable="true" data-searchable="true" class="text-center">{{ trans('admin/custom_fields/general.field_element_short') }}</th>


              <th data-searchable="true">{{ trans('admin/custom_fields/general.fieldsets') }}</th>
              <th>{{ trans('button.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($custom_fields as $field)
            <tr>
              <td>{{ $field->name }}</td>
              <td>{{ $field->help_text }}</td>
              <td>
                 <code>{{ $field->convertUnicodeDbSlug() }}</code>
                @if ($field->convertUnicodeDbSlug()!=$field->db_column)
                  <br><i class="fas fa-exclamation-triangle text-danger"></i>
                  {!! trans('admin/custom_fields/general.db_convert_warning',['db_column' => $field->db_column, 'expected' => $field->convertUnicodeDbSlug()]) !!}
                @endif
              </td>
              <td>{{ $field->format }}</td>
              <td class="text-center">{!!  ($field->field_encrypted=='1' ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>') !!}</td>
              <td class="text-center">{!!  ($field->show_in_listview=='1' ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>') !!}</td>
              <td class="text-center">{!!  ($field->display_in_user_view=='1' ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>') !!}</td>
              <td class="text-center">{!! ($field->show_in_email=='1') ? '<i class="fas fa-check text-success" aria-hidden="true"><span class="sr-only">'.trans('general.yes').'</span></i>' : '<i class="fas fa-times text-danger" aria-hidden="true"><span class="sr-only">'.trans('general.no').'</span></i>'  !!}</td>
              <td class="text-center">{!! ($field->show_in_requestable_list=='1') ? '<i class="fas fa-check text-success" aria-hidden="true"><span class="sr-only">'.trans('general.yes').'</span></i>' : '<i class="fas fa-times text-danger" aria-hidden="true"><span class="sr-only">'.trans('general.no').'</span></i>'  !!}</td>
              <td class="text-center">{!! ($field->is_unique=='1') ? '<i class="fas fa-check text-success" aria-hidden="true"><span class="sr-only">'.trans('general.yes').'</span></i>' : '<i class="fas fa-times text-danger" aria-hidden="true"><span class="sr-only">'.trans('general.no').'</span></i>'  !!}</td>
              <td class="text-center">{!! ($field->display_checkin=='1') ? '<i class="fas fa-check text-success" aria-hidden="true"><span class="sr-only">'.trans('general.yes').'</span></i>' : '<i class="fas fa-times text-danger" aria-hidden="true"><span class="sr-only">'.trans('general.no').'</span></i>'  !!}</td>
              <td class="text-center">{!! ($field->display_checkout=='1') ? '<i class="fas fa-check text-success" aria-hidden="true"><span class="sr-only">'.trans('general.yes').'</span></i>' : '<i class="fas fa-times text-danger" aria-hidden="true"><span class="sr-only">'.trans('general.no').'</span></i>'  !!}</td>
              <td class="text-center">{!! ($field->display_audit=='1') ? '<i class="fas fa-check text-success" aria-hidden="true"><span class="sr-only">'.trans('general.yes').'</span></i>' : '<i class="fas fa-times text-danger" aria-hidden="true"><span class="sr-only">'.trans('general.no').'</span></i>'  !!}</td>
              <td>{{ $field->element }}</td>
              <td>
                @foreach($field->fieldset as $fieldset)
                  <a href="{{ route('fieldsets.show', $fieldset->id) }}" class="label label-default">{{ $fieldset->name }}</a>
                @endforeach
              </td>
              <td>
                <nobr>
                  @can('update', $field)
                    <a href="{{ route('fields.edit', $field->id) }}" class="btn btn-warning btn-sm" data-tooltip="true" title="{{ trans('general.update') }}">
                      <i class="fas fa-pencil-alt" aria-hidden="true"></i>
                      <span class="sr-only">{{ trans('button.edit') }}</span>
                    </a>
                @endcan

                @can('delete', $field)

                  @if ($field->fieldset->count() > 0)
                    <button type="submit" class="btn btn-danger btn-sm disabled" data-tooltip="true" title="{{ trans('general.cannot_be_deleted') }}" disabled>
                      <i class="fas fa-trash" aria-hidden="true"></i>
                      <span class="sr-only">{{ trans('button.delete') }}</span>
                    </button>
                  @else
                    <a href="{{ route('fields.destroy', $field) }}" class="btn btn-danger btn-sm delete-asset" data-tooltip="true" title="{{ trans('general.delete') }}" data-toggle="modal" data-title="{{ trans('general.delete') }}" data-content="{{ trans('general.sure_to_delete_var', ['item' => $field->name]) }}" data-target="#dataConfirmModal" data-icon="fa fa-trash" onClick="return false;">
                      <i class="fas fa-trash" aria-hidden="true"></i>
                      <span class="sr-only">{{ trans('button.delete') }}</span>
                    </a>
                  @endif

                @endcan
                </nobr>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div><!-- /.box-body -->
    </div><!-- /.box -->
  </div> <!-- /.col-md-9-->
</div>
@endcan

@stop
@section('moar_scripts')
  @include ('partials.bootstrap-table')
  <script>
    $(function () {
      $('th').each(function (index, raw_element) {
        var element = $(raw_element);
        if (element.data('tooltip')) {
          element.tooltip({container: 'body', title: element.data('tooltip')})
        }
      });
    })
  </script>
@stop
