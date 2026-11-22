<?php

namespace App\Enums;

/**
 * Canonical values for `action_logs.action_source`. Writers pass
 * either a case or a plain string to `Actionlog::withActionSource()`.
 *
 * `defaultUserAgent()` returns the canonical label the trait stamps
 * into `action_logs.user_agent` when a given source is in use. HTTP-
 * driven sources (`Gui`, `Api`, `Cli`) return null so the real
 * request's `User-Agent` header passes through unchanged.
 */
enum ActionSource: string
{
    case Gui = 'gui';
    case Api = 'api';
    case Cli = 'cli/unknown';
    case Ldap = 'ldap';
    case Mcp = 'mcp';

    /**
     * Prefix for sync-adapter sources. The slug portion is dynamic
     * (`sync:abm`, `sync:jamf`, `sync:kandji`, etc.) so callers
     * concatenate directly rather than enumerating every adapter.
     * Compose a full source via `ActionSource::syncAdapter($slug)`.
     */
    public const SYNC_ADAPTER_PREFIX = 'sync:';

    /**
     * Compose the `sync:<slug>` string a sync-adapter pull writes
     * into `action_source`. The slug is dynamic (vendor-specific
     * SyncAdapterInstance slug) so this stays a static helper rather
     * than an enum case.
     */
    public static function syncAdapter(string $slug): string
    {
        return self::SYNC_ADAPTER_PREFIX.$slug;
    }

    /**
     * Human-readable label the paired `user_agent` column carries
     * when this source is active. Returns null for HTTP-driven
     * sources so the triggering client's `User-Agent` header is
     * preserved. Sync-adapter pulls compose their own UA through
     * `syncAdapterUserAgent()` because the slug is not on the enum.
     */
    public function defaultUserAgent(): ?string
    {
        return match ($this) {
            self::Ldap => 'Snipe-IT LDAP sync',
            self::Mcp => 'Snipe-IT MCP',
            default => null,
        };
    }

    /**
     * Companion to `syncAdapter()`. Returns the `user_agent` string
     * a sync-adapter pull stamps on every observer-driven write so
     * activity-report readers can tell the sync source apart from
     * the admin's browser (the GUI "Sync Now" click would otherwise
     * leak the admin's UA into every synced-row log).
     */
    public static function syncAdapterUserAgent(string $slug): string
    {
        return 'Snipe-IT sync: '.$slug;
    }
}
