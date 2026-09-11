<?php

namespace App\SyncAdapters;

use App\Models\SyncAdapterInstance;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;

/**
 * Contract every host-inventory sync adapter implements. An adapter is
 * always constructed against a specific SyncAdapterInstance so multiple
 * instances of the same adapter TYPE (two Fleet installs, one Fleet +
 * one Kandji, etc.) each get their own credentials, own settings tab,
 * own asset_external_sources.source value.
 *
 * The asset-side upsert code is shared across adapters
 * (SyncHostFromAdapter action). Adapters also own their own settings
 * view partial and save handler so the generic /admin/adapters page
 * can render every instance's section without the controller knowing
 * anything vendor-specific.
 */
interface HostInventoryAdapter
{
    /**
     * Bind the adapter to the given instance. Every adapter subclass
     * accepts a SyncAdapterInstance in its constructor via this contract
     * so AdapterRegistry::hydrate() can build one uniformly.
     */
    public function __construct(SyncAdapterInstance $instance);

    /**
     * The instance's slug. Used as the `source` value in
     * asset_external_sources and as the route parameter for the sync
     * trigger. Example: 'fleet-prod', 'jamf-staging', 'kandji'.
     */
    public function name(): string;

    /**
     * Human-readable name shown on the settings-page tab. Pulled from
     * the instance's `label` column, which is user-editable per instance.
     */
    public function label(): string;

    /**
     * Whether this instance is configured, active, and available to run.
     * Adapters check their required credentials, the instance's `active`
     * flag, and any adapter-specific readiness here.
     */
    public function isEnabled(): bool;

    /**
     * Whether this instance's `active` toggle is on. Distinct from
     * isEnabled(), which also requires config to be present. The blade
     * uses this to render the checkbox state.
     */
    public function isActive(): bool;

    /**
     * Three-state readiness for the settings-page tab indicator:
     * 'active' (will sync), 'partial' (turned on but missing config),
     * 'inactive' (toggle off). Drives the green/yellow/red dot and
     * the sort order on the shared adapters page.
     */
    public function readinessStatus(): string;

    /**
     * Whether this instance is a shipped built-in (undeletable). The
     * blade uses this to hide the delete control.
     */
    public function isBuiltIn(): bool;

    /**
     * View name for the adapter's settings section, rendered inside a
     * tab-pane on the shared /admin/adapters page. The view is included
     * with the current adapter instance available so it can read its own
     * state (e.g. getUrl(), credentialForDisplay(), isActive(), etc.).
     */
    public function settingsView(): string;

    /**
     * Validation rules the controller runs against the save request
     * BEFORE handing it to saveConfig. Adapters declare their own
     * required fields + SSRF guards here (e.g. the ExternalUrl rule on
     * the URL field to block loopback / RFC-1918 / metadata targets).
     *
     * @return array<string, array<int, mixed>>
     */
    public function validationRules(): array;

    /**
     * Persist this instance's settings from a save request. The generic
     * postAdapters controller iterates the registered instances and
     * calls this for the one whose slug matches the form's hidden
     * `adapter` input. Keeps every adapter's config-storage details
     * inside the adapter class instead of leaking into shared
     * controller code.
     */
    public function saveConfig(Request $request): void;

    /**
     * Pull the current host inventory from the vendor. Returns a
     * generator so adapters can page through large inventories without
     * buffering everything in memory.
     *
     * @return iterable<HostInventoryRecord>
     */
    public function pull(): iterable;

    /**
     * When the last sync ran, or null if this instance has never been
     * synced. Written by both the interactive Sync Now button and the
     * CLI command so the settings page can surface the most recent
     * outcome regardless of trigger source.
     */
    public function lastSyncedAt(): ?CarbonInterface;

    /**
     * Human-readable summary of the last sync's outcome (e.g.
     * "Sync complete. Synced 847 host(s), 3 error(s).") or null if
     * this instance has never been synced.
     */
    public function lastSyncResult(): ?string;
}
