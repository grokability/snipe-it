{{-- Page title --}}
@section('title')
{{ trans('admin/settings/general.webhook_title') }}
@parent
@stop

@section('header_right')
<a href="{{ route('settings.index') }}" class="btn btn-primary"> {{ trans('general.back') }}</a>
@stop


{{-- Page content --}}
@section('content')

<div><!-- livewire div - do not remove -->
    <form class="form-horizontal" role="form" wire:submit="submit">
        {{csrf_field()}}
        @if (session()->has('warning'))
            <div class="alert alert-warning">
                {!! session('warning') !!}
                @php
                    session()->forget('warning'); // Clear the session flash immediately
                @endphp
            </div>
        @endif
        <div class="row">

            <div class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">

                <div class="panel box box-default">

                    <div class="box-header with-border">
                        <h2 class="box-title">
                            <i class="{{$webhook_icon}}"></i> {{ trans('admin/settings/general.webhook', ['app' => $webhook_name] ) }}
                        </h2>
                    </div>

                <div class="box-body">
                    @if($webhook_selected != 'general')
                    <div class="col-md-12">
                            <p>
                                {!! trans('admin/settings/general.webhook_integration_help',array('webhook_link' => $webhook_link, 'app' => $webhook_name)) !!}
                            </p>
                        <br>
                    </div>
                    @endif

                    <div class="col-md-12" style="border-top: 0px;">

                        @if(session()->has('success'))
                            <div class="alert alert-success fade in">
                                {{session('success')}}
                            </div>
                        @endif
                        @if(session()->has('error'))
                            <div class="alert alert-danger fade in">
                                {{session('error')}}
                            </div>
                        @endif

                        <div class="form-group">
                            <div class="col-md-2">
                                <label for="webhook_selected">
                                    {{ trans('general.integration_option') }}

								</label>
                            </div>
                            <div class="col-md-9 required" wire:ignore>
                                <x-input.select
                                    name="webhook_selected"
                                    id="select2"
                                    :options="['slack' => trans('admin/settings/general.slack'), 'general' => trans('admin/settings/general.general_webhook'),'google' => trans('admin/settings/general.google_workspaces'), 'microsoft' => trans('admin/settings/general.ms_teams')]"
                                    :selected="old('webhook_selected', $webhook_selected)"
                                    :disabled="Helper::isDemoMode()"
                                    :for-livewire="true"
                                    data-minimum-results-for-search="-1"
                                    class="form-control"
                                    style="width:100%"
                                    aria-label="webhook_selected"
                                />
                            </div>
                        </div>

                        @if (Helper::isDemoMode())
                            @include('partials.forms.demo-mode')
                        @endif

                        <!--Webhook endpoint-->
                        <div class="form-group{{ $errors->has('webhook_endpoint') ? ' error' : '' }}">
                            <div class="col-md-2">
                                <label for="webhook_endpoint">{{ trans('admin/settings/general.webhook_endpoint',['app' => $webhook_name ]) }}</label>
                            </div>
                            <div class="col-md-9 required">
                                    <input type="url" wire:model.blur="webhook_endpoint" class="form-control" placeholder="{{$webhook_placeholder}}" value="{{old('webhook_endpoint', $webhook_endpoint)}}"{{ Helper::isDemoMode() ? ' disabled' : ''}}>
                                {!! $errors->first('webhook_endpoint', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                            </div>
                        </div>

                        @if (Helper::isDemoMode())
                            @include('partials.forms.demo-mode')
                        @endif


                        <!-- Webhook channel -->
                        @if($webhook_selected != 'microsoft' && $webhook_selected!= 'google')
                            <div class="form-group{{ $errors->has('webhook_channel') ? ' error' : '' }}">
                                <div class="col-md-2">
                                    <label for="webhook_channel">{{ trans('admin/settings/general.webhook_channel',['app' => $webhook_name ]) }}</label>
                                </div>
                                <div class="col-md-9 required">
                                        <input type="text" wire:model.blur="webhook_channel" class="form-control" placeholder="#IT-Ops" value="{{ old('webhook_channel', $webhook_channel) }}"{{ Helper::isDemoMode() ? ' disabled' : ''}}>

                                    {!! $errors->first('webhook_channel', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                </div>
                            </div>
                        @endif

                        @if (Helper::isDemoMode())
                            @include('partials.forms.demo-mode')
                        @endif

                        <!-- Webhook botname -->
                        @if($webhook_selected != 'microsoft' && $webhook_selected != 'google')
                            <div class="form-group{{ $errors->has('webhook_botname') ? ' error' : '' }}">
                                <div class="col-md-2">
                                    <label for="webhook_botname">{{ trans('admin/settings/general.webhook_botname',['app' => $webhook_name ]) }}</label>
                                </div>
                                <div class="col-md-9">
                                        <input type="text" wire:model.blur="webhook_botname" class='form-control' placeholder="Snipe-Bot" {{ old('webhook_botname', $webhook_botname)}}{{ Helper::isDemoMode() ? ' disabled' : ''}}>
                                    {!! $errors->first('webhook_botname', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                </div><!--col-md-10-->
                            </div>
                        @endif
                        @if (!Helper::isDemoMode())
                            @include('partials.forms.demo-mode')
                        @endif

                        <!--Webhook Integration Test-->

                            @if($webhook_endpoint != null && $webhook_channel != null)
                                <div class="form-group">
                                    <div class="col-md-offset-2 col-md-9">
                                        <a href="#" wire:click.prevent="{{$webhook_test}}"
                                           class="btn btn-default btn-sm pull-left">
                                            <i class="{{$webhook_icon}}" aria-hidden="true"></i>
                                                {!! trans('admin/settings/general.webhook_test',['app' => ucwords($webhook_selected) ]) !!}
                                        </a>
                                        <div wire:loading>
                                            <span style="padding-left: 5px; font-size: 20px">
                                                <i class="fas fa-spinner fa-spin" aria-hidden="true"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                    </div><!-- /.col-md-12 -->
               </div><!-- /.box-body -->

                <div class="box-footer">
                    <div class="text-right col-md-12">

                        <button type="reset" wire:click.prevent="clearSettings" class="col-md-2 text-left btn btn-danger pull-left"{{ Helper::isDemoMode() ? ' disabled' : ''}}>{{ trans('general.clear_and_save') }}</button>

                        <a class="btn btn-link pull-left" href="{{ route('settings.index') }}">{{ trans('button.cancel') }}</a>


                        <button type="submit" {{$isDisabled}} class="btn btn-primary"{{ Helper::isDemoMode() ? ' disabled' : ''}}>
                        <x-icon type="checkmark" /> {{ $save_button }}</button>

                    </div> <!-- /.col-md-12 -->
                </div><!--box-footer-->

           </div> <!-- /.box -->
       </div> <!-- /.col-md-8-->
    </div> <!-- /.row -->
</form>
</div>  <!-- /livewire div -->
