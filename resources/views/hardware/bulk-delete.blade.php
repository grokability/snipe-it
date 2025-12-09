@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.bulk_delete') }}
@parent
@stop


{{-- Page content --}}
@section('content')
<div class="row">
  <!-- left column -->
  <div class="col-md-8 col-md-offset-2">
    <p>{{ trans('admin/hardware/form.bulk_delete_help') }}</p>
    <form class="form-horizontal" method="post" action="{{ route('hardware.bulkdelete.store') }}" autocomplete="off" role="form">
      {{csrf_field()}}
      <div class="box box-default">

        <div class="box-body">

            <div class="callout callout-warning">
                <i class="fas fa-exclamation-triangle"></i>
                {{ trans('admin/hardware/form.bulk_delete_warn', ['asset_count' => count($assets)]) }}
            </div>

          <table class="table table-striped">

              <tr>
                <th></th>
                <th>{{ trans('admin/hardware/table.id') }}</th>
                <th>{{ trans('general.asset_name') }}</th>
                <th>{{ trans('admin/hardware/table.location')}}</th>
                <th>{{ trans('admin/hardware/table.assigned_to') }}</th>
              </tr>

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
          <a class="btn btn-link pull-left" href="{{ URL::previous() }}">
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
