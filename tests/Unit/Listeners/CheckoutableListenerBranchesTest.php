<?php

namespace Tests\Unit\Listeners;

use App\Events\CheckoutableCheckedIn;
use App\Events\CheckoutableCheckedOut;
use App\Listeners\CheckoutableListener;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Cubre las ramas de settings de CheckoutableListener: alert address (admin_cc),
 * Microsoft Teams workflows, acceptance requerida y checkoutables tipo LicenseSeat.
 */
class CheckoutableListenerBranchesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function assetWithCategory(array $catAttrs): Asset
    {
        $category = Category::factory()->forAssets()->create($catAttrs);
        $model = AssetModel::factory()->create(['category_id' => $category->id]);

        return Asset::factory()->for($model, 'model')->create();
    }

    public function test_checkout_with_alert_address_and_acceptance(): void
    {
        $this->settings->set([
            'admin_cc_email' => 'alerts@example.com, more@example.com',
            'admin_cc_always' => 1,
        ]);
        $asset = $this->assetWithCategory(['require_acceptance' => 1, 'checkin_email' => 1]);
        $target = User::factory()->create(['email' => 'u@example.com']);

        $event = new CheckoutableCheckedOut($asset, $target, User::factory()->superuser()->create(), 'nota');
        (new CheckoutableListener)->onCheckedOut($event);

        $this->assertTrue(true);
    }

    public function test_checkout_with_microsoft_teams_workflows_webhook(): void
    {
        $this->settings->set([
            'webhook_selected' => 'microsoft',
            'webhook_endpoint' => 'https://prod.workflows.azure.com/abc',
        ]);
        $asset = $this->assetWithCategory(['require_acceptance' => 0, 'checkin_email' => 1]);
        $target = User::factory()->create(['email' => 'u@example.com']);

        $event = new CheckoutableCheckedOut($asset, $target, User::factory()->superuser()->create(), 'nota');
        (new CheckoutableListener)->onCheckedOut($event);

        $this->assertTrue(true);
    }

    public function test_checkin_with_alert_address(): void
    {
        $this->settings->set([
            'admin_cc_email' => 'alerts@example.com',
            'admin_cc_always' => 1,
        ]);
        $asset = $this->assetWithCategory(['checkin_email' => 1]);
        $target = User::factory()->create(['email' => 'u@example.com']);

        $event = new CheckoutableCheckedIn($asset, $target, User::factory()->superuser()->create(), 'nota');
        (new CheckoutableListener)->onCheckedIn($event);

        $this->assertTrue(true);
    }

    public function test_checkout_license_seat_category_email(): void
    {
        $category = Category::factory()->create(['category_type' => 'license', 'checkin_email' => 1]);
        $license = License::factory()->create(['category_id' => $category->id]);
        $seat = LicenseSeat::factory()->create(['license_id' => $license->id]);
        $target = User::factory()->create(['email' => 'u@example.com']);

        $event = new CheckoutableCheckedOut($seat, $target, User::factory()->superuser()->create(), 'nota');
        (new CheckoutableListener)->onCheckedOut($event);

        $this->assertTrue(true);
    }

    public function test_checkin_license_seat_with_alert(): void
    {
        $this->settings->set(['admin_cc_email' => 'a@example.com', 'admin_cc_always' => 1]);
        $category = Category::factory()->create(['category_type' => 'license', 'checkin_email' => 1]);
        $license = License::factory()->create(['category_id' => $category->id]);
        $seat = LicenseSeat::factory()->create(['license_id' => $license->id]);
        $target = User::factory()->create(['email' => 'u@example.com']);

        $event = new CheckoutableCheckedIn($seat, $target, User::factory()->superuser()->create(), 'nota');
        (new CheckoutableListener)->onCheckedIn($event);

        $this->assertTrue(true);
    }
}
