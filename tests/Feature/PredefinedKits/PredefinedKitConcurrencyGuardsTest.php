<?php

namespace Tests\Feature\PredefinedKits;

use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Actionlog;
use App\Models\Consumable;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use App\Services\PredefinedKitCheckoutService;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;
use Tests\TestCase;

class PredefinedKitConcurrencyGuardsTest extends TestCase
{
    public function test_license_seat_race_skips_a_seat_already_claimed_between_read_and_write(): void
    {
        [$actor, $target] = [User::factory()->create(), User::factory()->create()];
        $racingWinner = User::factory()->create();

        $license = License::factory()->create(['seats' => 1]);
        $license->refresh()->load('freeSeats');
        $seat = $license->freeSeats->first();
        $this->assertNotNull($seat, 'License factory must produce a free seat for the test setup.');

        // A racing request has already claimed the same seat AFTER
        // this caller's getLicenseSeatsToAdd() but BEFORE its
        // saveToDb() transaction opens.
        LicenseSeat::whereKey($seat->id)->update(['assigned_to' => $racingWinner->id]);

        $service = new PredefinedKitCheckoutService;
        $errors = $this->invokeSaveToDb($service, $target, $actor, [], [$seat], [], []);

        $this->assertNotEmpty($errors, 'The guard must record an operator-visible error when the seat was claimed by a racing request.');
        $this->assertSame(
            $racingWinner->id,
            LicenseSeat::whereKey($seat->id)->value('assigned_to'),
            'The seat must retain the racing winner. The stale caller must not overwrite it.',
        );
        $this->assertSame(
            0,
            Actionlog::query()->where('target_id', $target->id)->where('item_id', $seat->id)->count(),
            'No CheckoutableCheckedOut action_log row may be written for the caller that lost the race.',
        );
    }

    public function test_consumable_race_skips_a_unit_already_consumed_between_read_and_write(): void
    {
        [$actor, $target] = [User::factory()->create(), User::factory()->create()];
        $racingWinner = User::factory()->create();

        // Single-unit consumable so a racing checkout exhausts capacity.
        $consumable = Consumable::factory()->create(['qty' => 1]);

        // Stale snapshot the caller built pre-transaction: the
        // consumable was reported as having one unit remaining and
        // the kit demands one, so it passed the pre-check.
        $stale = Consumable::query()->find($consumable->id);
        $stale->setRelation('pivot', (object) ['quantity' => 1]);

        // Racing request has consumed the only unit before this
        // caller's transaction opens. Direct pivot insert rather than
        // ->attach() because the kit service's own attach shape has a
        // pre-existing FK-column mismatch that trips SQLite. Simulating
        // the winning state at the row level sidesteps that unrelated
        // bug and keeps the test focused on the guard behavior.
        DB::table('consumables_users')->insert([
            'consumable_id' => $consumable->id,
            'assigned_to' => $racingWinner->id,
            'created_by' => $actor->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = new PredefinedKitCheckoutService;
        $errors = $this->invokeSaveToDb($service, $target, $actor, [], [], [$stale], []);

        $this->assertNotEmpty($errors, 'The guard must record an error when a racing request has already consumed the unit.');
        $this->assertSame(
            0,
            $consumable->fresh()->numRemaining(),
            'Consumable must remain at zero remaining. The guard must not oversubscribe.',
        );
        $this->assertSame(
            1,
            $consumable->users()->count(),
            'Only the racing winner may hold a pivot row. The stale caller must not add a second.',
        );
    }

    public function test_accessory_race_skips_a_unit_already_consumed_between_read_and_write(): void
    {
        [$actor, $target] = [User::factory()->create(), User::factory()->create()];
        $racingWinner = User::factory()->create();

        $accessory = Accessory::factory()->create(['qty' => 1]);

        $stale = Accessory::query()->find($accessory->id);
        $stale->setRelation('pivot', (object) ['quantity' => 1]);

        // Racing request has consumed the only unit before this
        // caller's transaction opens. Direct AccessoryCheckout create
        // rather than ->users()->attach() for the same reason as the
        // consumable branch above.
        AccessoryCheckout::create([
            'accessory_id' => $accessory->id,
            'assigned_to' => $racingWinner->id,
            'assigned_type' => User::class,
            'created_by' => $actor->id,
            'created_at' => now(),
        ]);

        $service = new PredefinedKitCheckoutService;
        $errors = $this->invokeSaveToDb($service, $target, $actor, [], [], [], [$stale]);

        $this->assertNotEmpty($errors, 'The guard must record an error when a racing request has already consumed the accessory unit.');
        $this->assertSame(
            0,
            $accessory->fresh()->numRemaining(),
            'Accessory must remain at zero remaining. The guard must not oversubscribe.',
        );
        $this->assertSame(
            1,
            AccessoryCheckout::query()->where('accessory_id', $accessory->id)->count(),
            'Only the racing winner may hold a checkout row. The stale caller must not add a second.',
        );
    }

    /**
     * Call the protected saveToDb() method with a signature that
     * matches what the public checkout() flow produces. Reflection
     * keeps the test aware of the exact contract the guards protect
     * without requiring the wider public flow to run.
     *
     * @param  array<int, \App\Models\Asset>  $assets
     * @param  array<int, LicenseSeat>  $seats
     * @param  array<int, Consumable>  $consumables
     * @param  array<int, Accessory>  $accessories
     * @return array<int, string>
     */
    private function invokeSaveToDb(
        PredefinedKitCheckoutService $service,
        User $user,
        User $admin,
        array $assets,
        array $seats,
        array $consumables,
        array $accessories,
    ): array {
        $method = new ReflectionMethod($service, 'saveToDb');
        return $method->invoke(
            $service,
            $user,
            $admin,
            date('Y-m-d H:i:s'),
            '',
            [],
            $assets,
            $seats,
            $consumables,
            $accessories,
            'concurrency-guard-test',
        );
    }
}
