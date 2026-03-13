@extends('layouts/basic')

{{-- Page title --}}
@section('title')
  {{ trans('general.maintenance_mode_title') }}
@parent
@stop

{{-- Page content --}}

@section('content')



<div class="container">
<div class="row">
  <div class="col-md-9 col-md-offset-1">

    <div class="box box-warning">

      <div class="box-header with-border">
        <h1 class="box-title">
          <x-icon type="warning" class="text-orange" />
          {{ trans('general.maintenance_mode_title') }}
        </h1>
      </div><!-- /.box-header -->

      <div class="box-body">
        <div class="col-md-12">

          <div class="col-md-2">
            <img src="{{ config('app.url') }}/img/sad-panda.png" class="pull-right" style="width: 140px; height: 140px;">
          </div>
          <div class="alert alert-warning fade in">
            <h2> {{ trans('general.maintenance_mode') }}</h2>
          </div>

        </div> <!-- /.div -->
      </div><!-- /.box-body -->

    </div>

</div>
@stop
