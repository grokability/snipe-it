@extends('layouts/setup')
{{-- Page title --}}
@section('title')
{{ trans('admin/settings/general.setup_migrations') }}
@parent
@stop

{{-- Page content --}}
@section('content')
<div class="col-lg-12" style="padding-top: 20px;">
    @if (trim($output)=='Nothing to migrate.')
    <div class="col-md-12">
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            {{ trans('general.setup_no_migrations') }}
        </div>
    </div>

    @endif

    <p>{{ trans('general.setup_migration_output') }} </p>
    <pre>{{ $output }}</pre>
</div>
@stop

@section('button')

    <a href="{{ route('setup.user') }}" class="btn btn-primary">
        {{ trans('general.setup_next') }}:
        {{ trans('general.setup_create_admin') }}
        <i class="fa-solid fa-angles-right"></i>
    </a>
@parent
@stop
