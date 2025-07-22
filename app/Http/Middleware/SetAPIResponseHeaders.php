<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpFoundation\Response;

class SetAPIResponseHeaders extends ThrottleRequests
{

    /**
     * Add the rate limit headers to the response.
     *
     * This extends the original ThrottleRequests middleware to add the 'X-RateLimit-Reset' and 'Retry-After' headers, even
     * if the rate limit is not exceeded.
     * @param $maxAttempts
     * @param $remainingAttempts
     * @param $retryAfter
     * @param Response|null $response
     * @return array|int[]
     */
    protected function getHeaders($maxAttempts,  $remainingAttempts, $retryAfter = null, ?Response $response = null)
    {
        if ($response &&
            ! is_null($response->headers->get('X-RateLimit-Remaining')) &&
            (int) $response->headers->get('X-RateLimit-Remaining') <= (int) $remainingAttempts) {
            $headers = [];
            $headers['Retry-After'] = $retryAfter; // this is the only line we changed
            $headers['X-RateLimit-Reset'] = $retryAfter; // this is the only line we changed
            $headers['X-RateLimit-Reset-Timestamp'] = $this->availableAt($retryAfter); // this is the only line we changed
            return $headers;
        }

        $headers = [
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
        ];

        if (! is_null($retryAfter)) {
            $headers['Retry-After'] = $retryAfter;
            $headers['X-RateLimit-Reset'] = $retryAfter; // this is the only line we changed
            $headers['X-RateLimit-Reset-Timestamp'] = $this->availableAt($retryAfter); // this is the only line we changed
        }

        return $headers;
    }



    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    protected function handleRequest($request, Closure $next, array $limits)
    {
        foreach ($limits as $limit) {
            if ($this->limiter->tooManyAttempts($limit->key, $limit->maxAttempts)) {
                throw $this->buildException($request, $limit->key, $limit->maxAttempts, $limit->responseCallback);
            }

            $this->limiter->hit($limit->key, $limit->decaySeconds);
        }

        $response = $next($request);

        foreach ($limits as $limit) {
            $response = $this->addHeaders(
                $response,
                $limit->maxAttempts,
                $this->calculateRemainingAttempts($limit->key, $limit->maxAttempts),
                $this->getTimeUntilNextRetry($limit->key) // this is the only line we changed
            );
        }

        return $response;
    }

}