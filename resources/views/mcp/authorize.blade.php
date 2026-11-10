@extends('layouts/basic')


{{-- Page content --}}
@section('content')

    <input type="hidden" name="_token" value="{{ csrf_token() }}"/>




    <div class="container">
        <div class="row">

            <div class="col-md-8 col-md-offset-2">

                <div class="box box-default">
                    <div class="box-header with-border">
                        <h1 class="box-title"> Authorize {{ $client->name }}</h1>
                    </div>

                    <div class="box-body">
                        <div class="col-md-12">

                            <!-- Notifications -->
                            @include('notifications')

                            <p class="text-muted">
                                This application will be able to use available MCP functionality.
                            </p>


                            <p>Logged in as: {{ $user->username }}</p>


                            <!-- Scopes / Permissions -->
                            @if(count($scopes) > 0)

                                <p>Permissions:</p>

                                <ul>
                                    @foreach($scopes as $scope)
                                        <li>
                                            {{ $scope->description }}
                                        </li>
                                    @endforeach
                                </ul>

                            @endif


                        </div> <!-- end row -->

                    </div>

                    <div class="box-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Deny Form -->
                                <form method="POST" action="{{ route('passport.authorizations.deny') }}" class="form-inline">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="state" value="">
                                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                                    <input type="hidden" name="auth_token" value="{{ $authToken }}">
                                    <button type="submit" class="btn btn-default">
                                        <i class="fa fa-xmark"></i>
                                        Cancel
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-6">

                                <!-- Approve Form -->
                                <form method="POST" action="{{ route('passport.authorizations.approve') }}" class="flex-1" id="authorizeForm">
                                    @csrf
                                    <input type="hidden" name="state" value="">
                                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                                    <input type="hidden" name="auth_token" value="{{ $authToken }}">
                                    <button type="submit" class="btn btn-primary pull-right" id="authorizeButton">
                                        <span id="authorizeText">Authorize</span>
                                        <span id="loadingSpinner" class="hidden">
                                    <i class="fa fa-spinner fa-spin"></i>
                                    </button>
                                </form>

                            </div>
                        </div>
                    </div>

                </div> <!-- end login box -->


            </div> <!-- col-md-4 -->

        </div> <!-- end row -->
    </div> <!-- end container -->

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('authorizeForm');
            const button = document.getElementById('authorizeButton');
            const authorizeText = document.getElementById('authorizeText');
            const loadingSpinner = document.getElementById('loadingSpinner');

            form.addEventListener('submit', function (e) {
                // Show loading state...
                button.disabled = true;
                authorizeText.textContent = 'Authorizing...';
                loadingSpinner.classList.remove('hidden');

                // After form submission, watch for redirect and close window...
                setTimeout(function () {
                    const checkRedirect = setInterval(function () {
                        // If URL changed or we have OAuth params, redirect happened...
                        if (!window.location.href.includes('/oauth/authorize') ||
                            window.location.search.includes('code=') ||
                            window.location.search.includes('error=')) {
                            clearInterval(checkRedirect);
                            window.close();
                        }
                    }, 100);

                    // Fallback: Close after five seconds...
                    setTimeout(function () {
                        clearInterval(checkRedirect);
                        window.close();
                    }, 5000);
                }, 200);
            });

            // Handle cancel button...
            const cancelForm = document.querySelector('form[method="POST"]:has(input[name="_method"][value="DELETE"])');
            if (cancelForm) {
                cancelForm.addEventListener('submit', function (e) {
                    setTimeout(function () {
                        window.close();
                    }, 200);
                });
            }
        });
    </script>

@stop


