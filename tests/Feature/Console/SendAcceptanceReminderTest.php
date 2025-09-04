<?php

namespace Tests\Feature\Console;

use App\Mail\UnacceptedAssetReminderMail;
use App\Models\CheckoutAcceptance;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
class SendAcceptanceReminderTest extends TestCase
{
    public function testAcceptanceReminderCommand()
    {
        Mail::fake();
       $userA = User::factory()->create(['email' => 'userA@test.com']);
       $userB = User::factory()->create(['email' => 'userB@test.com']);

       CheckoutAcceptance::factory()->pending()->count(2)->create([
           'assigned_to_id' => $userA->id,
       ]);
        CheckoutAcceptance::factory()->pending()->create([
            'assigned_to_id' => $userB->id,
        ]);

        $this->artisan('snipeit:acceptance-reminder')->assertExitCode(0);

        Mail::assertSent(UnacceptedAssetReminderMail::class, function ($mail) {
            return $mail->hasTo('userA@test.com');
        });

        Mail::assertSent(UnacceptedAssetReminderMail::class, function ($mail) {
            return $mail->hasTo('userB@test.com');
        });

        Mail::assertSent(UnacceptedAssetReminderMail::class,2);
    }

    public function testAcceptanceReminderCommandHandlesUserWithoutEmail()
    {
        Mail::fake();
        $userA = User::factory()->create(['email' => '']);

        CheckoutAcceptance::factory()->pending()->create([
            'assigned_to_id' => $userA->id,
        ]);
        $headers = ['ID', 'Name'];
        $rows = [
            [$userA->id, $userA->display_name],
        ];
        $this->artisan('snipeit:acceptance-reminder')
            ->expectsOutput("The following users do not have an email address:")
            ->expectsTable($headers, $rows)
            ->assertExitCode(0);

        Mail::assertNotSent(UnacceptedAssetReminderMail::class);
    }
}
