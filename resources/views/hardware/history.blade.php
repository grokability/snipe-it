@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.import-history') }}
    @parent
@stop

@section('header_right')
    <a href="{{ route('hardware.index') }}" class="btn btn-default"> {{ trans('general.back') }}</a>
@stop

{{-- Page content --}}
@section('content')


    @if (isset($status))

        @if (count($status['error']) > 0)
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="box box-default">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle faa-pulse animated"></i>
                            <strong>{{ count($status['error']) }} {{ trans('admin/hardware/general.error_messages') }}</strong> </strong>
                            {{ trans('admin/hardware/general.alert_details') }}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="box box-default">
                    <div class="alert alert-success">
                        <i class="fas fa-check faa-pulse animated"></i>
                        <strong>{{ count($status['success']) }} {{ trans('admin/hardware/general.success_messages') }} </strong>
                        {{ trans('admin/hardware/general.alert_details') }}
                    </div>
                </div>
            </div>
        </div>

        @endif

        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="box box-default">
                    <div class="box-body">
                        <form class="form-horizontal" role="form" method="post" enctype="multipart/form-data" action="">
                            <!-- CSRF Token -->
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" />

                        <div class="col-md-12">
                            @if (Session::get('message'))
                                <p class="alert-danger">
                                    {{ trans('admin/hardware/general.csv_error') }}<br />
                                    {{ Session::get('message') }}
                                </p>
                            @endif
                        </div>
                        <div class="col-md-12" style="padding: 10px;">
                            {!! trans('admin/hardware/general.import_text') !!}
                        </div>

                        <div class="col-md-12" style="padding: 10px;">
                            <div class="form-group">
                                <div class="col-md-9 col-md-offset-2">

                                    <label class="btn btn-default" aria-hidden="true">
                                        {{ trans('button.select_file')  }}
                                        <input type="file" name="user_import_csv" class="js-uploadFile" id="uploadFile" data-maxsize="user_import_csv" accept=".csv,.xls,.xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,text/plain" style="display:none; max-width: 90%" aria-label="user_import_csv" aria-hidden="true"{{ (config('app.lock_passwords')===true) ? ' disabled' : ''}}>
                                    </label>
                                    <span class="label label-default" id="uploadFile-info" style="font-size: 15px;"></span>

                                    @if (config('app.lock_passwords')===true)
                                        <p class="text-warning"><i class="fas fa-lock"></i>
                                            {{ trans('general.feature_disabled') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <!-- Match firstname.lastname -->
                            <div class="form-group">
                                <div class="col-sm-10 col-md-offset-2">
                                    <label class="form-control">
                                        <input type="checkbox" name="match_firstnamelastname" value="1" @checked(old('match_firstnamelastname'))>
                                        <!-- enclosing span needed for the checkbox CSS to work properly, since there's HTML in the label text -->
                                        <span>
                                            {!!  trans('admin/hardware/general.csv_import_match_f-l') !!}
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <!-- Match flastname -->
                            <div class="form-group">
                                <div class="col-sm-10 col-md-offset-2">
                                    <label class="form-control">
                                        <input type="checkbox" name="match_flastname" value="1" @checked(old('match_flastname'))>
                                        <!-- enclosing span needed for the checkbox CSS to work properly, since there's HTML in the label text -->
                                        <span>
                                        {!!  trans('admin/hardware/general.csv_import_match_initial_last') !!}
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <!-- Match firstname -->
                            <div class="form-group">
                                <div class="col-sm-10 col-md-offset-2">
                                    <label class="form-control">
                                        <input type="checkbox" name="match_firstname" value="1" @checked(old('match_firstname'))>
                                        <!-- enclosing span needed for the checkbox CSS to work properly, since there's HTML in the label text -->
                                        <span>
                                            {!! trans('admin/hardware/general.csv_import_match_first') !!}
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <!-- Match email -->
                            <div class="form-group">
                                <div class="col-sm-10 col-md-offset-2">
                                    <label class="form-control">
                                        <input type="checkbox" name="match_email" value="1" @checked(old('match_email'))>
                                        <!-- enclosing span needed for the checkbox CSS to work properly, since there's HTML in the label text -->
                                        <span>
                                        {!!  trans('admin/hardware/general.csv_import_match_email') !!}
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <!-- Match username -->
                            <div class="form-group">
                                <div class="col-sm-10 col-md-offset-2">
                                    <label class="form-control">
                                        <input type="checkbox" name="match_username" value="1" @checked(old('match_username'))>
                                        <!-- enclosing span needed for the checkbox CSS to work properly, since there's HTML in the label text -->
                                        <span>
                                            {!!  trans('admin/hardware/general.csv_import_match_username') !!}
                                        </span>
                                    </label>
                                </div>
                            </div>


                        <!-- Form Actions -->
                        <div class="box-footer text-right">
                            @if (config('app.lock_passwords')===true)
                                <div class="col-md-12">
                                    <div class="callout callout-info">
                                        {{ trans('general.feature_disabled') }}
                                    </div>
                                </div>

                            @else
                                <button type="submit" class="btn btn-default">{{ trans('button.submit') }}</button>
                            @endif
                        </div>

            </form>

        </div>

@if (isset($status))


@if (count($status['error']) > 0)
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h2 class="box-title"> {{ count($status['error']) }} {{ trans('admin/hardware/general.error_messages') }} </h2>
            </div>
            <div class="box-body">
                <div style="height : 400px; overflow : auto;">
                    <table class="table">
                        @for ($x = 0; $x < count($status['error']); $x++)
                            @foreach($status['error'][$x] as $object_type => $message)
                            <tr class="danger">
                                <td><strong>{{ ucwords($object_type)  }} {{ key($message)  }}:</strong></td>
                                <td>{{ $message[key($message)]['msg']  }}</td>
                            </tr>
                            @endforeach
                        @endfor
                    </table>
               </div>
            </div>
        </div>
    </div>
</div>

@endif

@if (count($status['success']) > 0)
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h2 class="box-title"> {{ count($status['success']) }} {{ trans('admin/hardware/general.success_messages') }} </h2>
            </div>
            <div class="box-body">
                <div style="height : 400px; overflow : auto;">
                    <table class="table">
                        @for ($x = 0; $x < count($status['success']); $x++)
                            @foreach($status['success'][$x] as $object_type => $message)
                                <tr class="success">
                                    <td><strong>{{ ucwords($object_type)  }} {{ key($message)  }}:</strong></td>
                                    <td>{{ $message[key($message)]['msg']  }}</td>
                                </tr>
                            @endforeach
                        @endfor
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endif

</div>
        </div>
    </div>

@stop
