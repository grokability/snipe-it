@extends('layouts/basic')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h1 class="box-title">
                            <x-icon type="warning" />
                            {{ trans('admin/settings/mcp.client_not_approved_title') }}
                        </h1>
                    </div>
                    <div class="box-body">
                        <p>{{ $message }}</p>
                        <p class="text-muted">
                            {{ trans('admin/settings/mcp.client_not_approved_name') }}
                            <strong>{{ $client->name }}</strong>
                        </p>
                        @php
                            $primaryRedirect = $client->primary_redirect_uri ?? null;
                        @endphp
                        @if ($primaryRedirect)
                            <p class="text-muted">
                                {{ trans('admin/settings/mcp.client_not_approved_redirect') }}
                                <code>{{ $primaryRedirect }}</code>
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
