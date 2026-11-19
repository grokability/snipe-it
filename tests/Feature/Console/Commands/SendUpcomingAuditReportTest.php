<?php

use App\Models\Asset;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;

test('survives transport exception and logs warning', function () {
    $this->settings->set([
        'alert_email' => 'ops@example.test',
        'audit_warning_days' => 30,
    ]);

    // AssetFactory's afterMaking hook may overwrite next_audit_date, so set it after create.
    $asset = Asset::factory()->create();
    $asset->next_audit_date = Carbon::now()->addDays(5);
    $asset->save();

    $pending = Mockery::mock();
    $pending->shouldReceive('send')->andThrow(new TransportException('SMTP auth failed'));
    Mail::shouldReceive('to')->andReturn($pending);

    Log::spy();

    $this->artisan('snipeit:upcoming-audits')->assertExitCode(0);

    Log::shouldHaveReceived('warning')->atLeast()->once();
});
