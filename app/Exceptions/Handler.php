<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use App\Helpers\Helper;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use ArieTimmerman\Laravel\SCIMServer\Exceptions\SCIMException;
use Illuminate\Support\Facades\Log;
use Throwable;
use JsonException;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
        \Intervention\Image\Exception\NotSupportedException::class,
        \League\OAuth2\Server\Exception\OAuthServerException::class,
        JsonException::class,
        SCIMException::class, //these generally don't need to be reported
        InvalidFormatException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function report(Throwable $exception)
    {
        if ($this->shouldReport($exception)) {
            if (class_exists(Log::class)) {
                Log::error($exception);
            }
            return parent::report($exception);
        }
    }

    /**
     * Render an exception into an HTTP response.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function render($request, Throwable $e)
    {


        // CSRF token mismatch error
        if ($e instanceof \Illuminate\Session\TokenMismatchException) {
            return redirect()->back()->with('error', trans('general.token_expired'));
        }

        // Invalid JSON exception
        // TODO: don't understand why we have to do this when we have the invalidJson() method, below, but, well, whatever
        if ($e instanceof JsonException) {
            return response()->json(Helper::formatStandardApiResponse('error', null, 'Invalid JSON'), 422);
        }

        // Handle SCIM exceptions
        if ($e instanceof SCIMException) {
            try {
                $e->report(); // logs as 'debug', so shouldn't get too noisy
            } catch(\Exception $reportException) {
                //do nothing
            }
            return $e->render($request); // ALL SCIMExceptions have the 'render()' method
        }

        // Handle standard requests that fail because Carbon cannot parse the date on validation (when a submitted date value is definitely not a date)
        if ($e instanceof InvalidFormatException) {
            return redirect()->back()->withInput()->with('error', trans('validation.date', ['attribute' => 'date']));
        }

        // Handle API requests that fail
        if ($request->ajax() || $request->wantsJson()) {

            // Handle API requests that fail because Carbon cannot parse the date on validation (when a submitted date value is definitely not a date)
            if ($e instanceof InvalidFormatException) {
                return response()->json(Helper::formatStandardApiResponse('error', null, trans('validation.date', ['attribute' => 'date'])), 200);
            }

            // Handle API requests that fail because the model doesn't exist
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $className = last(explode('\\', $e->getModel()));
                return response()->json(Helper::formatStandardApiResponse('error', null, $className . ' not found'), 200);
            }

            // Handle API requests that fail because of an HTTP status code and return a useful error message
            if ($this->isHttpException($e)) {

                $statusCode = $e->getStatusCode();

                // API throttle requests are handled in the RouteServiceProvider configureRateLimiting() method, so we don't need to handle them here
                switch ($e->getStatusCode()) {
                    case '404':
                       return response()->json(Helper::formatStandardApiResponse('error', null, $statusCode . ' endpoint not found'), 404);
                     case '405':
                        return response()->json(Helper::formatStandardApiResponse('error', null, 'Method not allowed'), 405);
                    default:
                        return response()->json(Helper::formatStandardApiResponse('error', null, $statusCode), $statusCode);
                }

            }

            // This handles API validation exceptions that happen at the Form Request level, so they
            // never even get to the controller where we normally  nicely format JSON responses
            if ($e instanceof ValidationException) {
                $response = $this->invalidJson($request, $e);
                return response()->json(Helper::formatStandardApiResponse('error', null,  $e->errors()), 200);
            }

        }


        // This is traaaaash but it handles models that are not found while using route model binding :(
        // The only alternative is to set that at *each* route, which is crazypants
        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            $ids = method_exists($e, 'getIds') ? $e->getIds() : [];

            if (in_array('bulkedit', $ids, true)) {
            $error_array = session()->get('bulk_asset_errors');
                return redirect()
                    ->route('hardware.index')
                    ->withErrors($error_array, 'bulk_asset_errors')
                    ->withInput();
            }

            // This gets the MVC model name from the exception and formats in a way that's less fugly
            $model_name = trim(strtolower(implode(" ", preg_split('/(?=[A-Z])/', last(explode('\\', $e->getModel()))))));
            $route = str_plural(strtolower(last(explode('\\', $e->getModel())))).'.index';

            // Sigh.
            if ($route == 'assets.index') {
                $route = 'hardware.index';
            } elseif ($route == 'reporttemplates.index') {
                $route = 'reports/custom';
            } elseif ($route == 'assetmodels.index') {
                $route = 'models.index';
            } elseif ($route == 'predefinedkits.index') {
                $route = 'kits.index';
            } elseif ($route == 'assetmaintenances.index') {
                $route = 'maintenances.index';
            } elseif ($route === 'licenseseats.index') {
                $route = 'licenses.index';
            } elseif (($route === 'customfieldsets.index') || ($route === 'customfields.index')) {
                $route = 'fields.index';
            }

            return redirect()
                ->route($route)
                ->withError(trans('general.generic_model_not_found', ['model' => $model_name]));
        }


        if ($this->isHttpException($e) && (isset($statusCode)) && ($statusCode == '404' )) {
            return response()->view('layouts/basic', [
                'content' => view('errors/404')
            ],$statusCode);
        }

        return parent::render($request, $e);

    }

 /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
  */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthorized or unauthenticated.'], 401);
        }

        return redirect()->guest('login');
    }

    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json(Helper::formatStandardApiResponse('error', null, $exception->errors()), 200);
    }


    /** 
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
