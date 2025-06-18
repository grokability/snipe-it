@extends('layouts/basic')

{{-- Page title --}}
@section('title')
404
@parent
@stop

{{-- Page content --}}

@section('content')



<div class="row">
  <div class="col-md-8 col-md-offset-2">

    <div style="padding-top: 200px">
      <img src="{{ config('app.url') }}/img/sad-panda.png" style="width: 200px; height: 200px;" class="pull-left" alt="Sad Panda cartoon" />
            <div class="error-content">
              <h2><x-icon type="warning" class="text-yellow" /> 404 Page not found.</h2>
              <p>
                Sad panda. We could not find the page you were looking for.
                You should maybe <a href="{{ config('app.url') }}">return to the dashboard</a>.
              </p>

    </div>
</div>
@stop
