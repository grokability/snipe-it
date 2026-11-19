<?php

use App\Models\CheckoutAcceptance;
use App\Models\User;
use App\Notifications\UnacceptedAssetReminderNotification;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\Mailer\Exception\TransportException;

test('acceptance reminder command', function () {
    Notification::fake();
    $userA = User::factory()->create(['email' => 'userA@test.com']);
    $userB = User::factory()->create(['email' => 'userB@test.com']);

    CheckoutAcceptance::factory()->pending()->count(2)->create([
        'assigned_to_id' => $userA->id,
    ]);
    CheckoutAcceptance::factory()->pending()->create([
        'assigned_to_id' => $userB->id,
    ]);

    $this->artisan('snipeit:acceptance-reminder')->assertExitCode(0);

    Notification::assertSentTo($userA, UnacceptedAssetReminderNotification::class);
    Notification::assertSentTo($userB, UnacceptedAssetReminderNotification::class);
    Notification::assertSentTimes(UnacceptedAssetReminderNotification::class, 2);
});

test('acceptance reminder command handles user without email', function () {
    Notification::fake();
    $userA = User::factory()->create(['email' => '']);

    CheckoutAcceptance::factory()->pending()->create([
        'assigned_to_id' => $userA->id,
    ]);
    $headers = ['ID', 'Name'];
    $rows = [
        [$userA->id, $userA->display_name],
    ];
    $this->artisan('snipeit:acceptance-reminder')
        ->expectsOutput('The following users do not have an email address:')
        ->expectsTable($headers, $rows)
        ->assertExitCode(0);

    Notification::assertNotSentTo($userA, UnacceptedAssetReminderNotification::class);
});

test('survives transport exception and logs warning', function () {
    $user = User::factory()->create(['email' => 'transport-fail@test.com']);
    CheckoutAcceptance::factory()->pending()->create(['assigned_to_id' => $user->id]);

    // Mock the notification channel manager so the mail driver
    // throws a transport exception on dispatch. The console
    // command catches TransportException and logs a warning
    // rather than bubbling up.
    $channelManager = Mockery::mock(ChannelManager::class);
    $channelManager->shouldReceive('send')->andThrow(new TransportException('SMTP auth failed'));
    $this->app->instance(ChannelManager::class, $channelManager);

    Log::spy();

    $this->artisan('snipeit:acceptance-reminder')->assertExitCode(0);

    Log::shouldHaveReceived('warning')->atLeast()->once();
});
