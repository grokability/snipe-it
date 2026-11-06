<?php

namespace App\Models;

/**
 * Read/write helper for per-instance sync-adapter configuration.
 *
 * Storage is a single JSON blob on the `config` column of
 * SyncAdapterInstance (prior shape was a separate sync_adapter_settings
 * table with one row per key, folded into the parent instance row per
 * review feedback). Adapters call the static methods here to avoid
 * having to json_decode or track dirty state themselves.
 *
 * Value semantics carry over from the old row-per-key design:
 *   - put(id, key, value) sets a value (including explicit null).
 *   - get(id, key, default) returns the stored value or the default
 *     when the key is missing entirely.
 *   - has(id, key) is true only when the key exists AND its value is
 *     not null.
 *   - forget(id, key) removes the key from the blob entirely.
 *
 * Adapters that store secrets (Fleet API tokens, Jamf passwords,
 * Intune client secrets, etc.) are responsible for Crypt::encrypt-ing
 * the value before calling put(). This class treats every value as an
 * opaque scalar and stores it verbatim inside the JSON blob.
 */
class SyncAdapterConfig
{
    // Cache the sync adapters to prevent N+1
    private static array $instanceCache = [];

    public static function get(int $instanceId, string $configKey, mixed $default = null): mixed
    {
        $instance = self::instance($instanceId);
        if ($instance === null) {
            return $default;
        }

        $config = $instance->config ?? [];

        return array_key_exists($configKey, $config) ? $config[$configKey] : $default;
    }

    public static function put(int $instanceId, string $configKey, ?string $value): void
    {
        $instance = self::instance($instanceId);
        if ($instance === null) {
            return;
        }

        $config = $instance->config ?? [];
        $config[$configKey] = $value;
        $instance->config = $config;
        $instance->save();
    }

    public static function has(int $instanceId, string $configKey): bool
    {
        $instance = self::instance($instanceId);
        if ($instance === null) {
            return false;
        }

        $config = $instance->config ?? [];

        return array_key_exists($configKey, $config) && $config[$configKey] !== null;
    }

    public static function forget(int $instanceId, string $configKey): void
    {
        $instance = self::instance($instanceId);
        if ($instance === null) {
            return;
        }

        $config = $instance->config ?? [];
        if (! array_key_exists($configKey, $config)) {
            return;
        }

        unset($config[$configKey]);
        $instance->config = $config;
        $instance->save();
    }

    /**
     * All (config_key => value) pairs stored for this instance. Used
     * by callers that need to enumerate a prefix range of keys
     * (e.g. `group_mapping.*`) rather than looking one up at a time.
     *
     * @return array<string, mixed>
     */
    public static function listForInstance(int $instanceId): array
    {
        return self::instance($instanceId)?->config ?? [];
    }

    public static function flushInstanceCache(): void
    {
        self::$instanceCache = [];
    }

    /**
     * Seed the per-instance cache from a caller that has already
     * hydrated the SyncAdapterInstance rows (typically the settings
     * controller, which pulls every instance up-front to render the
     * tab strip). Without this hint, each per-tab SyncAdapterConfig::get
     * call fires its own SyncAdapterInstance::find and the page ends
     * up with one duplicate SELECT per adapter.
     */
    public static function warmInstanceCache(iterable $instances): void
    {
        foreach ($instances as $instance) {
            if ($instance instanceof SyncAdapterInstance) {
                self::$instanceCache[(int) $instance->id] = $instance;
            }
        }
    }

    private static function instance(int $instanceId): ?SyncAdapterInstance
    {
        if (array_key_exists($instanceId, self::$instanceCache)) {
            return self::$instanceCache[$instanceId];
        }

        return self::$instanceCache[$instanceId] = SyncAdapterInstance::find($instanceId);
    }
}
