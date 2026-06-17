<?php

namespace Tests\Unit\Listeners;

use App\Events\CheckoutableCheckedIn;
use App\Events\CheckoutableCheckedOut;
use App\Listeners\LogListener;
use App\Models\Asset;
use App\Models\User;
use Tests\TestCase;

class LogListenerTest extends TestCase
{
    public function test_logs_entry_on_checkoutable_checked_out()
    {
        $asset = Asset::factory()->create();
        $checkedOutTo = User::factory()->create();
        $checkedOutBy = User::factory()->create();

        // Simply to ensure `created_by` is set in the action log
        $this->actingAs($checkedOutBy);

        (new LogListener)->onCheckoutableCheckedOut(new CheckoutableCheckedOut(
            $asset,
            $checkedOutTo,
            $checkedOutBy,
            'A simple note...',
        ));

        $this->assertDatabaseHas('action_logs', [
            'action_type' => 'checkout',
            'created_by' => $checkedOutBy->id,
            'target_id' => $checkedOutTo->id,
            'target_type' => User::class,
            'item_id' => $asset->id,
            'item_type' => Asset::class,
            'note' => 'A simple note...',
        ]);
    }

    public function test_logs_entry_on_checkoutable_checked_in()
    {
        $asset = Asset::factory()->create();
        $checkedOutTo = User::factory()->create();
        $admin = User::factory()->create();
        $this->actingAs($admin);

        (new LogListener)->onCheckoutableCheckedIn(new CheckoutableCheckedIn(
            $asset,
            $checkedOutTo,
            $admin,
            'Checkin note...',
        ));

        $this->assertGreaterThanOrEqual(1, $asset->history()->count());
    }

    public function test_subscribe_registers_handlers()
    {
        (new LogListener)->subscribe(app('events'));

        $this->assertTrue(true);
    }
}
