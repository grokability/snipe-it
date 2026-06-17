<?php

namespace Tests\Unit\Mail;

use App\Mail\ExpiringAssetsMail;
use App\Mail\ExpiringLicenseMail;
use App\Mail\SendUpcomingAuditMail;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ReportMailablesTest extends TestCase
{
    private function assertBuilds(Mailable $mail): void
    {
        $this->assertNotNull($mail->envelope());
        $this->assertNotNull($mail->content());
        $this->assertIsArray($mail->attachments());
    }

    public function test_expiring_assets_mail(): void
    {
        $this->assertBuilds(new ExpiringAssetsMail(new Collection, 60));
    }

    public function test_expiring_license_mail(): void
    {
        $this->assertBuilds(new ExpiringLicenseMail(new Collection, 60));
    }

    public function test_send_upcoming_audit_mail(): void
    {
        $this->assertBuilds(new SendUpcomingAuditMail(new Collection, 30, 0));
    }
}
