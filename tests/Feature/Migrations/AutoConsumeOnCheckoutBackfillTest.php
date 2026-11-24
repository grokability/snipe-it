<?php

namespace Tests\Feature\Migrations;

use App\Models\Consumable;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Covers the per-parent backfill heuristic in the migration that
 * added consumables.auto_consume_on_checkout.
 *
 * The migration runs once on test setup like every other migration,
 * so these tests drop the column, insert legacy-shaped data, and
 * re-run up() to pin the heuristic end-to-end.
 */
class AutoConsumeOnCheckoutBackfillTest extends TestCase
{
    private function rerunMigration(): void
    {
        $migration = require database_path('migrations/2026_11_24_182551_add_auto_consume_on_checkout_to_consumables_table.php');
        $migration->down();
        $migration->up();
    }

    public function test_consumable_with_a_consumed_row_is_flipped_to_outstanding_workflow(): void
    {
        $user = User::factory()->create();
        $consumable = Consumable::factory()->create();

        DB::table('consumables_users')->insert([
            'consumable_id' => $consumable->id,
            'assigned_to' => $user->id,
            'created_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
            'consumed_at' => now(),
        ]);

        $this->rerunMigration();

        $this->assertFalse((bool) DB::table('consumables')->where('id', $consumable->id)->value('auto_consume_on_checkout'));
    }

    public function test_consumable_with_only_outstanding_rows_keeps_default_true(): void
    {
        $user = User::factory()->create();
        $consumable = Consumable::factory()->create();

        DB::table('consumables_users')->insert([
            'consumable_id' => $consumable->id,
            'assigned_to' => $user->id,
            'created_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
            'consumed_at' => null,
        ]);

        $this->rerunMigration();

        $this->assertTrue((bool) DB::table('consumables')->where('id', $consumable->id)->value('auto_consume_on_checkout'));
    }

    public function test_outstanding_rows_under_flag_true_parents_are_backfilled(): void
    {
        $user = User::factory()->create();
        $consumable = Consumable::factory()->create();
        $created = now()->subDays(3);

        DB::table('consumables_users')->insert([
            'consumable_id' => $consumable->id,
            'assigned_to' => $user->id,
            'created_by' => $user->id,
            'created_at' => $created,
            'updated_at' => $created,
            'consumed_at' => null,
        ]);

        $this->rerunMigration();

        $pivot = DB::table('consumables_users')->where('consumable_id', $consumable->id)->first();
        $this->assertNotNull($pivot->consumed_at);
        $this->assertSame($created->format('Y-m-d H:i:s'), (string) $pivot->consumed_at);
    }

    public function test_outstanding_rows_under_flag_false_parents_stay_outstanding(): void
    {
        $user = User::factory()->create();
        $consumable = Consumable::factory()->create();

        // One consumed row flips the parent to false. Another outstanding
        // row on the same parent must stay outstanding (not silently
        // backfilled under the admin).
        DB::table('consumables_users')->insert([
            [
                'consumable_id' => $consumable->id,
                'assigned_to' => $user->id,
                'created_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
                'consumed_at' => now(),
            ],
            [
                'consumable_id' => $consumable->id,
                'assigned_to' => $user->id,
                'created_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
                'consumed_at' => null,
            ],
        ]);

        $this->rerunMigration();

        $outstandingCount = DB::table('consumables_users')
            ->where('consumable_id', $consumable->id)
            ->whereNull('consumed_at')
            ->count();
        $this->assertSame(1, $outstandingCount);
    }

    public function test_sibling_parents_route_independently(): void
    {
        $user = User::factory()->create();
        $tonerCartridge = Consumable::factory()->create();
        $keyboard = Consumable::factory()->create();

        DB::table('consumables_users')->insert([
            // Toner: no mark-consumed history. Flag stays true,
            // outstanding gets backfilled.
            [
                'consumable_id' => $tonerCartridge->id,
                'assigned_to' => $user->id,
                'created_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
                'consumed_at' => null,
            ],
            // Keyboard: admin actively using outstanding workflow.
            // Flag flips to false, outstanding row stays outstanding.
            [
                'consumable_id' => $keyboard->id,
                'assigned_to' => $user->id,
                'created_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
                'consumed_at' => now(),
            ],
            [
                'consumable_id' => $keyboard->id,
                'assigned_to' => $user->id,
                'created_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
                'consumed_at' => null,
            ],
        ]);

        $this->rerunMigration();

        $this->assertTrue((bool) DB::table('consumables')->where('id', $tonerCartridge->id)->value('auto_consume_on_checkout'));
        $this->assertFalse((bool) DB::table('consumables')->where('id', $keyboard->id)->value('auto_consume_on_checkout'));
        $this->assertSame(0, DB::table('consumables_users')->where('consumable_id', $tonerCartridge->id)->whereNull('consumed_at')->count());
        $this->assertSame(1, DB::table('consumables_users')->where('consumable_id', $keyboard->id)->whereNull('consumed_at')->count());
    }

    public function test_column_default_is_true_on_fresh_install(): void
    {
        // Sanity-pin the Blueprint->default(true) so the model-level
        // "assume true unless admin opts out" assumption on fresh
        // installs can't silently regress.
        $this->assertTrue(Schema::hasColumn('consumables', 'auto_consume_on_checkout'));
        $consumable = Consumable::factory()->create();
        $this->assertTrue((bool) DB::table('consumables')->where('id', $consumable->id)->value('auto_consume_on_checkout'));
    }
}
