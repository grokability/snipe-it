<?php

namespace App\SyncAdapters;

use App\Models\Asset;

/**
 * Marker + contract for adapters that support pushing Snipe-IT-owned
 * field values back to the vendor (Snipe-IT asset_tag -> vendor
 * asset_tag, Snipe-IT notes -> vendor notes, etc.). Kept separate
 * from HostInventoryAdapter so pull-only vendors (osctrl, Zentral,
 * UniFi) don't have to stub anything.
 *
 * Adapters that implement this ALSO implement HostInventoryAdapter
 * in practice — the two directions share credentials, base URL, and
 * asset identity (asset_external_sources.external_id). The interfaces
 * stay separate so an adapter can be pull-only, push-only, or both.
 */
interface PushableAdapter
{
    /**
     * Runtime check: is push actually available for this instance
     * right now? Adapters return false when a vendor-side gate is
     * in effect (Fleet manual labels are Premium-only, Jamf writes
     * need extra role scopes, etc.) so the settings-page UI hides
     * the push controls instead of letting admins configure a path
     * that will 403 at execution time.
     *
     * Default implementations may return true unconditionally. The
     * check is opt-in per adapter.
     */
    public function canPush(): bool;

    /**
     * Name (or dotted path) of the vendor's single freeform notes
     * field that admins can compose from a template. Return null
     * when the vendor has no such field (or when routing composed
     * notes would be a semantic stretch, e.g. hostname).
     *
     * Push implementations render pushNotesTemplate() through
     * NotesComposer and set the composed string on this field's
     * position in the outgoing payload.
     */
    public function notesFieldTarget(): ?string;

    /**
     * Push the currently-configured `push`-direction fields for one
     * asset to the vendor. Implementations are responsible for:
     * - Resolving the vendor-side external_id from the asset's
     *   asset_external_sources row scoped to this adapter's instance
     * - Filtering the field set to those directed 'push' via
     *   ConfigurableAdapter::directionFor()
     * - Serializing each field into the vendor's expected shape
     * - Sending the write call
     *
     * Silent no-op is acceptable when the asset has no external_source
     * row for this instance (never synced from here, so we have no
     * vendor-side id to write against).
     *
     * @param  array<int, string>  $changedFields
     *                                             Optional list of source-field names (hostname, asset_tag,
     *                                             etc.) that changed and prompted this push. Implementations
     *                                             may use this to skip an API call when no push-direction field
     *                                             is in the changed set. When empty, treat as "push everything
     *                                             marked push-direction".
     */
    public function push(Asset $asset, array $changedFields = []): void;
}
