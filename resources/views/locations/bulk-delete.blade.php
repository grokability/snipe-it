@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.bulk.delete.header', ['object_type' => trans_choice('general.location_plural', $valid_count)]) }}
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
            <form class="form-horizontal" method="post" action="{{ route('locations.bulkdelete.store') }}" autocomplete="off" role="form">
                {{csrf_field()}}
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h2 class="box-title" style="color: red">{{ trans_choice('general.bulk.delete.warn', $valid_count, ['count' => $valid_count,'object_type' => trans_choice('general.location_plural', $valid_count)]) }}</h2>
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
                                <td class="col-md-10">{{ trans('general.name') }}</td>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($locations as $location)
                                <tr{!!  (($location->assets_count > 0 ) ? ' class="danger"' : '') !!}>
                                    <td>
                                        <input type="checkbox" name="ids[]" class="{  ($location->isDeletable() ? '' : ' disabled') }}" value="{{ $location->id }}" {!!  (($location->isDeletable()) ? ' checked="checked"' : ' disabled') !!}>
                                    </td>
                                    <td>{{ $location->name }}</td>

                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div><!-- /.box-body -->

                    <div class="box-footer text-right">
                        <a class="btn btn-link pull-left" href="{{ URL::previous() }}">{{ trans('button.cancel') }}</a>
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
