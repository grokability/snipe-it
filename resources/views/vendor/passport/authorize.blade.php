<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }} - {{ trans('passport.authorize_title', ['client' => $client->name]) }}</title>

    {{-- stylesheets --}}
    <link rel="stylesheet" href="{{ url(mix('css/dist/all.css')) }}">
    <style>
        .passport-authorize .container {
            margin-top: 30px;
        }

        .passport-authorize .scopes {
            margin-top: 20px;
        }

        .passport-authorize .buttons {
            margin-top: 25px;
        }

        .passport-authorize .buttons form {
            display: inline;
        }
    </style>
</head>
<body class="passport-authorize">
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ trans('passport.authorize_title', ['client' => $client->name]) }}
                    </div>
                    <div class="panel-body">
                        <p>{{ trans('passport.authorize_body') }}</p>

                        <p>{{ trans('passport.authorize_logged_in_as', ['username' => $user->username]) }}</p>

                        @if (count($scopes) > 0)
                            <div class="scopes">
                                <p><strong>{{ trans('general.permissions') }}</strong></p>

                                <ul>
                                    @foreach ($scopes as $scope)
                                        <li>
                                            {{ $scope->description && $scope->description !== $scope->id ? $scope->description : $scope->id }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="buttons row">
                            <div class="col-xs-6">
                                <form method="post" action="{{ url('/oauth/authorize') }}">
                                    {{ csrf_field() }}
                                    {{ method_field('DELETE') }}

                                    <input type="hidden" name="state" value="{{ $request->state }}">
                                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                                    <button type="submit" class="btn btn-default">{{ trans('general.cancel') }}</button>
                                </form>
                            </div>
                            <div class="col-xs-6 text-right">
                                <form method="post" action="{{ url('/oauth/authorize') }}">
                                    {{ csrf_field() }}

                                    <input type="hidden" name="state" value="{{ $request->state }}">
                                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                                    <button type="submit" class="btn btn-primary">{{ trans('passport.authorize_button') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
