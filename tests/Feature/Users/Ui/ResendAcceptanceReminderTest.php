<?php

namespace Tests\Feature\Users\Ui;

use App\Models\CheckoutAcceptance;
use App\Models\User;
use App\Notifications\UnacceptedAssetReminderNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ResendAcceptanceReminderTest extends TestCase
{
    public function test_it_sends_acceptance_reminder_for_user_with_pending_acceptances(): void
    {
        Notification::fake();

        $viewer = User::factory()->viewUsers()->create();
        $targetUser = User::factory()->create(['email' => 'target@example.com']);

        CheckoutAcceptance::factory()->pending()->count(2)->create([
            'assigned_to_id' => $targetUser->id,
        ]);

        $this->actingAs($viewer)
            ->post(route('users.acceptance_reminder', $targetUser))
            ->assertRedirect()
            ->assertSessionHas('success');

        Notification::assertSentTo($targetUser, UnacceptedAssetReminderNotification::class);
    }

    public function test_it_does_not_send_when_user_has_no_pending_acceptances(): void
    {
        Notification::fake();

        $viewer = User::factory()->viewUsers()->create();
        $targetUser = User::factory()->create(['email' => 'target@example.com']);

        $this->actingAs($viewer)
            ->post(route('users.acceptance_reminder', $targetUser))
            ->assertRedirect()
            ->assertSessionHas('warning');

        Notification::assertNotSentTo($targetUser, UnacceptedAssetReminderNotification::class);
    }

    public function test_it_does_not_send_when_user_has_no_email(): void
    {
        Notification::fake();

        $viewer = User::factory()->viewUsers()->create();
        $targetUser = User::factory()->create(['email' => '']);

        CheckoutAcceptance::factory()->pending()->create([
            'assigned_to_id' => $targetUser->id,
        ]);

        $this->actingAs($viewer)
            ->post(route('users.acceptance_reminder', $targetUser))
            ->assertRedirect()
            ->assertSessionHas('error');

        Notification::assertNotSentTo($targetUser, UnacceptedAssetReminderNotification::class);
    }
}
