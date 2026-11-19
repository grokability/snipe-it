<?php

use App\Models\Consumable;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Exception\TransportException;

test('reports low inventory count when items are low', function () {
    $this->settings->set([
        'alerts_enabled' => 1,
        'alert_email' => 'ops@example.test',
        'alert_threshold' => 0,
    ]);

    Consumable::factory()->create(['qty' => 0, 'min_amt' => 1]);

    $this->artisan('snipeit:inventory-alerts')
        ->expectsOutputToContain('below minimum inventory')
        ->assertExitCode(0);
});

test('reports nothing to send when no low inventory', function () {
    $this->settings->set([
        'alerts_enabled' => 1,
        'alert_email' => 'ops@example.test',
        'alert_threshold' => 0,
    ]);

    Consumable::factory()->create(['qty' => 10, 'min_amt' => 1]);

    $this->artisan('snipeit:inventory-alerts')
        ->expectsOutputToContain('No low inventory items found')
        ->assertExitCode(0);
});

test('no op when alerts disabled in settings', function () {
    $this->settings->set([
        'alerts_enabled' => 0,
        'alert_email' => 'ops@example.test',
    ]);

    Consumable::factory()->create(['qty' => 0, 'min_amt' => 1]);

    $this->artisan('snipeit:inventory-alerts')
        ->expectsOutputToContain('Alerts are disabled')
        ->assertExitCode(0);
});

test('no op when alert email is blank', function () {
    $this->settings->set([
        'alerts_enabled' => 1,
        'alert_email' => '',
    ]);

    Consumable::factory()->create(['qty' => 0, 'min_amt' => 1]);

    $this->artisan('snipeit:inventory-alerts')
        ->expectsOutputToContain('No alert email configured')
        ->assertExitCode(0);
});

test('survives transport exception and logs warning', function () {
    $this->settings->set([
        'alerts_enabled' => 1,
        'alert_email' => 'ops@example.test',
        'alert_threshold' => 0,
    ]);

    Consumable::factory()->create(['qty' => 0, 'min_amt' => 1]);

    $this->mock(ChannelManager::class, function ($mock) {
        $mock->shouldReceive('send')->andThrow(new TransportException('SMTP auth failed'));
        $mock->shouldReceive('sendNow')->andThrow(new TransportException('SMTP auth failed'));
    });

    Log::spy();

    $this->artisan('snipeit:inventory-alerts')->assertExitCode(0);

    Log::shouldHaveReceived('warning')->atLeast()->once();
});
