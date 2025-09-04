@extends('layouts.default')

{{-- Page title --}}
@section('title')
    @if ($item->id)
        {{ $updateText }}
    @else
        {{ $createText }}
    @endif
@parent
@stop

@section('header_right')
<a href="{{ URL::previous() }}" class="btn btn-primary pull-right">
    {{ trans('general.back') }}</a>
@stop



{{-- Page content --}}

@section('content')

<!-- row -->
<div class="row">
    <!-- col-md-8 -->
    <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-sm-12 col-sm-offset-0">

        <form id="create-form" class="form-horizontal" method="post" action="{{ (isset($formAction)) ? $formAction : \Request::url()  }}" autocomplete="off" role="form" enctype="multipart/form-data">

        <!-- box -->
        <div class="box box-default">
            <!-- box-header -->
            <div class="box-header with-border">

                @if ((isset($topSubmit) && ($topSubmit=='true')) || (isset($item->id)))

                <div class="col-md-12 box-title text-right" style="padding: 0px; margin: 0px;">
                        <div class="col-md-9 text-left">
                            @if ($item->id)
                                <h2 class="box-title" style="padding-top: 8px; padding-bottom: 7px;">
                                    {{ $item->display_name }}
                                </h2>
                            @endif
                        </div>
                        @if (isset($topSubmit) && ($topSubmit=='true'))
                        <div class="col-md-3 text-right" style="padding-right: 10px;">
                            <button type="submit" class="btn btn-primary pull-right" name="submit">
                                <x-icon type="checkmark" />
                                {{ trans('general.save') }}
                            </button>
                        </div>
                        @endif
                </div>
            </div><!-- /.box-header -->
            @endif

            <!-- box-body -->
            <div class="box-body">

                <div style="padding-top: 30px;">
                    @if ($item->id)
                    {{ method_field('PUT') }}
                    @endif

                    <!-- CSRF Token -->
                    {{ csrf_field() }}
                    @yield('inputFields')
                        <x-redirect_submit_options
                                :index_route="$index_route ?? null"
                                :button_label="trans('general.save')"
                                :options="$options ?? []"
                        />
                </div>

            </div> <!-- ./box-body -->
        </div> <!-- box -->
        </form>
    </div> <!-- col-md-8 -->

</div><!-- ./row -->

@stop
