<?php

namespace App\SyncAdapters;

use App\Exceptions\SyncAdapterVendorException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Throwable;

class SyncErrorFormatter
{
    /**
     * Short, UI-safe one-liner used by the "Last Synced" field on the
     * sync-adapter settings page and by the log-line headline.
     *
     * For an HTTP error, returns "HTTP 401: Forbidden" when the vendor
     * body yields something readable, "HTTP 401" otherwise.
     * For a connection error, returns the configured network-failure
     * translation.
     * For a vendor-raised exception (wrong base URL, unexpected content
     * type), returns the exception's own actionable message.
     * Everything else falls back to the exception's short class name.
     */
    public static function summary(Throwable $e): string
    {
        if ($e instanceof RequestException) {
            $status = $e->response->status();
            $vendorMessage = self::extractVendorErrorMessage($e->response);

            return $vendorMessage !== null
                ? sprintf('HTTP %d: %s', $status, $vendorMessage)
                : 'HTTP '.$status;
        }

        if ($e instanceof ConnectionException) {
            return trans('admin/settings/sync_adapters.sync_failed_network');
        }

        if ($e instanceof SyncAdapterVendorException) {
            return $e->getMessage();
        }

        /*
         * Generic throwable fallback. A bare class_basename alone is
         * useless to admins debugging ("RuntimeException" tells them
         * nothing), so append the exception's own message when it has
         * one. Covers the long-tail: JWT key-parse failures from
         * firebase/php-jwt (RuntimeException "OpenSSL unable to..."),
         * OpenSSL key-import errors (ValueError), JSON decode failures,
         * etc. Truncate so a verbose message doesn't nuke the UI.
         */
        $class = class_basename($e);
        $message = trim((string) $e->getMessage());
        if ($message === '') {
            return $class;
        }
        if (mb_strlen($message) > 200) {
            $message = mb_substr($message, 0, 200) . '…';
        }

        return $class . ': ' . $message;
    }

    /**
     * Structured context fields for the sync-adapters log channel.
     * Carries the full, unescaped vendor response body and the raw
     * HTTP status rather than Laravel's RequestException-constructed
     * message, which is both truncated and re-escaped.
     *
     * @return array<string, mixed>
     */
    public static function logContext(Throwable $e): array
    {
        $context = ['exception' => $e];

        if ($e instanceof RequestException) {
            $context['http_status'] = $e->response->status();
            $context['response_body'] = $e->response->body();
            $vendorMessage = self::extractVendorErrorMessage($e->response);
            if ($vendorMessage !== null) {
                $context['vendor_message'] = $vendorMessage;
            }
        }

        return $context;
    }

    /**
     * Extract the first human-readable message from a vendor error
     * response body. Handles the common shapes:
     * - {"message": "..."}           Fleet, WS1, JumpCloud, Mosyle
     * - {"detail": "..."}            DRF-based (Zentral, some others)
     * - {"error": "..."}             plain string
     * - {"error": {"message": "..."}}  Microsoft Graph
     * - {"errorMessage": "..."}      NinjaOne
     * - {"errors": [{"description|reason|message|detail": "..."}]}  Jamf, Fleet nested
     * - {"errors": ["..."]}          Meraki
     * Response bodies get truncated so a vendor returning a novel
     * doesn't blow up the flash bar.
     */
    public static function extractVendorErrorMessage(Response $response): ?string
    {
        $body = $response->json();
        if (! is_array($body)) {
            return null;
        }

        $candidate = self::pluckErrorCandidate($body);
        if (! is_string($candidate) || trim($candidate) === '') {
            return null;
        }

        $candidate = trim($candidate);

        return mb_strlen($candidate) > 200
            ? mb_substr($candidate, 0, 200).'…'
            : $candidate;
    }

    /**
     * Walk the shapes we know about in order (top-level scalar keys,
     * nested error object, errors array of strings, errors array of
     * objects). Returns the first string it lands on or null if none
     * of the paths yield anything.
     *
     * @param  array<string, mixed>  $body
     */
    private static function pluckErrorCandidate(array $body): ?string
    {
        $candidate = $body['message']
            ?? $body['detail']
            ?? $body['errorMessage']
            ?? null;
        if (is_string($candidate)) {
            return $candidate;
        }

        $candidate = self::pluckErrorFromErrorKey($body);
        if (is_string($candidate)) {
            return $candidate;
        }

        return self::pluckErrorFromErrorsArray($body);
    }

    /**
     * Extract from a top-level `error` key. Handles both the string
     * shape ({"error": "..."}) and the Microsoft Graph nested-object
     * shape ({"error": {"message": "..."}}).
     *
     * @param  array<string, mixed>  $body
     */
    private static function pluckErrorFromErrorKey(array $body): ?string
    {
        if (! isset($body['error'])) {
            return null;
        }
        if (is_string($body['error'])) {
            return $body['error'];
        }
        if (is_array($body['error']) && isset($body['error']['message']) && is_string($body['error']['message'])) {
            return $body['error']['message'];
        }

        return null;
    }

    /**
     * Extract from an `errors` array. First entry wins. Handles the
     * Meraki string-array shape and the Jamf / Fleet object-array
     * shape whose entries carry description / reason / message /
     * detail keys.
     *
     * @param  array<string, mixed>  $body
     */
    private static function pluckErrorFromErrorsArray(array $body): ?string
    {
        if (! isset($body['errors']) || ! is_array($body['errors'])) {
            return null;
        }
        $first = $body['errors'][0] ?? null;
        if (is_string($first)) {
            return $first;
        }
        if (! is_array($first)) {
            return null;
        }

        return $first['description']
            ?? $first['reason']
            ?? $first['message']
            ?? $first['detail']
            ?? null;
    }
}
