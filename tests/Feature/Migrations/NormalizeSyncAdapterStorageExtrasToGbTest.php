<?php

namespace Tests\Feature\Migrations;

use App\Models\SyncAdapterInstance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NormalizeSyncAdapterStorageExtrasToGbTest extends TestCase
{
    private function runMigration(): void
    {
        $migration = require database_path('migrations/2026_11_03_212634_normalize_sync_adapter_storage_extras_to_gb.php');
        $migration->up();
    }

    private function freshCustomFieldColumn(string $suffix): string
    {
        $column = '_snipeit_test_'.str_replace(' ', '_', strtolower($suffix)).'_'.uniqid();
        Schema::table('assets', function ($table) use ($column) {
            $table->text($column)->nullable();
        });

        return $column;
    }

    public function test_renames_byte_source_mapping_keys_to_gb_in_config_json(): void
    {
        $column = $this->freshCustomFieldColumn('Storage Test A');
        $instance = SyncAdapterInstance::create([
            'slug' => 'test-intune-'.uniqid(),
            'adapter_type' => 'intune',
            'label' => 'Test Intune',
            'config' => [
                'mapping.intune_total_storage_bytes' => $column,
                'mapping.intune_free_storage_bytes' => $column,
                'mapping.intune_physical_memory_bytes' => $column,
                'mapping.abm_mdm_storage_total' => $column,
                'mapping.abm_mdm_storage_free' => $column,
                'mapping.unrelated_field' => 'something_else',
            ],
        ]);

        $this->runMigration();

        $instance->refresh();
        $config = $instance->config;

        $this->assertArrayNotHasKey('mapping.intune_total_storage_bytes', $config);
        $this->assertArrayNotHasKey('mapping.intune_free_storage_bytes', $config);
        $this->assertArrayNotHasKey('mapping.intune_physical_memory_bytes', $config);
        $this->assertArrayNotHasKey('mapping.abm_mdm_storage_total', $config);
        $this->assertArrayNotHasKey('mapping.abm_mdm_storage_free', $config);

        $this->assertSame($column, $config['mapping.intune_total_storage_gb']);
        $this->assertSame($column, $config['mapping.intune_free_storage_gb']);
        $this->assertSame($column, $config['mapping.intune_physical_memory_gb']);
        $this->assertSame($column, $config['mapping.abm_mdm_storage_total_gb']);
        $this->assertSame($column, $config['mapping.abm_mdm_storage_free_gb']);

        // Non-renamed mappings survive untouched.
        $this->assertSame('something_else', $config['mapping.unrelated_field']);
    }

    public function test_converts_stored_byte_values_in_mapped_columns_to_gb(): void
    {
        $column = $this->freshCustomFieldColumn('Storage Test B');

        SyncAdapterInstance::create([
            'slug' => 'test-intune-'.uniqid(),
            'adapter_type' => 'intune',
            'label' => 'Test Intune',
            'config' => [
                'mapping.intune_total_storage_bytes' => $column,
            ],
        ]);

        // Simulate a previously-synced asset with a byte value in the
        // custom field column. 494 GB = 494000000000 bytes.
        $assetId = DB::table('assets')->insertGetId([
            'name' => 'test-mac-01',
            'asset_tag' => 'TEST-0001',
            $column => '494000000000',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->runMigration();

        $value = DB::table('assets')->where('id', $assetId)->value($column);
        $this->assertEquals(494, (float) $value);
    }

    public function test_skips_columns_that_are_not_on_the_assets_table(): void
    {
        /*
         * Instance mapping references a custom-field column that no
         * longer exists on assets (admin deleted the custom field).
         * The migration should rename the mapping key without choking.
         */
        $slug = 'test-abm-'.uniqid();
        SyncAdapterInstance::create([
            'slug' => $slug,
            'adapter_type' => 'abm',
            'label' => 'Test AxM',
            'config' => [
                'mapping.abm_mdm_storage_total' => '_snipeit_nonexistent_column_999',
            ],
        ]);

        $this->runMigration();

        $instance = SyncAdapterInstance::where('slug', $slug)->firstOrFail();
        $this->assertArrayHasKey('mapping.abm_mdm_storage_total_gb', $instance->config);
        $this->assertArrayNotHasKey('mapping.abm_mdm_storage_total', $instance->config);
    }

    public function test_leaves_null_custom_field_values_alone(): void
    {
        $column = $this->freshCustomFieldColumn('Storage Test C');

        SyncAdapterInstance::create([
            'slug' => 'test-intune-'.uniqid(),
            'adapter_type' => 'intune',
            'label' => 'Test Intune',
            'config' => [
                'mapping.intune_total_storage_bytes' => $column,
            ],
        ]);

        $assetId = DB::table('assets')->insertGetId([
            'name' => 'never-synced',
            'asset_tag' => 'TEST-NULL',
            $column => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->runMigration();

        $this->assertNull(DB::table('assets')->where('id', $assetId)->value($column));
    }
}
