<?php

namespace Tests\Feature\Users;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regression tests for GHSA-f54q-mj8g-xmw6. `User::isManagerOf()` walked
 * the manager chain with no cycle guard and no depth cap, so a two-node
 * cycle (A manages B, B manages A) made any `/api/v1/account/eulas?user_id
 * =<cycle_member>` call spin until PHP's memory_limit killed it, pinning
 * a worker and a CPU core and generating a sustained stream of DB point
 * queries. Fix is paired. The walk now carries a visited-set + depth cap
 * (primary DoS defense covering legacy rows), and `User::$rules` now
 * applies `non_circular:users,id` on `manager_id` (prevents new cycles
 * at save time, matches the precedent on `Location::$rules['parent_id']`).
 */
class ManagerChainCycleGuardTest extends TestCase
{
    public function test_is_manager_of_returns_false_on_a_two_node_cycle(): void
    {
        [$a, $b, $c] = User::factory()->count(3)->create();

        // Direct DB write bypasses the validator so we can simulate a
        // legacy cycle row that pre-dates the fix.
        DB::table('users')->where('id', $a->id)->update(['manager_id' => $b->id]);
        DB::table('users')->where('id', $b->id)->update(['manager_id' => $a->id]);

        $this->assertFalse($c->isManagerOf($a->fresh()));
        $this->assertFalse($c->isManagerOf($b->fresh()));
    }

    public function test_is_manager_of_returns_false_on_a_three_node_cycle(): void
    {
        [$a, $b, $c, $outsider] = User::factory()->count(4)->create();

        DB::table('users')->where('id', $a->id)->update(['manager_id' => $b->id]);
        DB::table('users')->where('id', $b->id)->update(['manager_id' => $c->id]);
        DB::table('users')->where('id', $c->id)->update(['manager_id' => $a->id]);

        $this->assertFalse($outsider->isManagerOf($a->fresh()));
    }

    public function test_is_manager_of_returns_true_on_direct_management(): void
    {
        [$manager, $report] = User::factory()->count(2)->create();
        DB::table('users')->where('id', $report->id)->update(['manager_id' => $manager->id]);

        $this->assertTrue($manager->isManagerOf($report->fresh()));
    }

    public function test_is_manager_of_returns_true_on_indirect_three_level_chain(): void
    {
        // Topmost manages middle, middle manages direct, direct manages
        // bottom. isManagerOf(bottom) from topmost must still return true.
        [$topmost, $middle, $direct, $bottom] = User::factory()->count(4)->create();
        DB::table('users')->where('id', $middle->id)->update(['manager_id' => $topmost->id]);
        DB::table('users')->where('id', $direct->id)->update(['manager_id' => $middle->id]);
        DB::table('users')->where('id', $bottom->id)->update(['manager_id' => $direct->id]);

        $this->assertTrue($topmost->isManagerOf($bottom->fresh()));
    }

    public function test_is_manager_of_returns_false_beyond_the_depth_cap(): void
    {
        // Build a 52-hop legitimate chain. The 50-hop cap means a user
        // past the cap is not findable even though the chain is cycle-
        // free. Trade-off is deliberate: anything that deep is
        // operationally unusual, and the cap is the primary bound on
        // worst-case walk cost. Matches the non_circular validator cap.
        $users = User::factory()->count(52)->create();
        for ($i = 1; $i < 52; $i++) {
            DB::table('users')->where('id', $users[$i]->id)->update([
                'manager_id' => $users[$i - 1]->id,
            ]);
        }

        $this->assertFalse($users->first()->isManagerOf($users->last()->fresh()));
    }

    public function test_validator_rejects_a_direct_self_manager_cycle(): void
    {
        // Pre-fix behavior preserved: cant_manage_self already caught
        // the one-node cycle. Guards against a refactor that drops it
        // while adding non_circular.
        $user = User::factory()->create();
        $user->manager_id = $user->id;

        $this->assertFalse($user->isValid());
    }

    public function test_validator_rejects_a_two_node_manager_cycle(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create(['manager_id' => $a->id]);

        $a->manager_id = $b->id;

        // non_circular walks the chain starting from the proposed value
        // and refuses to save when it returns to $a. cant_manage_self
        // alone would have let this through.
        $this->assertFalse($a->isValid());
    }

    public function test_validator_allows_a_manager_whose_chain_contains_a_legacy_cycle_elsewhere(): void
    {
        // A pre-existing A <-> B cycle that does not involve the user
        // being edited (the scenario in #20034, typically a SCIM-synced
        // directory). Before this test, non_circular walked into the
        // cycle and spun until the depth cap, so *any* manager update
        // whose chain reached the cycle failed with "The manager id
        // must not create a circular reference" even though the edit
        // created no cycle.
        [$a, $b] = User::factory()->count(2)->create();
        DB::table('users')->where('id', $a->id)->update(['manager_id' => $b->id]);
        DB::table('users')->where('id', $b->id)->update(['manager_id' => $a->id]);

        $outsider = User::factory()->create();
        $outsider->manager_id = $a->id;

        $this->assertTrue($outsider->isValid());
    }

    public function test_eulas_endpoint_returns_safely_when_target_is_in_a_cycle(): void
    {
        // End-to-end guard. Pre-fix this request pinned the worker for
        // ~17 to 20 seconds then 500'd with "Allowed memory size
        // exhausted". Post-fix it must return in bounded time with the
        // normal error envelope. The caller is a self.api-only user
        // (the default permission set for every Snipe-IT user), which
        // is what makes the issue reachable from a routine account.
        Setting::getSettings()->update(['manager_view_enabled' => 1]);

        [$a, $b] = User::factory()->count(2)->create();
        DB::table('users')->where('id', $a->id)->update(['manager_id' => $b->id]);
        DB::table('users')->where('id', $b->id)->update(['manager_id' => $a->id]);

        $caller = User::factory()->create();

        $start = microtime(true);
        $this->actingAsForApi($caller)
            ->getJson(route('api.self.eulas', ['user_id' => $a->id]))
            ->assertOk();
        $elapsed = microtime(true) - $start;

        // Generous ceiling. Pre-fix hit the 17s PHP memory kill, so a
        // 5s cap is comfortably below while still giving CI breathing
        // room on a slow runner.
        $this->assertLessThan(5.0, $elapsed, 'eulas endpoint should not spin on a manager cycle');
    }
}
