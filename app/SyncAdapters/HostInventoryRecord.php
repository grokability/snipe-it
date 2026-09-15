<?php

namespace App\SyncAdapters;

use Carbon\CarbonInterface;

/**
 * Normalized host record produced by every adapter. Universal currency of
 * the host-inventory ingestion pipeline. Adapters convert vendor-specific
 * payloads into this shape, the sync action reads only this shape.
 */
final readonly class HostInventoryRecord
{
    /**
     * @param  array<string, mixed>  $extra  source-specific data that per-adapter
     *                                       fieldsets can surface as custom fields
     */
    public function __construct(
        public string $sourceKey,
        public string $sourceId,
        public ?string $hostname = null,
        public ?string $hardwareSerial = null,
        public ?string $hardwareModel = null,
        public ?string $manufacturer = null,
        public ?string $primaryMac = null,
        public ?string $primaryIp = null,
        public ?string $os = null,
        public ?string $osVersion = null,
        public ?CarbonInterface $lastSeen = null,
        // Vendor-provided asset tag when the source has one (Kandji,
        // Jamf School, Mosyle, Addigy). Never overwrites Snipe-IT's
        // asset_tag by default. Admins opt in via the mapping UI by
        // picking `native:asset_tag` as the target for this field.
        public ?string $assetTag = null,
        // Vendor-side identifiers for whoever the device is assigned
        // to. Adapters populate whichever the vendor emits. The sync
        // path's user-lookup uses one of these per the adapter's
        // configured match strategy (email or username).
        public ?string $assignedUserEmail = null,
        public ?string $assignedUserName = null,
        // Vendor's group identifier (Fleet Team id, Jamf Site id,
        // Kandji Blueprint id, etc). Sync path uses this to resolve
        // the asset's Snipe-IT company via the adapter's per-group
        // company mapping. Null when the vendor doesn't group the
        // device or the adapter doesn't emit group data.
        public ?string $vendorGroupId = null,
        public array $extra = [],
    ) {}
}
