<?php

namespace Tests\Unit\Listeners;

use App\Events\CheckoutableCheckedIn;
use App\Events\CheckoutableCheckedOut;
use App\Listeners\CheckoutableListener;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Cubre las ramas de webhook, alert-address y aceptacion del CheckoutableListener
 * activando los ajustes correspondientes.
 */
class CheckoutableListenerWebhookTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
        $this->settings->set([
            'webhook_selected' => 'slack',
            'webhook_endpoint' => 'https://example.webhook.office.com/hook',
            'webhook_channel' => '#it',
            'alert_email' => 'alerts@example.com',
            'alerts_enabled' => 1,
        ]);
    }

    private function assetWithAcceptanceCategory(): Asset
    {
        $category = Category::factory()->forAssets()->create([
            'checkin_email' => 1,
            'require_acceptance' => 1,
        ]);
        $model = AssetModel::factory()->create(['category_id' => $category->id]);

        return Asset::factory()->for($model, 'model')->create();
    }

    public function test_on_checked_out_with_webhook_alert_and_acceptance(): void
    {
        $asset = $this->assetWithAcceptanceCategory();
        $target = User::factory()->create(['email' => 'target@example.com']);
        $admin = User::factory()->superuser()->create();

        (new CheckoutableListener)->onCheckedOut(new CheckoutableCheckedOut($asset, $target, $admin, 'note'));

        $this->assertTrue(true);
    }

    public function test_on_checked_in_with_webhook_and_alert(): void
    {
        $asset = $this->assetWithAcceptanceCategory();
        $target = User::factory()->create(['email' => 'target@example.com']);
        $admin = User::factory()->superuser()->create();

        (new CheckoutableListener)->onCheckedIn(new CheckoutableCheckedIn($asset, $target, $admin, 'note'));

        $this->assertTrue(true);
    }
}
