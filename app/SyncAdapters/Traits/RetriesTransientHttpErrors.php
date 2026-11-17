<?php

namespace App\SyncAdapters\Traits;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;

/**
 * Transient-error retry policy for sync-adapter HTTP clients. Attaches
 * exponential backoff + jitter to a PendingRequest, with the retry
 * window honoring 429 Retry-After up to 120 seconds. Applied through
 * every client's base request() helper so one dropped connection or
 * throttled page does not abort a scheduled pull.
 *
 * ConnectionException covers DNS, TCP, SSL, and mid-transfer drops
 * (including cURL error 18 "transfer closed with outstanding read data
 * remaining," which is the specific failure mode from issue #19853 that
 * killed a Jamf pull on page 37 of 38).
 *
 * The policy stays off 4xx/5xx aside from 429 so adapter-side
 * misconfiguration (bad filter shape, missing permission, auth drift)
 * still surfaces immediately instead of running down the retry budget
 * before failing. Writes that build their own Http:: calls (OAuth
 * token exchanges, PATCHes against write-only endpoints) stay outside
 * this policy unless they also route through request().
 */
trait RetriesTransientHttpErrors
{
    protected function withTransientRetry(PendingRequest $request, int $times = 8): PendingRequest
    {
        return $request->retry(
            times: $times,
            sleepMilliseconds: function (int $attempt, \Throwable $e): int {
                if ($e instanceof RequestException && $e->response->status() === 429) {
                    $retryAfter = (int) ($e->response->header('Retry-After') ?: 0);
                    if ($retryAfter > 0) {
                        return min($retryAfter, 120) * 1000;
                    }
                }

                // 500ms, 1s, 2s, 4s, 8s, 16s, 32s, 60s plus up to 250ms
                // jitter so parallel instance runs do not retry in
                // lockstep.
                return min(60_000, 500 * (2 ** ($attempt - 1))) + random_int(0, 250);
            },
            when: fn (\Throwable $e): bool => $e instanceof ConnectionException
                || ($e instanceof RequestException && $e->response->status() === 429),
        );
    }
}
