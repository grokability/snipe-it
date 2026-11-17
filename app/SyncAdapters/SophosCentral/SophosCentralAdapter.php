<?php

namespace App\SyncAdapters\SophosCentral;

use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\SyncAdapter;
use Carbon\Carbon;
use Illuminate\Support\Arr;

/**
 * Sophos Central adapter. Pulls endpoint inventory and security status
 * from Sophos Central (Sophos Endpoint / Intercept X) and normalizes it
 * into HostInventoryRecord objects.
 *
 * Sophos is often a second source for devices another adapter already
 * created (an MDM, or Apple Business Manager), and the only source for
 * machines no MDM covers: Linux laptops, the odd Windows PC, Macs that
 * were never added to ABM. Records are keyed on the hardware serial, the
 * way the Zentral adapter does it, so "match by serial" joins them to
 * existing assets and a reinstall (which Sophos gives a new endpoint id)
 * stays on the same asset. Endpoints without a serial fall back to the
 * Sophos endpoint id.
 *
 * Sophos can hold more than one endpoint record for the same machine
 * after a reinstall or hand-off. pull() keeps the most recently seen one.
 *
 * Sophos reports no hardware model or manufacturer.
 */
class SophosCentralAdapter extends SyncAdapter
{
    /**
     * Sophos endpoint types. Every Linux device is reported as a server,
     * laptops included, so the type alone can't separate Linux laptops
     * from Linux servers.
     */
    public const ENDPOINT_TYPES = [
        'computer' => 'Computers',
        'server' => 'Servers',
    ];

    public static function typeLabel(): string
    {
        return 'Sophos Central';
    }

    public static function typeSlug(): string
    {
        return 'sophos_central';
    }

    public static function docsUrl(): ?string
    {
        return 'https://developer.sophos.com/intro';
    }

    /**
     * Sophos hosts are fixed: tokens come from id.sophos.com, and the
     * tenant's regional data host is discovered through whoami. There is
     * nothing for the admin to type.
     */
    public function usesConfigurableUrl(): bool
    {
        return false;
    }

    public function settingsSchema(): array
    {
        return [
            [
                'key' => 'client_id',
                'label' => trans('admin/settings/sync_adapters.label_client_id'),
                'help' => trans('admin/settings/sync_adapters.sophos_client_id_help'),
            ],
            [
                'key' => 'client_secret',
                'label' => trans('admin/settings/sync_adapters.label_client_secret'),
                'secret' => true,
                'help' => trans('admin/settings/sync_adapters.sophos_client_secret_help'),
            ],
            [
                'key' => 'endpoint_types',
                'label' => trans('admin/settings/sync_adapters.sophos_label_endpoint_types'),
                'type' => 'multiselect',
                'options' => self::ENDPOINT_TYPES,
                'default' => ['computer'],
                'required' => false,
                'help' => trans('admin/settings/sync_adapters.sophos_endpoint_types_help'),
            ],
            [
                'key' => 'skip_cloud_instances',
                'label' => trans('admin/settings/sync_adapters.sophos_label_skip_cloud_instances'),
                'type' => 'checkbox',
                'required' => false,
                'help' => trans('admin/settings/sync_adapters.sophos_skip_cloud_instances_help'),
            ],
        ];
    }

    public function presetExtraFields(): array
    {
        return [
            'sophos_endpoint_id' => ['label_key' => 'admin/settings/sync_adapters.extra_endpoint_id'],
            'sophos_endpoint_type' => ['label_key' => 'admin/settings/sync_adapters.extra_device_type'],
            'sophos_health' => ['label_key' => 'admin/settings/sync_adapters.extra_health'],
            'sophos_threat_status' => ['label_key' => 'admin/settings/sync_adapters.extra_threat_status'],
            'sophos_service_status' => ['label_key' => 'admin/settings/sync_adapters.extra_service_status'],
            'sophos_tamper_protection' => ['label_key' => 'admin/settings/sync_adapters.extra_tamper_protection', 'type' => 'boolean'],
            'sophos_encryption_status' => ['label_key' => 'admin/settings/sync_adapters.extra_encryption_status'],
            'sophos_isolation_status' => ['label_key' => 'admin/settings/sync_adapters.extra_isolation_status'],
            'sophos_last_user' => ['label_key' => 'admin/settings/sync_adapters.extra_last_user'],
            'sophos_installed_products' => ['label_key' => 'admin/settings/sync_adapters.extra_installed_products'],
            'sophos_group' => ['label_key' => 'admin/settings/sync_adapters.extra_group'],
        ];
    }

    public function supportsAdminDefinedExtras(): bool
    {
        return true;
    }

    public function pull(): iterable
    {
        $types = $this->allowedEndpointTypes();
        $skipCloud = $this->skipsCloudInstances();

        // One record per machine: key on the serial (or the endpoint id
        // when there isn't one) and keep the most recently seen endpoint.
        $current = [];
        foreach ($this->buildClient()->endpoints() as $endpoint) {
            if (! in_array(strtolower((string) Arr::get($endpoint, 'type')), $types, true)) {
                continue;
            }
            if ($skipCloud && Arr::get($endpoint, 'cloud.provider')) {
                continue;
            }

            $key = self::serialOf($endpoint) ?? 'id:'.Arr::get($endpoint, 'id');
            if (! isset($current[$key]) || self::seenLater($endpoint, $current[$key])) {
                $current[$key] = $endpoint;
            }
        }

        foreach ($current as $endpoint) {
            yield $this->normalize($endpoint);
        }
    }

    private function buildClient(): SophosCentralClient
    {
        return new SophosCentralClient(
            clientId: $this->credential('client_id'),
            clientSecret: $this->credential('client_secret'),
        );
    }

    /**
     * Convert a Sophos endpoint (view=full) into the normalized record.
     *
     * @param  array<string, mixed>  $endpoint
     */
    private function normalize(array $endpoint): HostInventoryRecord
    {
        $serial = self::serialOf($endpoint);
        $endpointId = (string) Arr::get($endpoint, 'id');
        [$os, $osVersion] = self::osNameAndVersion($endpoint);

        $extra = [
            'sophos_endpoint_id' => $endpointId,
            'sophos_endpoint_type' => Arr::get($endpoint, 'type'),
            'sophos_health' => Arr::get($endpoint, 'health.overall'),
            'sophos_threat_status' => Arr::get($endpoint, 'health.threats.status'),
            'sophos_service_status' => Arr::get($endpoint, 'health.services.status'),
            'sophos_tamper_protection' => Arr::get($endpoint, 'tamperProtectionEnabled'),
            'sophos_encryption_status' => Arr::get($endpoint, 'encryption.overallStatus'),
            'sophos_isolation_status' => Arr::get($endpoint, 'isolation.status'),
            'sophos_last_user' => Arr::get($endpoint, 'associatedPerson.name')
                ?? Arr::get($endpoint, 'associatedPerson.viaLogin'),
            'sophos_installed_products' => self::installedProducts($endpoint),
            'sophos_group' => Arr::get($endpoint, 'group.name'),
        ];
        $extra = $this->applyAdminExtras($extra, $endpoint);

        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: $serial ?? $endpointId,
            hostname: Arr::get($endpoint, 'hostname'),
            hardwareSerial: $serial,
            hardwareModel: $this->modelFor($endpoint),
            manufacturer: null,
            primaryMac: Arr::first((array) Arr::get($endpoint, 'macAddresses', [])),
            primaryIp: Arr::first((array) Arr::get($endpoint, 'ipv4Addresses', [])),
            os: $os,
            osVersion: $osVersion,
            lastSeen: $this->parseTimestamp(Arr::get($endpoint, 'lastSeenAt')),
            extra: $extra,
        );
    }

    /**
     * Sophos reports no hardware model, but a new asset needs one, so this
     * is a readable placeholder per platform and type, for example
     * "macOS computer (model unknown)", which an admin can merge into a
     * real model later. When another adapter already supplies real models,
     * set this adapter's model mapping to skip: adopted assets then keep
     * their model, and new assets still get the placeholder.
     *
     * @param  array<string, mixed>  $endpoint
     */
    private function modelFor(array $endpoint): string
    {
        $platform = match (strtolower((string) Arr::get($endpoint, 'os.platform'))) {
            'macos' => 'macOS',
            'linux' => 'Linux',
            'windows' => 'Windows',
            '' => 'Unknown',
            default => (string) Arr::get($endpoint, 'os.platform'),
        };
        $type = Arr::get($endpoint, 'type') ?: 'endpoint';

        return $platform.' '.$type.' (model unknown)';
    }

    /**
     * The hardware serial, or null when Sophos has none.
     *
     * @param  array<string, mixed>  $endpoint
     */
    private static function serialOf(array $endpoint): ?string
    {
        $serial = trim((string) Arr::get($endpoint, 'serialNumber', ''));

        return $serial === '' ? null : $serial;
    }

    /**
     * True when $a was seen more recently than $b. Timestamps are compared
     * as dates, so a different precision or UTC offset can't reorder them.
     * A missing or unreadable timestamp counts as the oldest.
     *
     * @param  array<string, mixed>  $a
     * @param  array<string, mixed>  $b
     */
    private static function seenLater(array $a, array $b): bool
    {
        return self::lastSeenMillis($a) > self::lastSeenMillis($b);
    }

    /**
     * @param  array<string, mixed>  $endpoint
     */
    private static function lastSeenMillis(array $endpoint): int
    {
        $value = Arr::get($endpoint, 'lastSeenAt');
        if (! is_string($value) || $value === '') {
            return 0;
        }

        try {
            return (int) Carbon::parse($value)->valueOf();
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Split Sophos's OS fields into an OS name and a version.
     *
     * Sophos puts both in os.name ("macOS Tahoe 26.6", "Ubuntu 24.04.3 LTS",
     * "Windows 11 Pro") and fills majorVersion, minorVersion and build only
     * some of the time. On Linux those numbers drop Ubuntu's leading zero
     * (24.04 comes back as 24 and 4) or are missing, so the version is read
     * from the name there. On macOS and Windows the numbers are more precise
     * than the name ("macOS Sonoma 14" carries 14.6), so they win.
     *
     * @param  array<string, mixed>  $endpoint
     * @return array{0: ?string, 1: ?string}
     */
    private static function osNameAndVersion(array $endpoint): array
    {
        $name = trim((string) preg_replace('/\s+/', ' ', (string) Arr::get($endpoint, 'os.name', '')));
        $platform = strtolower((string) Arr::get($endpoint, 'os.platform', ''));

        // The version inside the name: a dotted number anywhere ("24.04.3"),
        // or a bare number at the very end ("macOS Sonoma 14"). A bare number
        // mid-name, like the 11 in "Windows 11 Pro", is part of the product name.
        $nameVersion = null;
        if (preg_match('/(?<![\w.])\d+(?:\.\d+)+(?![\w.])/', $name, $match)
            || preg_match('/(?<=\s)\d+$/', $name, $match)) {
            $nameVersion = $match[0];
            $name = trim((string) preg_replace('/\s+/', ' ', (string) preg_replace('/(?<![\w.])'.preg_quote($nameVersion, '/').'(?![\w.])/', '', $name, 1)));
        }

        $numbers = array_filter(
            [Arr::get($endpoint, 'os.majorVersion'), Arr::get($endpoint, 'os.minorVersion'), Arr::get($endpoint, 'os.build')],
            fn ($part) => $part !== null && $part !== '',
        );
        $numericVersion = $numbers === [] ? null : implode('.', $numbers);

        $version = $platform === 'linux'
            ? ($nameVersion ?? $numericVersion)
            : ($numericVersion ?? $nameVersion);

        $os = $name !== '' ? $name : (Arr::get($endpoint, 'os.platform') ?: null);

        return [$os, $version];
    }

    /**
     * Installed Sophos products as a comma-separated list of product codes,
     * for example "coreAgent, endpointProtection, interceptX".
     *
     * @param  array<string, mixed>  $endpoint
     */
    private static function installedProducts(array $endpoint): ?string
    {
        $codes = collect((array) Arr::get($endpoint, 'assignedProducts', []))
            ->filter(fn ($product) => is_array($product) && ($product['status'] ?? null) === 'installed')
            ->pluck('code')
            ->filter()
            ->sort()
            ->values();

        return $codes->isEmpty() ? null : $codes->implode(', ');
    }

    /**
     * The admin's endpoint-type selection, lower-cased. Nothing selected
     * (or nothing saved yet) means computers only, the schema default.
     *
     * @return array<int, string>
     */
    private function allowedEndpointTypes(): array
    {
        try {
            $decoded = json_decode($this->credential('endpoint_types'), true);
        } catch (\Throwable) {
            $decoded = null;
        }

        $types = is_array($decoded)
            ? array_values(array_map('strtolower', array_filter($decoded, 'is_string')))
            : [];

        return $types === [] ? ['computer'] : $types;
    }

    private function skipsCloudInstances(): bool
    {
        try {
            return $this->credential('skip_cloud_instances') === '1';
        } catch (\Throwable) {
            return false;
        }
    }

    private function parseTimestamp(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value);
    }
}
