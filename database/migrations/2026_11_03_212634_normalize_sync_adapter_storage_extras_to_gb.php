<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const RENAMES = [
        'mapping.intune_total_storage_bytes' => 'mapping.intune_total_storage_gb',
        'mapping.intune_free_storage_bytes' => 'mapping.intune_free_storage_gb',
        'mapping.intune_physical_memory_bytes' => 'mapping.intune_physical_memory_gb',
        'mapping.abm_mdm_storage_total' => 'mapping.abm_mdm_storage_total_gb',
        'mapping.abm_mdm_storage_free' => 'mapping.abm_mdm_storage_free_gb',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('sync_adapter_instances')) {
            return;
        }

        $targetColumns = [];

        foreach (DB::table('sync_adapter_instances')->get() as $instance) {
            $config = json_decode($instance->config ?? '[]', true);
            if (! is_array($config)) {
                continue;
            }

            $dirty = false;
            foreach (self::RENAMES as $oldKey => $newKey) {
                if (! array_key_exists($oldKey, $config)) {
                    continue;
                }
                $target = $config[$oldKey];
                if (is_string($target) && str_starts_with($target, '_snipeit_')) {
                    $targetColumns[$target] = true;
                }
                $config[$newKey] = $target;
                unset($config[$oldKey]);
                $dirty = true;
            }

            if ($dirty) {
                DB::table('sync_adapter_instances')
                    ->where('id', $instance->id)
                    ->update(['config' => json_encode($config)]);
            }
        }

        /*
         * Convert the stored byte values in each affected custom-field
         * column to GB.
         */
        foreach (array_keys($targetColumns) as $column) {
            if (! Schema::hasColumn('assets', $column)) {
                continue;
            }

            DB::table('assets')
                ->whereNotNull($column)
                ->update([
                    $column => DB::raw("$column / 1000000000"),
                ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('sync_adapter_instances')) {
            return;
        }

        $reverse = array_flip(self::RENAMES);

        foreach (DB::table('sync_adapter_instances')->get() as $instance) {
            $config = json_decode($instance->config ?? '[]', true);
            if (! is_array($config)) {
                continue;
            }

            $dirty = false;
            foreach ($reverse as $newKey => $oldKey) {
                if (! array_key_exists($newKey, $config)) {
                    continue;
                }
                $config[$oldKey] = $config[$newKey];
                unset($config[$newKey]);
                $dirty = true;
            }

            if ($dirty) {
                DB::table('sync_adapter_instances')
                    ->where('id', $instance->id)
                    ->update(['config' => json_encode($config)]);
            }
        }
    }
};
