<?php

namespace Tests\Unit\Notifications;

use App\Models\Asset;
use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use App\Notifications\AcceptanceItemAcceptedNotification;
use App\Notifications\AcceptanceItemDeclinedNotification;
use App\Notifications\AuditNotification;
use App\Notifications\ExpiringAssetsNotification;
use App\Notifications\ExpiringLicenseNotification;
use App\Notifications\RequestAssetNotification;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ParamNotificationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->settings->set([
            'webhook_selected' => 'slack',
            'webhook_endpoint' => 'https://example.webhook.office.com/hook',
            'webhook_channel' => '#it',
            'webhook_botname' => 'Snipe-Bot',
        ]);
    }

    public function test_audit_notification(): void
    {
        $params = [
            'item' => Asset::factory()->create(),
            'admin' => User::factory()->create(),
            'note' => 'audited',
            'location' => Location::factory()->create()->name,
        ];
        $n = new AuditNotification($params);

        $this->assertIsArray($n->via());
        $this->assertNotNull($n->toSlack());
    }

    public function test_request_asset_notification(): void
    {
        $asset = Asset::factory()->create();
        $params = [
            'target' => User::factory()->create(),
            'item' => $asset,
            'item_type' => Asset::class,
            'item_quantity' => 1,
            'requested_date' => now()->format('Y-m-d H:i:s'),
            'note' => 'please',
        ];
        $n = new RequestAssetNotification($params);

        $this->assertIsArray($n->via());
        $this->assertNotNull($n->toSlack());
    }

    public function test_expiring_assets_and_licenses_notifications(): void
    {
        $assetsN = new ExpiringAssetsNotification(new Collection, 60);
        $licN = new ExpiringLicenseNotification(new Collection, 60);

        $this->assertIsArray($assetsN->via());
        $this->assertIsArray($licN->via());
    }

    public function test_acceptance_accepted_and_declined_notifications(): void
    {
        $params = [
            'item_tag' => 'A-1', 'item_name' => 'Laptop', 'item_model' => 'MBP',
            'item_serial' => 'SN1', 'item_status' => 'Ready', 'accepted_date' => now()->toDateString(),
            'assigned_to' => 'Jane Doe', 'company_name' => Company::factory()->create()->name,
        ];
        $accepted = new AcceptanceItemAcceptedNotification($params);
        $this->assertIsArray($accepted->via());
        $this->assertNotNull($accepted->toMail());

        $declined = new AcceptanceItemDeclinedNotification(array_merge($params, ['declined_date' => now()->toDateString(), 'note' => 'no']));
        $this->assertNotNull($declined->toMail(null));
    }
}
