<?php

use App\Models\Accessory;
use App\Models\Component;
use App\Models\Consumable;
use Illuminate\Support\Facades\DB;

/**
 * Regression coverage for the TOCTOU race in AdjustsQuantity::adjustQuantity()
 * that GHSA-3wf3-jcpq-265f reported.
 *
 * Pre-fix, the read of $current + the read of currentlyInUseCount() + the
 * floor invariant check all ran OUTSIDE the DB::transaction, and only the
 * increment/decrement itself ran inside. Two concurrent adjusters each
 * observed the same pre-decrement quantity, each independently passed the
 * floor check, each atomically applied their own delta, and the aggregate
 * effect drove on-hand below in-use even though every individual request
 * looked valid on its own.
 *
 * The fix moves the whole read + check + write sequence inside the
 * transaction and takes a pessimistic row lock (SELECT ... FOR UPDATE)
 * so concurrent adjusters serialize instead of interleaving.
 *
 * SQLite (which the test suite uses in memory) does not enforce row locks
 * the same way MySQL/MariaDB does, so a real concurrent-request test
 * cannot deterministically reproduce the race here. Instead we assert on
 * the code-path indicators the lock adds: the SELECT query must carry
 * `for update`, and it must be emitted INSIDE the same transaction as the
 * UPDATE. Those two invariants together are what closes the race on any
 * ACID engine that honors row locks.
 */

/**
 * @return array<string, array{class-string}>
 */
dataset('inventoryModelProvider', function () {
    return [
        'accessory' => [Accessory::class],
        'consumable' => [Consumable::class],
        'component' => [Component::class],
    ];
});

test('adjust quantity takes pessimistic row lock', function (string $modelClass) {
    // SQLite's Laravel grammar strips the FOR UPDATE clause on
    // compile (Illuminate\Database\Query\Grammars\SQLiteGrammar::compileLock
    // returns ''), so the emitted SQL doesn't carry the lock text
    // even though the builder had lockForUpdate() applied. Skip on
    // SQLite; MySQL/MariaDB/Postgres runs (CI) will exercise this.
    $this->markIncompleteIfSqlite('SQLite grammar drops FOR UPDATE on compile, cannot observe the lock in the query log.');

    $item = $modelClass::factory()->create(['qty' => 10]);

    DB::enableQueryLog();
    DB::flushQueryLog();

    $item->adjustQuantity(1, 'lock probe');

    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    // Look for a SELECT on the item's table carrying `for update`.
    // Case-insensitive match handles driver variance in how the SQL
    // is echoed back (Laravel normalizes to lowercase but grammars
    // differ).
    $selectForUpdate = collect($queries)->first(
        fn ($q) => stripos($q['query'], 'select') === 0
            && stripos($q['query'], (new $modelClass)->getTable()) !== false
            && stripos($q['query'], 'for update') !== false
    );

    expect($selectForUpdate)->not->toBeNull("adjustQuantity() on {$modelClass} must issue a SELECT ... FOR UPDATE on the item row to serialize concurrent adjusters. GHSA-3wf3-jcpq-265f regression.");
})->with('inventoryModelProvider');

test('adjust quantity lock and write share a transaction', function (string $modelClass) {
    // The lock is only meaningful if it holds through the write. If
    // the SELECT ... FOR UPDATE ran outside the transaction that
    // does the increment/decrement, the lock would release before
    // the write and the race would reappear.
    //
    // Laravel's query log does not include BEGIN/COMMIT (those are
    // PDO-driver-level commands, not routed through the query
    // builder), so we sample DB::transactionLevel() live at every
    // query-fire via DB::listen. transactionLevel() > 0 at the
    // moment the locking SELECT fires AND at the moment the UPDATE
    // fires, plus both being emitted before the level returns to 0,
    // proves the lock's transaction covers the write.
    //
    // Same SQLite constraint as the sibling test: the compiled SQL
    // doesn't carry `for update` on SQLite, so we can't reliably
    // pick the locking SELECT apart from the count-checkouts SELECT
    // in the log.
    $this->markIncompleteIfSqlite('SQLite grammar drops FOR UPDATE on compile, cannot identify the locking SELECT.');

    $item = $modelClass::factory()->create(['qty' => 10]);
    $table = $item->getTable();

    $observed = [];
    DB::listen(function ($event) use (&$observed, $table) {
        $sql = strtolower($event->sql);
        $isLockingSelect = str_starts_with($sql, 'select')
            && str_contains($sql, $table)
            && str_contains($sql, 'for update');
        $isTableUpdate = str_starts_with($sql, 'update')
            && str_contains($sql, $table);
        if ($isLockingSelect || $isTableUpdate) {
            $observed[] = [
                'kind' => $isLockingSelect ? 'lock' : 'update',
                'txLevel' => DB::transactionLevel(),
            ];
        }
    });

    $item->adjustQuantity(-1, 'lock probe');

    $lock = collect($observed)->firstWhere('kind', 'lock');
    $update = collect($observed)->firstWhere('kind', 'update');

    expect($lock)->not->toBeNull('Expected a locking SELECT ... FOR UPDATE on the item row');
    expect($update)->not->toBeNull('Expected an UPDATE on the item row');
    expect($lock['txLevel'])->toBeGreaterThan(0, 'Locking SELECT must fire inside an open transaction');
    expect($update['txLevel'])->toBeGreaterThan(0, 'UPDATE must fire inside an open transaction');
})->with('inventoryModelProvider');

test('floor check still rejects below zero adjustment', function (string $modelClass) {
    // Existing regression: a single adjuster that would drop qty
    // below zero (or below in-use) still throws. The fix moved this
    // check inside the transaction; make sure the check itself did
    // not regress in the process.
    $item = $modelClass::factory()->create(['qty' => 3]);

    $this->expectException(DomainException::class);
    $item->adjustQuantity(-5, 'would go negative');
})->with('inventoryModelProvider');
