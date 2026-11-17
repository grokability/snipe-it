<?php

namespace Tests\Feature\Support;

use App\Models\CustomField;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\MySqlDatabaseSnapshot;
use Tests\TestCase;

class MySqlDatabaseSnapshotTest extends TestCase
{
    public function test_restoring_the_baseline_removes_test_schema_and_data_but_preserves_migration_rows(): void
    {
        if (! MySqlDatabaseSnapshot::isSupported(DB::connection())) {
            $this->markTestSkipped('Snapshot restoration requires MySQL/MariaDB and the MySQL client tools.');
        }

        $maintenanceTypes = DB::table('maintenance_types')->orderBy('id')->get()->toArray();
        $customFields = DB::table('custom_fields')->orderBy('id')->get()->toArray();
        $migrations = DB::table('migrations')->orderBy('id')->get()->toArray();
        $this->assertNotEmpty($maintenanceTypes);

        $field = CustomField::factory()->create();
        $column = $field->db_column_name();
        $this->assertTrue(Schema::hasColumn('assets', $column));
        DB::table('maintenance_types')->delete();

        $this->withoutMockingConsoleOutput();
        $this->migrateDatabases();

        $this->assertFalse(Schema::hasColumn('assets', $column));
        $this->assertEquals($customFields, DB::table('custom_fields')->orderBy('id')->get()->toArray());
        $this->assertDatabaseCount('settings', 0);
        $this->assertDatabaseCount('sync_adapter_instances', 0);
        $this->assertEquals($maintenanceTypes, DB::table('maintenance_types')->orderBy('id')->get()->toArray());
        $this->assertEquals($migrations, DB::table('migrations')->orderBy('id')->get()->toArray());
    }

    public function test_file_cache_locks_do_not_leak_into_a_new_test_cache(): void
    {
        $firstPath = config('cache.stores.file.path');
        Cache::store('file')->add('import:1:running', true, 1800);
        $this->assertTrue(Cache::store('file')->has('import:1:running'));

        $this->isolateFileCache();

        $this->assertNotSame($firstPath, config('cache.stores.file.path'));
        $this->assertFalse(Cache::store('file')->has('import:1:running'));
    }
}
