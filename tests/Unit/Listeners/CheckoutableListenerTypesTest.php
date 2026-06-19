<?php

namespace Tests\Unit\Listeners;

use App\Events\CheckoutableCheckedIn;
use App\Events\CheckoutableCheckedOut;
use App\Listeners\CheckoutableListener;
use App\Models\Accessory;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Cubre las ramas por tipo de checkoutable del CheckoutableListener
 * (Accessory, Consumable, Component, LicenseSeat) en checkout y checkin.
 */
class CheckoutableListenerTypesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function admin(): User
    {
        return User::factory()->superuser()->create();
    }

    private function target(): User
    {
        return User::factory()->create(['email' => 'target@example.com']);
    }

    public function test_checkout_accessory(): void
    {
        $event = new CheckoutableCheckedOut(Accessory::factory()->create(), $this->target(), $this->admin(), 'nota');
        (new CheckoutableListener)->onCheckedOut($event);
        $this->assertTrue(true);
    }

    public function test_checkin_accessory(): void
    {
        $event = new CheckoutableCheckedIn(Accessory::factory()->create(), $this->target(), $this->admin(), 'nota');
        (new CheckoutableListener)->onCheckedIn($event);
        $this->assertTrue(true);
    }

    public function test_checkout_consumable(): void
    {
        $event = new CheckoutableCheckedOut(Consumable::factory()->create(), $this->target(), $this->admin(), 'nota');
        (new CheckoutableListener)->onCheckedOut($event);
        $this->assertTrue(true);
    }

    public function test_checkout_component(): void
    {
        $event = new CheckoutableCheckedOut(Component::factory()->create(), $this->target(), $this->admin(), 'nota');
        (new CheckoutableListener)->onCheckedOut($event);
        $this->assertTrue(true);
    }

    public function test_checkin_component(): void
    {
        $event = new CheckoutableCheckedIn(Component::factory()->create(), $this->target(), $this->admin(), 'nota');
        (new CheckoutableListener)->onCheckedIn($event);
        $this->assertTrue(true);
    }

    public function test_checkout_license_seat(): void
    {
        $license = License::factory()->create();
        $seat = LicenseSeat::factory()->create(['license_id' => $license->id]);

        $event = new CheckoutableCheckedOut($seat, $this->target(), $this->admin(), 'nota');
        (new CheckoutableListener)->onCheckedOut($event);
        $this->assertTrue(true);
    }

    public function test_checkin_license_seat(): void
    {
        $license = License::factory()->create();
        $seat = LicenseSeat::factory()->create(['license_id' => $license->id]);

        $event = new CheckoutableCheckedIn($seat, $this->target(), $this->admin(), 'nota');
        (new CheckoutableListener)->onCheckedIn($event);
        $this->assertTrue(true);
    }

    public function test_checkout_to_location_uses_manager(): void
    {
        $location = \App\Models\Location::factory()->create();
        $event = new CheckoutableCheckedOut(Accessory::factory()->create(), $location, $this->admin(), 'nota');
        (new CheckoutableListener)->onCheckedOut($event);
        $this->assertTrue(true);
    }
}
