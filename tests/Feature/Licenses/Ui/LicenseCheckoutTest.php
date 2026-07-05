<?php

namespace Tests\Feature\Licenses\Ui;

use App\Events\CheckoutableCheckedOut;
use App\Models\Asset;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LicenseCheckoutTest extends TestCase
{
    #[Test]
    public function requires_checkout_permission(): void
    {
        $license = License::factory()->create(['seats' => 1]);

        $this->actingAs(User::factory()->create())
            ->post(route('licenses.checkout.save', $license->id), [
                'assigned_to' => User::factory()->create()->id,
            ])
            ->assertForbidden();
    }

    #[Test]
    public function checkout_to_user_assigns_free_seat(): void
    {
        Event::fake([CheckoutableCheckedOut::class]);

        $license = License::factory()->create(['seats' => 1]);
        $target = User::factory()->create();
        $seat = $license->licenseseats()->first();

        $this->actingAs(User::factory()->checkoutLicenses()->create())
            ->post(route('licenses.checkout.save', $license->id), [
                'assigned_to' => $target->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals($target->id, $seat->fresh()->assigned_to);
        Event::assertDispatched(CheckoutableCheckedOut::class);
    }

    #[Test]
    public function checkout_to_asset_assigns_free_seat(): void
    {
        Event::fake([CheckoutableCheckedOut::class]);

        $license = License::factory()->create(['seats' => 1]);
        $asset = Asset::factory()->create();
        $seat = $license->licenseseats()->first();

        $this->actingAs(User::factory()->checkoutLicenses()->create())
            ->post(route('licenses.checkout.save', $license->id), [
                'asset_id' => $asset->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals($asset->id, $seat->fresh()->asset_id);
        Event::assertDispatched(CheckoutableCheckedOut::class);
    }

    #[Test]
    public function checkout_of_specific_seat_by_id(): void
    {
        Event::fake([CheckoutableCheckedOut::class]);

        $license = License::factory()->create(['seats' => 3]);
        $seats = $license->licenseseats()->orderBy('id')->get();
        $target = User::factory()->create();

        $this->actingAs(User::factory()->checkoutLicenses()->create())
            ->post(route('licenses.checkout.save', ['licenseId' => $license->id, 'seatId' => $seats[1]->id]), [
                'assigned_to' => $target->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals($target->id, $seats[1]->fresh()->assigned_to);
        $this->assertNull($seats[0]->fresh()->assigned_to);
        $this->assertNull($seats[2]->fresh()->assigned_to);
    }

    #[Test]
    public function cannot_checkout_when_no_seats_available(): void
    {
        $license = License::factory()->create(['seats' => 1]);
        LicenseSeat::where('license_id', $license->id)->update(['assigned_to' => User::factory()->create()->id]);

        $this->actingAs(User::factory()->checkoutLicenses()->create())
            ->post(route('licenses.checkout.save', $license->id), [
                'assigned_to' => User::factory()->create()->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    #[Test]
    public function sequential_checkouts_each_receive_a_distinct_seat(): void
    {
        Event::fake([CheckoutableCheckedOut::class]);

        $license = License::factory()->create(['seats' => 2]);
        $actor = User::factory()->checkoutLicenses()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($actor)
            ->post(route('licenses.checkout.save', $license->id), ['assigned_to' => $user1->id])
            ->assertSessionHas('success');

        $this->actingAs($actor)
            ->post(route('licenses.checkout.save', $license->id), ['assigned_to' => $user2->id])
            ->assertSessionHas('success');

        $assignedTo = $license->licenseseats()->pluck('assigned_to');

        $this->assertCount(2, $assignedTo->filter());
        $this->assertContains($user1->id, $assignedTo);
        $this->assertContains($user2->id, $assignedTo);
        $this->assertEquals(2, $assignedTo->unique()->count(), 'Both users should hold different seats');

        Event::assertDispatched(CheckoutableCheckedOut::class, 2);
    }

    #[Test]
    public function third_checkout_fails_when_only_two_seats_exist(): void
    {
        Event::fake([CheckoutableCheckedOut::class]);

        $license = License::factory()->create(['seats' => 2]);
        $actor = User::factory()->checkoutLicenses()->create();

        foreach ([User::factory()->create(), User::factory()->create()] as $user) {
            $this->actingAs($actor)
                ->post(route('licenses.checkout.save', $license->id), ['assigned_to' => $user->id])
                ->assertSessionHas('success');
        }

        $this->actingAs($actor)
            ->post(route('licenses.checkout.save', $license->id), [
                'assigned_to' => User::factory()->create()->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertEquals(0, $license->fresh()->freeSeats()->count());
        Event::assertDispatched(CheckoutableCheckedOut::class, 2);
    }
}
