<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Storage for per-instance sync-adapter configuration. Row-per-
 * (sync_adapter_instance_id, config_key) so every adapter instance
 * carries its own config bag and no future adapter has to touch the
 * schema. Adapters that store secrets (Fleet API tokens, Jamf passwords,
 * Intune client secrets, etc.) are responsible for Crypt::encrypt-ing
 * the value before calling put(). This class treats every value as an
 * opaque string.
 */
class SyncAdapterConfig extends Model
{
    protected $table = 'sync_adapter_settings';

    protected $fillable = ['sync_adapter_instance_id', 'config_key', 'value'];

    public static function get(int $instanceId, string $configKey, mixed $default = null): mixed
    {
        $value = self::query()
            ->where('sync_adapter_instance_id', $instanceId)
            ->where('config_key', $configKey)
            ->value('value');

        return $value ?? $default;
    }

    public static function put(int $instanceId, string $configKey, ?string $value): void
    {
        self::query()->updateOrCreate(
            ['sync_adapter_instance_id' => $instanceId, 'config_key' => $configKey],
            ['value' => $value],
        );
    }

    public static function has(int $instanceId, string $configKey): bool
    {
        return self::query()
            ->where('sync_adapter_instance_id', $instanceId)
            ->where('config_key', $configKey)
            ->whereNotNull('value')
            ->exists();
    }

    public static function forget(int $instanceId, string $configKey): void
    {
        self::query()
            ->where('sync_adapter_instance_id', $instanceId)
            ->where('config_key', $configKey)
            ->delete();
    }

    /**
     * All (config_key => value) pairs stored for this instance. Used
     * by callers that need to enumerate a prefix range of keys
     * (e.g. `group_mapping.*`) rather than looking one up at a time.
     *
     * @return array<string, ?string>
     */
    public static function listForInstance(int $instanceId): array
    {
        return self::query()
            ->where('sync_adapter_instance_id', $instanceId)
            ->pluck('value', 'config_key')
            ->all();
    }
}
