@extends('layouts/default')
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
    <div class="row">
        <div class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">
            <div class="panel box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">
                        <x-icon type="general-settings"/>
                        {{ trans('admin/settings/general.webhook_help') }}
                    </h2>
                </div>
                <div class="box-body">

                    <div class="col-md-12">
                        <fieldset>
                            <x-form.legend>
                                {{ trans('admin/settings/general.general_webhook') }}
                            </x-form.legend>
                            @livewire('integration-settings-form')
                        </fieldset>

                        <fieldset>
                            <x-form.legend>
                                {{ trans('admin/settings/general.webhook_company_integrations') }}
                            </x-form.legend>
                            <div class="col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1">
                                <x-table
                                        name="webhook_companies"
                                        fixed_right_number="1"
                                        fixed_number="1"
                                        api_url="{{ route('api.companies.index', ['has_webhook' => 1]) }}"
                                        :presenter="\App\Presenters\CompanyIntegrationsPresenter::dataTableLayout()"
                                        :show_export="false"
                                        :show_columns="false"
                                        :show_column_search="false"
                                        :no_matches="trans('admin/settings/general.no_company_webhooks')"
                                />
                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


@section('moar_scripts')
    @include ('partials.bootstrap-table', ['search' => true])
@stop



