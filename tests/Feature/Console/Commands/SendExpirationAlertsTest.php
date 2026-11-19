<?php

use App\Models\Asset;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;

test('command suppresses alerts when alert interval is null', function () {
    $this->settings->set([
        'alerts_enabled' => 1,
        'alert_email' => 'ops@example.test',
        'alert_interval' => null,
    ]);

    $asset = Asset::factory()->create(['archived' => 0]);
    $asset->asset_eol_date = Carbon::now()->addDays(10);
    $asset->save();

    Mail::fake();

    $this->artisan('snipeit:expiring-alerts')
        ->expectsOutput('No alert interval configured in settings. No alerts will be sent.')
        ->assertExitCode(0);

    Mail::assertNothingSent();
});

test('command suppresses alerts when alert interval is zero', function () {
    // Zero reads as "disabled" the same as null. The schema allows
    // either shape in practice (null from a cleared input, 0 from
    // a numeric field zeroed out).
    $this->settings->set([
        'alerts_enabled' => 1,
        'alert_email' => 'ops@example.test',
        'alert_interval' => 0,
    ]);

    Mail::fake();

    $this->artisan('snipeit:expiring-alerts')
        ->expectsOutput('No alert interval configured in settings. No alerts will be sent.')
        ->assertExitCode(0);

    Mail::assertNothingSent();
});

test('survives transport exception and logs warning', function () {
    $this->settings->set([
        'alerts_enabled' => 1,
        'alert_email' => 'ops@example.test',
        'alert_interval' => 60,
    ]);

    // AssetFactory's afterMaking hook overwrites asset_eol_date, so set it after create.
    $asset = Asset::factory()->create(['archived' => 0]);
    $asset->asset_eol_date = Carbon::now()->addDays(10);
    $asset->save();

    // Sanity check the fixture actually qualifies before asserting on side effects.
    expect(Asset::getExpiringWarrantyOrEol(60)->count())->toBeGreaterThan(0);

    $pending = Mockery::mock();
    $pending->shouldReceive('send')->andThrow(new TransportException('SMTP auth failed'));
    Mail::shouldReceive('to')->andReturn($pending);

    Log::spy();

    $this->artisan('snipeit:expiring-alerts')->assertExitCode(0);

    Log::shouldHaveReceived('warning')->atLeast()->once();
});
