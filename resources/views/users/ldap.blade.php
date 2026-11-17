@extends('layouts/default')
{{-- Page title --}}
@section('title')
{{ trans('general.ldap_user_sync') }}
@parent
@stop

{{-- Page content --}}
@section('content')

<x-container columns="1" class="col-md-8 col-md-offset-2">
    @if ($snipeSettings->ldap_enabled == 0)
        {{ trans('admin/users/message.ldap_not_configured') }}
    @else
        @if ($ldapSyncRunning)
            <x-callout type="warning" icon="warning" live="assertive">
                {{ trans('general.operation_already_running', ['operation' => trans('general.ldap_sync')]) }}
            </x-callout>
        @endif

        <x-form id="ldap-form">
            <x-box>
                <x-callout type="legend" icon="tip" class="col-md-12">

                        <strong>
                            {!! trans('admin/users/general.ldap_sync_intro', ['link' => 'https://snipe-it.readme.io/docs/ldap-sync#/']) !!}
                        </strong>

                </x-callout>

                <x-input.location-select
                    :label="trans('general.ldap_sync_location')"
                    name="location_id[]"
                    :selected="null"
                    :multiple="true"
                    :hide-new-button="true"
                    :help-text="trans('admin/users/general.ldap_config_text')"
                />

                <x-slot:customfooter>
                    <div class="box-footer">
                        <div class="text-left col-md-6">
                            <a class="btn btn-link" href="{{ route('users.index') }}">{{ trans('button.cancel') }}</a>
                        </div>
                        <div class="text-right col-md-6">
                            <span @if ($ldapSyncRunning) data-tooltip="true" title="{{ trans('general.operation_already_running', ['operation' => trans('general.ldap_sync')]) }}" @endif>
                                <button type="submit" class="btn btn-primary" id="sync" @disabled($ldapSyncRunning)>
                                    <i id="sync-button-icon" class="fas fa-sync-alt icon-white" aria-hidden="true"></i>
                                    <span id="sync-button-text">{{ trans('general.synchronize') }}</span>
                                </button>
                            </span>
                        </div>
                    </div>
                </x-slot:customfooter>
            </x-box>
        </x-form>
    @endif
</x-container>

@if (Session::get('summary'))
<x-container columns="1" class="col-md-8 col-md-offset-2">
    <x-box :header="trans('general.sync_results')">
        <table
            data-cookie-id-table="ldapUserSync"
            data-id-table="ldapUserSyncTable"
            data-side-pagination="client"
            data-sort-order="asc"
            data-sort-name="username"
            data-show-refresh="false"
            id="customFieldsTable"
            data-advanced-search="false"
            class="table table-striped snipe-table"
            data-export-options='{
            "fileName": "ldap-sync-results-{{ date('Y-m-d') }}"
        }'>
            <thead>
                <tr>
                    <th scope="col" data-sortable="true" data-visible="false" data-searchable="true">{{ trans('general.id') }}</th>
                    <th scope="col" data-sortable="true" data-visible="true" data-searchable="true">{{ trans('general.username') }}</th>
                    <th scope="col" data-sortable="true" data-visible="true" data-searchable="true">{{ trans('admin/users/table.display_name') }}</th>
                    <th scope="col" data-sortable="true" data-visible="true" data-searchable="true">{{ trans('general.employee_number') }}</th>
                    <th scope="col" data-sortable="true" data-visible="true" data-searchable="true">{{ trans('general.first_name') }}</th>
                    <th scope="col" data-sortable="true" data-visible="true" data-searchable="true">{{ trans('general.last_name') }}</th>
                    <th scope="col" data-sortable="true" data-visible="true" data-searchable="true">{{ trans('general.email') }}</th>
                    <th scope="col" data-sortable="true" data-visible="true" data-searchable="true">{{ trans('general.notes') }}</th>
                </tr>
            </thead>
            <tbody>

                @foreach (Session::get('summary') as $entry)
                    <tr>
                        <td>{{ (array_key_exists('id', $entry)) ?  $entry['id'] : '' }}</td>
                        <td>{{ $entry['username'] }}</td>
                        <td>{{ $entry['display_name'] }}</td>
                        <td>{{ $entry['employee_num'] }}</td>
                        <td>{{ $entry['first_name'] }}</td>
                        <td>{{ $entry['last_name'] }}</td>
                        <td>{{ $entry['email'] }}</td>
                        <td>
                        @if ($entry['status']=='success')
                        <span class="text-success"><i class="fas fa-check"></i> {!! $entry['note'] !!}</span>
                        @else
                        <span class="alert-msg" role="alert" aria-live="assertive">{!! $entry['note'] !!}</span>
                        @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-box>
</x-container>
@endif

@stop

@section('moar_scripts')

 @include ('partials.bootstrap-table')

    <script type="text/javascript">
    $(document).ready(function () {
        $("#sync").click(function () {
            $("#sync").removeClass("btn-warning");
            $("#sync").addClass("btn-success");
            $("#sync-button-icon").addClass("fa-spin");
            $("#sync-button-text").html("{{ trans('general.processing') }}");
        });
    });
</script>

@stop
