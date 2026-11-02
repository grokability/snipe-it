<?php

namespace App\SyncAdapters\GoogleWorkspace;

use App\Models\Asset;
use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\PushableSyncAdapter;
use App\SyncAdapters\SyncAdapter;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Google Workspace (ChromeOS) adapter. Pulls Chrome device inventory
 * from the Google Admin SDK Directory API and normalizes it into
 * HostInventoryRecord objects. Also pushes annotatedAssetId and
 * composed notes back to Google Admin so ChromeOS devices stay
 * aligned with Snipe-IT ownership.
 *
 * Auth model: service account with domain-wide delegation. The admin
 * creates a service account in Google Cloud Console, downloads a JSON
 * key, and delegates the required OAuth scopes to it in Google Admin
 * Console. The adapter signs an RS256 JWT with the service account
 * key, exchanges it for a bearer, and impersonates a real admin user
 * on every call. The impersonation email must belong to a super
 * admin (or a delegated admin with Chrome device permissions).
 *
 * Group scoping: Google organizational units (OUs) map to Snipe-IT
 * companies. Every Chrome device carries an `orgUnitPath` (e.g.
 * "/Sales/USA" or "/Engineering"), and the adapter uses that path as
 * the vendorGroupId so admins can route devices to Snipe-IT companies
 * by OU. The vendor group refresh endpoint calls Admin SDK's
 * /customer/{cid}/orgunits and lists every OU under the root.
 *
 * URL is not configurable. The Admin SDK is a single Google-hosted
 * endpoint (admin.googleapis.com), and token exchange lives at
 * oauth2.googleapis.com. Neither has regional variants.
 */
class GoogleWorkspaceAdapter extends PushableSyncAdapter
{
    public static function typeLabel(): string
    {
        return 'Google Workspace (ChromeOS)';
    }

    public static function typeSlug(): string
    {
        return 'google_workspace';
    }

    public static function docsUrl(): ?string
    {
        return 'https://developers.google.com/admin-sdk/directory/reference/rest/v1/chromeosdevices';
    }

    /**
     * Google Admin SDK is a single fixed host. Admins never type a URL.
     */
    public function usesConfigurableUrl(): bool
    {
        return false;
    }

    public function settingsSchema(): array
    {
        return [
            [
                'key' => 'service_account_email',
                'label' => trans('admin/settings/sync_adapters.google_workspace_label_service_account_email'),
                'help' => trans('admin/settings/sync_adapters.google_workspace_service_account_email_help'),
                'placeholder' => 'snipeit-sync@your-project.iam.gserviceaccount.com',
            ],
            [
                'key' => 'private_key',
                'label' => trans('admin/settings/sync_adapters.google_workspace_label_private_key'),
                'type' => 'textarea',
                'secret' => true,
                'help' => trans('admin/settings/sync_adapters.google_workspace_private_key_help'),
                'placeholder' => "-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----",
            ],
            [
                'key' => 'impersonate_email',
                'label' => trans('admin/settings/sync_adapters.google_workspace_label_impersonate_email'),
                'help' => trans('admin/settings/sync_adapters.google_workspace_impersonate_email_help'),
                'placeholder' => 'admin@your-domain.com',
            ],
            [
                'key' => 'customer_id',
                'label' => trans('admin/settings/sync_adapters.google_workspace_label_customer_id'),
                'help' => trans('admin/settings/sync_adapters.google_workspace_customer_id_help'),
                'default' => 'my_customer',
                'required' => false,
            ],
        ];
    }

    public function extraFields(): array
    {
        return [
            'google_workspace_annotated_location' => ['label_key' => 'admin/settings/sync_adapters.google_workspace_extra_annotated_location'],
            'google_workspace_annotated_user' => ['label_key' => 'admin/settings/sync_adapters.google_workspace_extra_annotated_user'],
            'google_workspace_boot_mode' => ['label_key' => 'admin/settings/sync_adapters.google_workspace_extra_boot_mode'],
            'google_workspace_dev_mode' => ['label_key' => 'admin/settings/sync_adapters.google_workspace_extra_dev_mode'],
            'google_workspace_platform_version' => ['label_key' => 'admin/settings/sync_adapters.google_workspace_extra_platform_version'],
            'google_workspace_firmware_version' => ['label_key' => 'admin/settings/sync_adapters.google_workspace_extra_firmware_version'],
            'google_workspace_ethernet_mac' => ['label_key' => 'admin/settings/sync_adapters.google_workspace_extra_ethernet_mac'],
            'google_workspace_wifi_mac' => ['label_key' => 'admin/settings/sync_adapters.google_workspace_extra_wifi_mac'],
            'google_workspace_enrollment_time' => ['label_key' => 'admin/settings/sync_adapters.google_workspace_extra_enrollment_time', 'type' => 'date'],
            'google_workspace_org_unit_path' => ['label_key' => 'admin/settings/sync_adapters.google_workspace_extra_org_unit_path'],
        ];
    }

    public function supportsGroupScoping(): bool
    {
        return true;
    }

    public function vendorGroupLabel(): string
    {
        return trans('admin/settings/sync_adapters.vendor_group_google_workspace_org_unit');
    }

    /**
     * Fetch every organizational unit under the customer root. Admin
     * SDK returns a flat list keyed on orgUnitPath ("/Sales", "/Sales/USA",
     * etc.). We use the path as the group id because it's stable across
     * OU renames of ancestors and it's what per-device records carry as
     * `orgUnitPath`. The root OU ("/") is included as an explicit option
     * so admins can route devices at the top level.
     */
    public function fetchGroups(): array
    {
        $client = $this->buildClient();

        $groups = [['id' => '/', 'label' => '/']];
        foreach ($client->orgUnits() as $unit) {
            $path = (string) ($unit['orgUnitPath'] ?? '');
            if ($path === '') {
                continue;
            }
            $groups[] = [
                'id' => $path,
                'label' => $path,
            ];
        }

        return $groups;
    }

    public function pull(): iterable
    {
        $client = $this->buildClient();

        foreach ($client->chromeosDevices() as $device) {
            yield $this->normalize($device);
        }
    }

    /**
     * Convert a Chrome device payload into the normalized record shape.
     *
     * Admin SDK's stable id is `deviceId` (a GUID). Serial is at
     * `serialNumber`. Model comes as a marketing string ("HP Chromebook
     * 14 G6"). Manufacturer is not exposed on the Chrome device
     * resource today so it stays null unless the admin maps something
     * from extras.
     *
     * hostname preference order: annotatedAssetId (admin-assigned
     * label if any), then the marketing model plus a serial suffix so
     * every device gets a distinct-looking hostname in Snipe-IT even
     * when the admin has not typed an asset ID on the vendor side.
     * Never falls back to just the serial because that reads as
     * duplicate serial data in the UI.
     *
     * @param  array<string, mixed>  $device
     */
    private function normalize(array $device): HostInventoryRecord
    {
        $serial = Arr::get($device, 'serialNumber');
        $model = Arr::get($device, 'model');
        $annotatedAssetId = Arr::get($device, 'annotatedAssetId');

        $hostname = null;
        if (is_string($annotatedAssetId) && $annotatedAssetId !== '') {
            $hostname = $annotatedAssetId;
        } elseif (is_string($model) && $model !== '' && is_string($serial) && $serial !== '') {
            $hostname = $model.' ('.$serial.')';
        }

        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: (string) Arr::get($device, 'deviceId'),
            hostname: $hostname,
            hardwareSerial: $serial,
            hardwareModel: $model,
            manufacturer: null,
            primaryMac: Arr::get($device, 'ethernetMacAddress') ?? Arr::get($device, 'macAddress'),
            primaryIp: $this->extractPrimaryIp($device),
            os: 'ChromeOS',
            osVersion: Arr::get($device, 'osVersion'),
            lastSeen: $this->parseTimestamp(Arr::get($device, 'lastSync')),
            assetTag: is_string($annotatedAssetId) && $annotatedAssetId !== '' ? $annotatedAssetId : null,
            assignedUserEmail: $this->extractUserEmail($device),
            vendorGroupId: Arr::get($device, 'orgUnitPath'),
            extra: [
                'google_workspace_annotated_location' => Arr::get($device, 'annotatedLocation'),
                'google_workspace_annotated_user' => Arr::get($device, 'annotatedUser'),
                'google_workspace_boot_mode' => Arr::get($device, 'bootMode'),
                'google_workspace_dev_mode' => Arr::get($device, 'devMode'),
                'google_workspace_platform_version' => Arr::get($device, 'platformVersion'),
                'google_workspace_firmware_version' => Arr::get($device, 'firmwareVersion'),
                'google_workspace_ethernet_mac' => Arr::get($device, 'ethernetMacAddress'),
                'google_workspace_wifi_mac' => Arr::get($device, 'macAddress'),
                'google_workspace_enrollment_time' => Arr::get($device, 'lastEnrollmentTime'),
                'google_workspace_org_unit_path' => Arr::get($device, 'orgUnitPath'),
            ],
        );
    }

    /**
     * `recentUsers[0].email` is the most-recently-signed-in Google
     * account. Empty or "unknown" strings (Chrome returns these when a
     * device has never signed in with a managed account) collapse to
     * null so the sync path treats them as "no user".
     *
     * @param  array<string, mixed>  $device
     */
    private function extractUserEmail(array $device): ?string
    {
        $email = Arr::get($device, 'recentUsers.0.email');
        if (! is_string($email) || $email === '' || strcasecmp($email, 'UNKNOWN_USER') === 0) {
            return null;
        }

        return $email;
    }

    /**
     * Admin SDK returns an array of network history entries. The most
     * recent one carries the current IP. Falls back to the top-level
     * `lastKnownNetwork[0].ipAddress` shape Google's docs describe.
     *
     * @param  array<string, mixed>  $device
     */
    private function extractPrimaryIp(array $device): ?string
    {
        $ip = Arr::get($device, 'lastKnownNetwork.0.ipAddress');
        if (is_string($ip) && $ip !== '') {
            return $ip;
        }

        return null;
    }

    private function parseTimestamp(mixed $value): ?Carbon
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        return Carbon::parse($value);
    }

    /**
     * Push is always available for a configured Google Workspace
     * instance. The delegated OAuth scope needed to write is
     * https://www.googleapis.com/auth/admin.directory.device.chromeos
     * (no `.readonly` suffix). Runtime failure surfaces as a 403 from
     * Google when the admin only granted the readonly scope.
     */
    public function canPush(): bool
    {
        return true;
    }

    /**
     * Google Admin exposes a freeform `notes` field on every Chrome
     * device. It is the natural target for composed notes (asset tag,
     * category, user display name, whatever the admin templates in the
     * settings UI).
     */
    public function notesFieldTarget(): ?string
    {
        return 'notes';
    }

    /**
     * Push Snipe-IT-owned fields back to Google Admin. Today the
     * writable set is: annotatedAssetId (from asset_tag when mapped
     * push), annotatedLocation (from asset location if mapped push),
     * annotatedUser (from assigned user email if mapped push), and
     * notes (from the composed-notes template). Fields the admin has
     * not directed push stay untouched.
     *
     * @param  array<int, string>  $changedFields
     */
    public function push(Asset $asset, array $changedFields = []): bool
    {
        $externalSource = $this->pushPrologue($asset, $changedFields);
        if ($externalSource === null) {
            return false;
        }

        $payload = $this->buildOutgoingPayload($asset);
        $this->applyComposedNotesToPayload($asset, $payload);

        if ($payload === []) {
            return false;
        }

        if ($this->isPushDryRun()) {
            Log::channel('sync-adapters')->info(sprintf(
                '%s push [dry-run]: would PATCH Google Admin Chrome device %s with %s',
                $this->name(),
                $externalSource->external_id,
                json_encode($payload, JSON_UNESCAPED_SLASHES),
            ));

            return true;
        }

        $this->buildClient()->updateChromeosDevice($externalSource->external_id, $payload);

        Log::channel('sync-adapters')->info(sprintf(
            '%s push: updated Google Admin Chrome device %s (%s)',
            $this->name(),
            $externalSource->external_id,
            implode(',', array_keys($payload)),
        ));

        return true;
    }

    /**
     * Map push-directed Snipe-IT fields onto Google Admin write keys.
     * Only the fields the admin has flagged push (or both) in the
     * direction UI appear here. This is the base payload before
     * composed notes are spliced in.
     *
     * Named distinct from the SyncAdapter base's buildPushPayload()
     * template hook because that hook returns a `[payload, touched]`
     * for adapters that route through pushViaSinglePayload().
     * This adapter's push() dispatches directly and only needs the
     * payload half, so the shape and name stay separate.
     *
     * @return array<string, mixed>
     */
    private function buildOutgoingPayload(Asset $asset): array
    {
        $payload = [];

        foreach ($this->pushDirectedFields() as $field) {
            $target = $this->pushTargetForField($field);
            if ($target === null) {
                continue;
            }
            $payload[$target] = $this->assetValueForSourceField($asset, $field);
        }

        return $payload;
    }

    /**
     * Snipe-IT source field -> Google Admin write key. Fields not in
     * this map do not have a Google-side counterpart and are skipped
     * on push even if the admin flagged them push-direction.
     *
     * `google_workspace_annotated_user` is one of the vendor-specific
     * extras exposed via extraFields(). Admins flag direction=push on
     * it in the mapping UI so Google's annotatedUser field mirrors
     * whoever the asset is currently checked out to on the Snipe-IT
     * side. Value resolution happens in assetValueForSourceField()
     * below, which reads asset->assignedTo->email at push time.
     */
    private function pushTargetForField(string $field): ?string
    {
        return match ($field) {
            'asset_tag' => 'annotatedAssetId',
            'google_workspace_annotated_user' => 'annotatedUser',
            default => null,
        };
    }

    /**
     * Resolve Snipe-IT source-field values for push. Standard fields
     * (asset_tag, hostname, serial, etc.) fall through to the parent
     * implementation. The vendor-specific extras this adapter can
     * push get resolved here.
     *
     * `google_workspace_annotated_user` returns the assigned user's
     * email address (Google's freeform annotatedUser field is a
     * string, and Google Admin convention is to store the primary
     * account there). Falls back to the user's display name when
     * no email is on file. Returns null when the asset is
     * unassigned or is checked out to a Location / another Asset
     * rather than a User. buildPushPayload includes the key in the
     * PATCH regardless, so a null value clears annotatedUser on
     * Google's side, which propagates check-in state through the
     * sync.
     */
    protected function assetValueForSourceField(Asset $asset, string $field): mixed
    {
        if ($field === 'google_workspace_annotated_user') {
            $assignee = $asset->assignedTo;
            if (! $assignee instanceof \App\Models\User) {
                return null;
            }

            return $assignee->email ?: $assignee->display_name;
        }

        return parent::assetValueForSourceField($asset, $field);
    }

    private function buildClient(): GoogleWorkspaceClient
    {
        return new GoogleWorkspaceClient(
            serviceAccountEmail: $this->credential('service_account_email'),
            privateKeyPem: $this->credential('private_key'),
            impersonateEmail: $this->credential('impersonate_email'),
            customerId: $this->credential('customer_id') ?: 'my_customer',
        );
    }
}
