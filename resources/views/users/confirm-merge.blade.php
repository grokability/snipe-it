@extends('layouts/default')
{{-- Page title --}}
@section('title')
    {!! trans('general.merge_users') !!}
    @parent
@stop

{{-- Page content --}}
@section('content')
    
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="box box-default">
                <form class="form-horizontal" role="form" method="post" action="{{ route('users.merge.save') }}">
                    <div class="box-body">

                        <div class="col-md-12">
                            <p style="padding:10px">
                                {{ trans('general.merge_information', array('count' => count($users))) }}
                            </p>
                        </div>
                        <!-- CSRF Token -->
                        {{csrf_field()}}
                        <div class="row">
                            <div class="col-md-12">
                                <div class="callout callout-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    {{ trans('general.warning_merge_information') }}
                                </div>
                            </div>
                        </div>

                        @if (config('app.lock_passwords'))
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="callout callout-info">
                                        <p>{{ trans('general.feature_disabled') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="display table table-hover">
                                    <thead>
                                    <tr>
                                        <th class="col-md-3">{{ trans('general.name') }}</th>
                                        <th class="col-md-3">{{ trans('general.email') }}</th>
                                        <th class="col-md-3">{{ trans('general.username') }}</th>
                                        <th class="col-md-3">{{ trans('general.employee_number') }}</th>
                                        <th class="col-md-3">{{ trans('general.groups') }}</th>
                                        <th class="col-md-1 text-right">
                                            <i class="fas fa-barcode fa-fw" aria-hidden="true" style="font-size: 17px;"></i>
                                            <span class="sr-only">{{ trans('general.assets') }}</span>
                                        </th>
                                        <th class="col-md-1 text-right">
                                            <i class="far fa-keyboard fa-fw" aria-hidden="true" style="font-size: 17px;"></i>
                                            <span class="sr-only">{{ trans('general.accessories') }}</span>
                                        </th>
                                        <th class="col-md-1 text-right">
                                            <i class="far fa-save fa-fw" aria-hidden="true" style="font-size: 17px;"></i>
                                            <span class="sr-only">{{ trans('general.licenses') }}</span>
                                        </th>
                                        <th class="col-md-1 text-right">
                                            <i class="fas fa-tint fa-fw" aria-hidden="true" style="font-size: 17px;"></i>
                                            <span class="sr-only">{{ trans('general.consumables') }}</span>
                                        </th>
                                        <th class="col-md-1 text-right">
                                            <i class="fas fa-paperclip fa-fw" aria-hidden="true" style="font-size: 17px;"></i>
                                            <span class="sr-only">{{ trans('general.files') }}</span>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($users as $user)
                                        <tr {!! ($user->isSuperUser() ? ' class="danger"':'') !!}>
                                            <td>
                                                <label class="form-control" for="{{ $user->id }}"><input type="radio" name="merge_into_id" id="{{ $user->id }}" value="{{ $user->id }}">  {{ $user->display_name }}</label>
                                            </td>
                                            <td>
                                                {{ $user->email }}

                                            </td>
                                            <td>
                                                {{ $user->username }}
                                            </td>
                                            <td>
                                                {{ $user->employee_num }}
                                            </td>

                                            <td>
                                                @foreach ($user->groups as $group)
                                                    <a href=" {{ route('groups.update', $group->id) }}" class="label  label-default">
                                                        {{ $group->name  }}
                                                    </a>&nbsp;
                                                @endforeach
                                            </td>
                                            <td class="text-right">
                                                {{ number_format($user->assets->count())  }}
                                            </td>
                                            <td class="text-right">
                                                {{ number_format($user->accessories->count())  }}
                                            </td>
                                            <td class="text-right">
                                                {{ number_format($user->licenses->count())  }}
                                            </td>
                                            <td class="text-right">
                                                {{ number_format($user->consumables->count())  }}
                                            </td>
                                            <td class="text-right">
                                                {{ number_format($user->uploads->count())  }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>

                                </table>
                            </div> <!--/table-responsive-->
                        </div><!--/col-md-12-->
                    </div> <!--/box-body-->
                    <div class="box-footer text-right">
                        <a class="btn btn-link pull-left" href="{{ URL::previous() }}">{{ trans('button.cancel') }}</a>
                        <button type="submit" class="btn btn-success"{{ (config('app.lock_passwords') ? ' disabled' : '') }} disabled><x-icon type="checkmark" /> {{ trans('button.submit') }}</button>
                    </div><!-- /.box-footer -->

                    @foreach ($users as $user)
                        <input type="hidden" name="ids_to_merge[]" value="{{ $user->id }}">
                    @endforeach

                </form>
            </div>
        </div>
    </div>

@stop

@section('moar_scripts')

    @if (!(config('app.lock_passwords')))

    <script>

        $('button[type="submit"]').prop("disabled", true);

        $("input[type='radio']").click(function () {
            if($(this).is(':enabled')) {
                $("button[type='submit']").prop("disabled", false);
                $("button[type='submit']").removeAttr("disabled");
            }
        });




    </script>
    @endif

@stop