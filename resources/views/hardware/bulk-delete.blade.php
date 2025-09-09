@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/hardware/form.bulk_delete') }}
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
    <p>{{ trans('admin/hardware/form.bulk_delete_help') }}</p>
    <form class="form-horizontal" method="post" action="{{ route('hardware/bulkdelete') }}" autocomplete="off" role="form">
      {{csrf_field()}}
      <div class="box box-default">
        <div class="box-header with-border">
          <h2 class="box-title" style="color: red">{{ trans('admin/hardware/form.bulk_delete_warn', ['asset_count' => count($assets)]) }}</h2>
        </div>

        <div class="box-body">
          <table class="table table-striped table-condensed">
            <thead>
              <tr>
                <th></th>
                <th>{{ trans('admin/hardware/table.id') }}</th>
                <th>{{ trans('general.asset_name') }}</th>
                <th>{{ trans('admin/hardware/table.location')}}</th>
                <th>{{ trans('admin/hardware/table.assigned_to') }}</th>
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
                  {{ $asset->location->display_name }}
                  @elseif($asset->rtd_location)
                  {{ $asset->defaultLoc->display_name }}
                  @endif
                </td>
                <td>
                  @if ($asset->assigned)
                    {{ $asset->assigned->display_name }}
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div><!-- /.box-body -->

        <div class="box-footer text-right">
          <a class="btn btn-link" href="{{ URL::previous() }}">
            {{ trans('button.cancel') }}
          </a>
          <button type="submit" class="btn btn-success" id="submit-button">
            <x-icon type="checkmark" /> {{ trans('button.delete') }}
          </button>
        </div><!-- /.box-footer -->
      </div><!-- /.box -->
    </form>
  </div> <!-- .col-md-12-->
</div><!--.row-->
@stop
