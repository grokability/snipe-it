<?php

namespace Tests\Unit\Listeners;

use App\Events\CheckoutableCheckedOut;
use App\Listeners\LogListener;
use App\Models\Asset;
use App\Models\User;
use Tests\TestCase;

class LogListenerTest extends TestCase
{
    public function testLogsEntryOnCheckoutableCheckedOut()
    {
        $asset = Asset::factory()->create();
        $checkedOutTo = User::factory()->create();
        $checkedOutBy = User::factory()->create();

        // Simply to ensure `created_by` is set in the action log
        $this->actingAs($checkedOutBy);

        (new LogListener())->onCheckoutableCheckedOut(new CheckoutableCheckedOut(
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

}
