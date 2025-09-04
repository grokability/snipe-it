@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/hardware/form.bulk_restore') }}
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
  <div class="col-md-12">
    <p>{{ trans('admin/hardware/form.bulk_restore_help') }}</p>
    <form class="form-horizontal" method="post" action="{{ route('hardware/bulkrestore') }}" autocomplete="off" role="form">
      {{csrf_field()}}
      <div class="box box-default">
        <div class="box-header with-border">
          <h2 class="box-title" style="color: red">{{ trans('admin/hardware/form.bulk_restore_warn', ['asset_count' => count($assets)]) }}</h2>
        </div>

        <div class="box-body">
          <table class="table table-striped table-condensed">
            <thead>
              <tr>
                <td></td>
                <td>{{ trans('admin/hardware/table.id') }}</td>
                <td>{{ trans('admin/hardware/form.name') }}</td>
                <td>{{ trans('admin/hardware/table.location')}}</td>
              </tr>
            </thead>
            <tbody>
              @foreach ($assets as $asset)
              <tr>
                <td><input type="checkbox" name="ids[]" value="{{ $asset->id }}" checked="checked"></td>
                <td>{{ $asset->id }}</td>
                <td>{{ $asset->display_name }}</td>
                <td>
                  @if ($asset->location)
                  {{ $asset->location->name }}
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div><!-- /.box-body -->

        <div class="box-footer text-right">
          <a class="btn btn-link" href="{{ URL::previous() }}" method="post" enctype="multipart/form-data">{{ trans('button.cancel') }}</a>
          <button type="submit" class="btn btn-success" id="submit-button"><x-icon type="checkmark" /> {{ trans('button.restore') }}</button>
        </div><!-- /.box-footer -->
      </div><!-- /.box -->
    </form>
  </div> <!-- .col-md-12-->
</div><!--.row-->
@stop
