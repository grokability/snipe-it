@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/models/general.bulk_delete') }}
    @parent
@stop

@section('header_right')
    <a href="{{ URL::previous() }}" class="btn btn-primary pull-right">
        {{ trans('general.back') }}</a>
@stop

{{-- Page content --}}
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-8 col-md-offset-2">
            <p>{{ trans('admin/models/general.bulk_delete_help') }}</p>
            <form class="form-horizontal" method="post" action="{{ route('models.bulkdelete.store') }}" autocomplete="off" role="form">
                {{csrf_field()}}
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h2 class="box-title" style="color: red">{{ trans_choice('admin/models/general.bulk_delete_warn', $valid_count, ['model_count' => $valid_count]) }}</h2>
                    </div>

                    <div class="box-body">
                        <table class="table table-striped table-condensed">
                            <thead>
                            <tr>
                                <td class="col-md-1">
                                    <label>
                                        <input type="checkbox" id="checkAll" checked="checked">
                                    </label>

                                </td>
                                <td class="col-md-1"><i class="fas fa-barcode" aria-hidden="true"></i></td>
                                <td class="col-md-10">{{ trans('general.name') }}</td>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($models as $model)
                                <tr{!!  (($model->assets_count > 0 ) ? ' class="danger"' : '') !!}>
                                    <td>
                                        <input type="checkbox" name="ids[]" class="{  (($model->assets_count == 0) ? '' : ' disabled') }}" value="{{ $model->id }}" {!!  (($model->assets_count == 0) ? ' checked="checked"' : ' disabled') !!}>
                                    </td>
                                    <td>{{ $model->assets_count }}</td>
                                    <td>{{ $model->name }}</td>

                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div><!-- /.box-body -->

                    <div class="box-footer text-right">
                        <a class="btn btn-link pull-left" href="{{ URL::previous() }}" method="post" enctype="multipart/form-data">{{ trans('button.cancel') }}</a>
                        <button type="submit" class="btn btn-success" id="submit-button"><x-icon type="checkmark" /> {{ trans('general.delete') }}</button>
                    </div><!-- /.box-footer -->
                </div><!-- /.box -->
            </form>
        </div> <!-- .col-md-12-->
    </div><!--.row-->
@stop
@section('moar_scripts')
    <script>


        $("#checkAll").change(function () {
            $("input:checkbox").prop('checked', $(this).prop("checked"));
        });

    </script>
@stop
