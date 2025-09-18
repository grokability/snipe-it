<?php

namespace Tests\Feature\Notifications\Email;

use App\Mail\ExpiringAssetsMail;
use App\Mail\ExpiringLicenseMail;
use App\Mail\SendUpcomingAuditMail;
use App\Models\Asset;
use App\Models\License;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;


class ExpiringAlertsNotificationTest extends TestCase
{
     public function testExpiringAssetsEmailNotification()
     {
         Mail::fake();

         $this->settings->enableAlertEmail('admin@example.com');
         $this->settings->setAlertInterval(30);

         $alert_email = Setting::first()->alert_email;

         $expiringWarrantyAsset = Asset::factory()->create([
             'purchase_date' => now()->subDays(356)->format('Y-m-d'),
             'warranty_months' => 12,
             'archived' => 0,
         ]);


         $alreadyExpiredAsset = Asset::factory()->create([
             'purchase_date' => now()->subDays(396)->format('Y-m-d'),
             'warranty_months' => 12,
             'archived' => 0,
         ]);

         // Asset has a manually entered EOL date that's coming up
         $expiringEOLAsset = Asset::factory()->create([
             'archived' => 0,
         ]);

         // We have to set this here because of the configure() method in the Asset factory :(
         $expiringEOLAsset->asset_eol_date = now()->addDays(5)->format('Y-m-d');
         $expiringEOLAsset->save();

         $notExpiringAsset = Asset::factory()->create([
             'purchase_date' => now()->addDays(330)->format('Y-m-d'),
             'warranty_months' => 12,
             'archived' => 0,
         ]);
         // We have to set this here because of the configure() method in the Asset factory :(
         $notExpiringAsset->asset_eol_date = null;
         $expiringEOLAsset->save();


         $this->artisan('snipeit:expiring-alerts')->assertExitCode(0);

         Mail::assertSent(ExpiringAssetsMail::class, function($mail) use ($alert_email, $expiringWarrantyAsset, $expiringEOLAsset) {
             return $mail->hasTo($alert_email) && ($mail->assets->contains($expiringEOLAsset) || $mail->assets->contains($expiringWarrantyAsset));
         });
         

         Mail::assertNotSent(ExpiringAssetsMail::class, function($mail) use ($alert_email, $notExpiringAsset, $alreadyExpiredAsset) {
             return $mail->assets->contains($alert_email) || ($mail->assets->contains($alreadyExpiredAsset) && ($mail->assets->contains($notExpiringAsset)));
         });
     }

     public function testExpiringLicensesEmailNotification()
     {
         Mail::fake();
         $this->settings->enableAlertEmail('admin@example.com');
         $this->settings->setAlertInterval(60);

         $alert_email = Setting::first()->alert_email;

         $expiringLicense = License::factory()->create([
             'expiration_date' => now()->addDays(30)->format('Y-m-d'),
             'deleted_at' => null,
             'termination_date' => null,
         ]);

         $expiredLicense = License::factory()->create([
             'expiration_date' => now()->subDays(10)->format('Y-m-d'),
             'deleted_at' => null,
         ]);
         $notExpiringLicense = License::factory()->create([
             'expiration_date' => now()->addMonths(3)->format('Y-m-d'),
             'deleted_at' => null,
         ]);

         $expiringButTerminatedLicense = License::factory()->create([
             'termination_date' => now()->subDays(10)->format('Y-m-d'),
             'expiration_date' => now()->subDays(10)->format('Y-m-d'),
             'deleted_at' => null,
         ]);

         $deletedExpiringLicense = License::factory()->create([
             'expiration_date' => now()->addDays(30)->format('Y-m-d'),
             'deleted_at' => now()->subDays(10)->format('Y-m-d'),
         ]);

         $this->artisan('snipeit:expiring-alerts')->assertExitCode(0);

         Mail::assertSent(ExpiringLicenseMail::class, function($mail) use ($alert_email, $expiringLicense) {
             return $mail->hasTo($alert_email) && $mail->licenses->contains($expiringLicense);
         });

         Mail::assertNotSent(ExpiringLicenseMail::class, function($mail) use ($alert_email, $expiredLicense) {
             return $mail->hasTo($alert_email) && $mail->licenses->contains($expiredLicense);
         });

         Mail::assertNotSent(ExpiringLicenseMail::class, function($mail) use ($alert_email, $notExpiringLicense) {
             return $mail->licenses->contains($alert_email) || $mail->licenses->contains($notExpiringLicense);
         });

         Mail::assertNotSent(ExpiringLicenseMail::class, function($mail) use ($alert_email, $expiringButTerminatedLicense) {
             return $mail->licenses->contains($alert_email) || $mail->licenses->contains($expiringButTerminatedLicense);
         });

         Mail::assertNotSent(ExpiringLicenseMail::class, function($mail) use ($alert_email, $deletedExpiringLicense) {
             return $mail->licenses->contains($alert_email) || $mail->licenses->contains($deletedExpiringLicense);
         });

     }

     public function testAuditWarningThresholdEmailNotification()
     {
         Mail::fake();
         $this->settings->enableAlertEmail('admin@example.com');
         $this->settings->setAuditWarningDays(15);

         $alert_email = Setting::first()->alert_email;

         $upcomingAuditableAsset = Asset::factory()->create([
             'next_audit_date' => now()->addDays(14)->format('Y-m-d'),
             'deleted_at' => null,
         ]);

         $overDueForAuditableAsset = Asset::factory()->create([
             'next_audit_date' => now()->subDays(1)->format('Y-m-d'),
             'deleted_at' => null,
         ]);

         $notAuditableAsset = Asset::factory()->create([
             'next_audit_date' => now()->addDays(30)->format('Y-m-d'),
             'deleted_at' => null,
         ]);

         $this->artisan('snipeit:upcoming-audits')->assertExitCode(0);

         Mail::assertSent(SendUpcomingAuditMail::class, function($mail) use ($alert_email, $upcomingAuditableAsset, $overDueForAuditableAsset) {
             return $mail->hasTo($alert_email) && ($mail->assets->contains($upcomingAuditableAsset) && $mail->assets->contains($overDueForAuditableAsset));
         });
         Mail::assertNotSent(SendUpcomingAuditMail::class, function($mail) use ($alert_email, $notAuditableAsset) {
             return $mail->hasTo($alert_email) && $mail->assets->contains($notAuditableAsset);
         });
     }
}