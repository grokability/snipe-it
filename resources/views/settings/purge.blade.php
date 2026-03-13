@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.purge') }}
@parent
@stop

@section('header_right')
<a href="{{ URL::previous() }}" class="btn btn-primary pull-right">
  {{ trans('general.back') }}</a>
@stop

{{-- Page content --}}
@section('content')
<div class="row">
	<div class="col-md-9">
		<div class="box box-default">
			<div class="box-body">
				{!! nl2br($output) !!}
			</div>
		</div>
	</div>
</div>
@stop
