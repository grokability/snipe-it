<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * Shared cache-backed mutex for long-running operations that must not
 * overlap: backup, restore, LDAP sync, import, sync-adapter pulls.
 *
 * These operations have no job queue in Snipe-IT today, they
 * run synchronously inside the request/response cycle (or the Artisan
 * command). The mutex refuses the second trigger before the operation
 * body runs.
 *
 * The lock lives in the file cache store specifically (not the
 * configured default). This is deliberate: a restore wipes the
 * database as part of its work, and installs that set `CACHE_STORE` to
 * `database` would lose the lock mid-restore if we used the default
 * store. The file store survives `db:wipe` and lets the mutex outlive
 * the operation it's protecting.
 *
 * The file store is also shared across the web PHP process and the CLI
 * PHP process on a standard install (same filesystem path, same
 * process user).
 *
 * TTL on the lock is the automatic recovery for the "process died
 * without releasing" case (SIGKILL, OOM, Apache mod_reap). 30 minutes
 * is the shared default. Individual callers can override when their
 * operation is reasonably expected to run longer (restore + database
 * reload on a large install, for example). Admins recovering from a
 * stuck lock before the TTL expires can run `php artisan cache:clear`.
 *
 * The TTL being 30 minutes is largely for the CLI end of things, since the
 * web UI will die long before then and lead to a gateway timeout.
 * The CLI end of things is more likely to be long-running (LDAP sync, restore, etc).
 */
trait LocksLongRunningOperation
{
    protected function acquireOperationLock(string $key, int $ttlMinutes = 30): bool
    {
        return Cache::store('file')->add($key, true, now()->addMinutes($ttlMinutes));
    }

    protected function releaseOperationLock(string $key): void
    {
        Cache::store('file')->forget($key);
    }

    protected function isOperationLocked(string $key): bool
    {
        return Cache::store('file')->has($key);
    }

    /**
     * Return the translated "already in progress" message with the
     * operation's own label substituted in. One parameterized string
     * covers every lock in the system so we don't ship per-operation
     * copies that translators have to localize separately.
     */
    protected function operationAlreadyRunningMessage(string $operationLabelKey): string
    {
        return trans('general.operation_already_running', [
            'operation' => trans($operationLabelKey),
        ]);
    }
}
