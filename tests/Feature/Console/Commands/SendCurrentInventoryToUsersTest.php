<?php

use App\Models\Asset;
use App\Models\User;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Exception\TransportException;

test('command survives transport exception and logs warning', function () {
    $user = User::factory()->create(['email' => 'test@example.org']);
    Asset::factory()->assignedToUser($user)->create();

    $this->mock(ChannelManager::class, function ($mock) {
        $mock->shouldReceive('send')->andThrow(new TransportException('SMTP auth failed'));
        $mock->shouldReceive('sendNow')->andThrow(new TransportException('SMTP auth failed'));
    });

    Log::spy();

    $this->artisan('snipeit:user-inventory')->assertExitCode(0);

    Log::shouldHaveReceived('warning')->atLeast()->once();
});
