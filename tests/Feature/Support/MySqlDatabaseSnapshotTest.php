<?php

use App\Models\CustomField;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\MySqlDatabaseSnapshot;

test('restoring the baseline removes test schema and data but preserves migration rows', function () {
    if (! MySqlDatabaseSnapshot::isSupported(DB::connection())) {
        $this->markTestSkipped('Snapshot restoration requires MySQL/MariaDB and the MySQL client tools.');
    }

    $maintenanceTypes = DB::table('maintenance_types')->orderBy('id')->get()->toArray();
    $customFields = DB::table('custom_fields')->orderBy('id')->get()->toArray();
    $migrations = DB::table('migrations')->orderBy('id')->get()->toArray();
    expect($maintenanceTypes)->not->toBeEmpty();

    $field = CustomField::factory()->create();
    $column = $field->db_column_name();
    expect(Schema::hasColumn('assets', $column))->toBeTrue();
    DB::table('maintenance_types')->delete();

    $this->withoutMockingConsoleOutput();
    $this->migrateDatabases();

    expect(Schema::hasColumn('assets', $column))->toBeFalse();
    expect(DB::table('custom_fields')->orderBy('id')->get()->toArray())->toEqual($customFields);
    $this->assertDatabaseCount('settings', 0);
    $this->assertDatabaseCount('sync_adapter_instances', 0);
    expect(DB::table('maintenance_types')->orderBy('id')->get()->toArray())->toEqual($maintenanceTypes);
    expect(DB::table('migrations')->orderBy('id')->get()->toArray())->toEqual($migrations);
});

test('file cache locks do not leak into a new test cache', function () {
    $firstPath = config('cache.stores.file.path');
    Cache::store('file')->add('import:1:running', true, 1800);
    expect(Cache::store('file')->has('import:1:running'))->toBeTrue();

    $this->isolateFileCache();

    $this->assertNotSame($firstPath, config('cache.stores.file.path'));
    expect(Cache::store('file')->has('import:1:running'))->toBeFalse();
});
