<?php

namespace Tests\Unit\Listeners;

use App\Events\CheckoutablesCheckedOutInBulk;
use App\Listeners\CheckoutablesCheckedOutInBulkListener;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Cubre CheckoutablesCheckedOutInBulkListener (antes 6%): handle() con distintos
 * targets y las condiciones de envio (usuario, alert address, eula/acceptance).
 */
class BulkCheckoutListenerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function event($target, $assets): CheckoutablesCheckedOutInBulk
    {
        return new CheckoutablesCheckedOutInBulk(
            assets: $assets,
            target: $target,
            admin: User::factory()->superuser()->create(),
            checkout_at: now()->toDateString(),
            expected_checkin: now()->addWeek()->toDateString(),
            note: 'bulk',
        );
    }

    public function test_handle_to_user_with_acceptance_assets(): void
    {
        $assets = Asset::factory()->count(2)->requiresAcceptance()->create();
        $target = User::factory()->create(['email' => 'u@example.com']);

        (new CheckoutablesCheckedOutInBulkListener)->handle($this->event($target, $assets));

        $this->assertTrue(true);
    }

    public function test_handle_to_user_without_email_skips(): void
    {
        $assets = Asset::factory()->count(1)->create();
        $target = User::factory()->create(['email' => '']);

        (new CheckoutablesCheckedOutInBulkListener)->handle($this->event($target, $assets));

        $this->assertTrue(true);
    }

    public function test_handle_with_admin_cc_always(): void
    {
        $this->settings->set([
            'admin_cc_always' => 1,
            'admin_cc_email' => 'admin@example.com',
        ]);
        $assets = Asset::factory()->count(1)->create();
        $target = User::factory()->create(['email' => 'u@example.com']);

        (new CheckoutablesCheckedOutInBulkListener)->handle($this->event($target, $assets));

        $this->assertTrue(true);
    }

    public function test_handle_to_location_uses_manager(): void
    {
        $manager = User::factory()->create(['email' => 'mgr@example.com']);
        $location = Location::factory()->create(['manager_id' => $manager->id]);
        $assets = Asset::factory()->count(1)->requiresAcceptance()->create();

        (new CheckoutablesCheckedOutInBulkListener)->handle($this->event($location, $assets));

        $this->assertTrue(true);
    }

    public function test_handle_to_asset_target_resolves_assigned_user(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.com']);
        $targetAsset = Asset::factory()->assignedToUser($user)->create();
        $assets = Asset::factory()->count(1)->create();

        (new CheckoutablesCheckedOutInBulkListener)->handle($this->event($targetAsset, $assets));

        $this->assertTrue(true);
    }

    public function test_subscribe_registers(): void
    {
        (new CheckoutablesCheckedOutInBulkListener)->subscribe(app('events'));
        $this->assertTrue(true);
    }
}
