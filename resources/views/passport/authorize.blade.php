@extends('layouts/basic')

{{-- Generic OAuth consent view used when the authorization request is
     not for an MCP scope. The parallel mcp/authorize.blade.php handles
     MCP clients (ChatGPT, Claude Desktop, etc.), which have their own
     UX including a window.close() hook. --}}

@section('content')

    <div class="container">
        <div class="row">

            <div class="col-md-8 col-md-offset-2">

                <div class="box box-default">

                    <div class="box-header with-border">
                        <h1 class="box-title">
                            {{ trans('passport.authorize_title', ['client' => $client->name]) }}
                        </h1>
                    </div>

                    <div class="box-body">
                        <div class="col-md-12">

                            <x-notifications />

                            <p class="text-muted">
                                {{ trans('passport.authorize_body') }}
                            </p>

                            <p>
                                {{ trans('passport.authorize_logged_in_as', ['username' => $user->username]) }}
                            </p>

                            @if (count($scopes) > 0)
                                <p>{{ trans('general.permissions') }}</p>

                                <ul>
                                    @foreach ($scopes as $scope)
                                        <li>
                                            {{ $scope->description && $scope->description !== $scope->id ? $scope->description : $scope->id }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                        </div>
                    </div>

                    <div class="box-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <form method="POST" action="{{ route('passport.authorizations.deny') }}" class="form-inline">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="state" value="{{ $request->state }}">
                                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                                    <input type="hidden" name="auth_token" value="{{ $authToken }}">
                                    <button type="submit" class="btn btn-default">
                                        <x-icon type="x" />
                                        {{ trans('general.cancel') }}
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <form method="POST" action="{{ route('passport.authorizations.approve') }}">
                                    @csrf
                                    <input type="hidden" name="state" value="{{ $request->state }}">
                                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                                    <input type="hidden" name="auth_token" value="{{ $authToken }}">
                                    <button type="submit" class="btn btn-primary pull-right">
                                        {{ trans('passport.authorize_button') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>

@stop
