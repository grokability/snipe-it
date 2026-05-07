<?php

namespace Tests\Feature\Users\Ui;

use App\Mail\UnacceptedAssetReminderMail;
use App\Models\CheckoutAcceptance;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ResendAcceptanceReminderTest extends TestCase
{
    public function test_it_sends_acceptance_reminder_email_for_user_with_pending_acceptances(): void
    {
        Mail::fake();

        $viewer = User::factory()->viewUsers()->create();
        $targetUser = User::factory()->create(['email' => 'target@example.com']);

        CheckoutAcceptance::factory()->pending()->count(2)->create([
            'assigned_to_id' => $targetUser->id,
        ]);

        $this->actingAs($viewer)
            ->post(route('users.acceptance_reminder', $targetUser))
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertSent(UnacceptedAssetReminderMail::class, function (UnacceptedAssetReminderMail $mail) use ($targetUser) {
            return $mail->hasTo($targetUser->email);
        });
    }

    public function test_it_does_not_send_when_user_has_no_pending_acceptances(): void
    {
        Mail::fake();

        $viewer = User::factory()->viewUsers()->create();
        $targetUser = User::factory()->create(['email' => 'target@example.com']);

        $this->actingAs($viewer)
            ->post(route('users.acceptance_reminder', $targetUser))
            ->assertRedirect()
            ->assertSessionHas('warning');

        Mail::assertNotSent(UnacceptedAssetReminderMail::class);
    }

    public function test_it_does_not_send_when_user_has_no_email(): void
    {
        Mail::fake();

        $viewer = User::factory()->viewUsers()->create();
        $targetUser = User::factory()->create(['email' => '']);

        CheckoutAcceptance::factory()->pending()->create([
            'assigned_to_id' => $targetUser->id,
        ]);

        $this->actingAs($viewer)
            ->post(route('users.acceptance_reminder', $targetUser))
            ->assertRedirect()
            ->assertSessionHas('error');

        Mail::assertNotSent(UnacceptedAssetReminderMail::class);
    }
}
