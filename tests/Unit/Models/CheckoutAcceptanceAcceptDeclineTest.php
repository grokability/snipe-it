<?php

namespace Tests\Unit\Models;

use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CheckoutAcceptanceAcceptDeclineTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function pendingAcceptance(): CheckoutAcceptance
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create(['assigned_to' => $user->id, 'assigned_type' => User::class]);

        return CheckoutAcceptance::factory()->pending()->create([
            'checkoutable_id' => $asset->id,
            'checkoutable_type' => Asset::class,
            'assigned_to_id' => $user->id,
        ]);
    }

    public function test_accept_marks_accepted_and_updates_item(): void
    {
        $acceptance = $this->pendingAcceptance();

        $acceptance->accept('signature.png', 'EULA text', null, 'accepted note');

        $this->assertNotNull($acceptance->fresh()->accepted_at);
        $this->assertFalse($acceptance->fresh()->isPending());
    }

    public function test_decline_marks_declined_and_updates_item(): void
    {
        $acceptance = $this->pendingAcceptance();

        $acceptance->decline('signature.png', 'declined note');

        $this->assertNotNull($acceptance->fresh()->declined_at);
        $this->assertFalse($acceptance->fresh()->isPending());
    }
}
