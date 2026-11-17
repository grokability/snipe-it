<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the indexes the Api\ConsumablesController::index query hot path
 * relies on. On installs with hundreds of thousands of consumables and
 * a correspondingly large consumables_users pivot, the two withCount
 * subqueries (consumables_users_count, outstanding_assignments_count)
 * fire once per paginated row, each correlating on
 * consumables_users.consumable_id. Without an index that is a full
 * table scan of the pivot per selected row, which blows past the
 * request timeout on 680k-consumable fleets.
 *
 * Indexes added:
 *   - consumables_users.consumable_id        (withCount correlation)
 *   - consumables_users.assigned_to          (unconstrainedUsers JOIN)
 *   - consumables(archived, created_at)      (default list query
 *                                             filter + sort)
 *
 * The migration itself can take minutes on affected installs because
 * consumables_users is probably tens of millions of rows. Run during a
 * maintenance window on large fleets.
 *
 * Each add is guarded by a hasColumn + doesntHaveIndex check so re-runs
 * on installs that already added any of these by hand are no-ops instead
 * of hard failures. The corresponding down() drops each index only if it
 * exists.
 */
return new class extends Migration
{
    private const TARGETS = [
        [
            'table' => 'consumables_users',
            'columns' => 'consumable_id',
            'index' => 'consumables_users_consumable_id_index',
        ],
        [
            'table' => 'consumables_users',
            'columns' => 'assigned_to',
            'index' => 'consumables_users_assigned_to_index',
        ],
        [
            'table' => 'consumables',
            'columns' => ['archived', 'created_at'],
            'index' => 'consumables_archived_created_at_index',
        ],
    ];

    public function up(): void
    {
        foreach (self::TARGETS as $target) {
            $columns = (array) $target['columns'];
            foreach ($columns as $column) {
                if (! Schema::hasColumn($target['table'], $column)) {
                    continue 2;
                }
            }
            if ($this->indexExists($target['table'], $target['index'])) {
                continue;
            }

            Schema::table($target['table'], function (Blueprint $t) use ($target) {
                $t->index($target['columns'], $target['index']);
            });
        }
    }

    public function down(): void
    {
        foreach (self::TARGETS as $target) {
            if (! $this->indexExists($target['table'], $target['index'])) {
                continue;
            }

            Schema::table($target['table'], function (Blueprint $t) use ($target) {
                $t->dropIndex($target['index']);
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        return collect(Schema::getIndexes($table))
            ->pluck('name')
            ->contains($index);
    }
};
