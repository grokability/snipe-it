@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.purge') }}
@parent
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
