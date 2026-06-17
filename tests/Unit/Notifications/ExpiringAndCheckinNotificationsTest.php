<?php

namespace Tests\Unit\Notifications;

use App\Models\Asset;
use App\Notifications\ExpectedCheckinAdminNotification;
use App\Notifications\ExpectedCheckinNotification;
use App\Notifications\ExpiringAssetsNotification;
use App\Notifications\ExpiringLicenseNotification;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ExpiringAndCheckinNotificationsTest extends TestCase
{
    public function test_expiring_assets_notification_mail(): void
    {
        $n = new ExpiringAssetsNotification(new Collection, 60);

        $this->assertIsArray($n->via());
        $this->assertNotNull($n->toMail());
    }

    public function test_expiring_license_notification_mail(): void
    {
        $n = new ExpiringLicenseNotification(new Collection, 60);

        $this->assertIsArray($n->via());
        $this->assertNotNull($n->toMail());
    }

    public function test_expected_checkin_admin_notification_mail(): void
    {
        $n = new ExpectedCheckinAdminNotification(new Collection);

        $this->assertIsArray($n->via());
        $this->assertNotNull($n->toMail());
    }

    public function test_expected_checkin_notification_mail(): void
    {
        // toMail() lee $params->expected_checkin (objeto), por lo que se pasa un Asset.
        // (Nota: via() de esta clase espera $params['item'] (array) — inconsistencia del codigo.)
        $asset = Asset::factory()->create(['expected_checkin' => now()->addDays(5)->toDateString()]);
        $n = new ExpectedCheckinNotification($asset);

        $this->assertNotNull($n->toMail());
    }
}
