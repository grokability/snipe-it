<?php

namespace Tests\Feature\Licenses\Api;

use App\Events\CheckoutableCheckedIn;
use App\Events\CheckoutableCheckedOut;
use App\Models\Asset;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LicenseCheckoutCheckinTest extends TestCase
{
    // ---------------------------------------------------------------------------
    // Checkout
    // ---------------------------------------------------------------------------

    #[Test]
    public function checkout_requires_checkout_permission(): void
    {
        $license = License::factory()->create(['seats' => 1]);

        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.licenses.checkout', $license->id), [
                'target_type' => 'user',
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

        $this->actingAsForApi(User::factory()->checkoutLicenses()->create())
            ->postJson(route('api.licenses.checkout', $license->id), [
                'target_type' => 'user',
                'assigned_to' => $target->id,
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $seat = $license->licenseseats()->first();
        $this->assertEquals($target->id, $seat->assigned_to);

        Event::assertDispatched(CheckoutableCheckedOut::class);
    }

    #[Test]
    public function checkout_to_asset_assigns_free_seat(): void
    {
        Event::fake([CheckoutableCheckedOut::class]);

        $license = License::factory()->create(['seats' => 1]);
        $asset = Asset::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutLicenses()->create())
            ->postJson(route('api.licenses.checkout', $license->id), [
                'target_type' => 'asset',
                'asset_id' => $asset->id,
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $seat = $license->licenseseats()->first();
        $this->assertEquals($asset->id, $seat->asset_id);

        Event::assertDispatched(CheckoutableCheckedOut::class);
    }

    #[Test]
    public function checkout_to_specific_seat_by_id(): void
    {
        Event::fake([CheckoutableCheckedOut::class]);

        $license = License::factory()->create(['seats' => 3]);
        $seats = $license->licenseseats()->orderBy('id')->get();
        $target = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutLicenses()->create())
            ->postJson(route('api.licenses.checkout', $license->id), [
                'seat_id' => $seats[1]->id,
                'target_type' => 'user',
                'assigned_to' => $target->id,
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertEquals($target->id, $seats[1]->fresh()->assigned_to);
        $this->assertNull($seats[0]->fresh()->assigned_to);
        $this->assertNull($seats[2]->fresh()->assigned_to);

        Event::assertDispatched(CheckoutableCheckedOut::class);
    }

    #[Test]
    public function checkout_fails_when_no_seats_available(): void
    {
        $license = License::factory()->create(['seats' => 1]);
        LicenseSeat::where('license_id', $license->id)->update(['assigned_to' => User::factory()->create()->id]);

        $this->actingAsForApi(User::factory()->checkoutLicenses()->create())
            ->postJson(route('api.licenses.checkout', $license->id), [
                'target_type' => 'user',
                'assigned_to' => User::factory()->create()->id,
            ])
            ->assertJson(['status' => 'error']);
    }

    #[Test]
    public function checkout_returns_error_for_nonexistent_user(): void
    {
        $license = License::factory()->create(['seats' => 1]);

        $this->actingAsForApi(User::factory()->checkoutLicenses()->create())
            ->postJson(route('api.licenses.checkout', $license->id), [
                'target_type' => 'user',
                'assigned_to' => 99999,
            ])
            ->assertJson(['status' => 'error']);
    }

    #[Test]
    public function checkout_returns_error_for_nonexistent_asset(): void
    {
        $license = License::factory()->create(['seats' => 1]);

        $this->actingAsForApi(User::factory()->checkoutLicenses()->create())
            ->postJson(route('api.licenses.checkout', $license->id), [
                'target_type' => 'asset',
                'asset_id' => 99999,
            ])
            ->assertJson(['status' => 'error']);
    }

    #[Test]
    public function sequential_checkouts_each_receive_a_distinct_seat(): void
    {
        Event::fake([CheckoutableCheckedOut::class]);

        $license = License::factory()->create(['seats' => 2]);
        $actor = User::factory()->checkoutLicenses()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.licenses.checkout', $license->id), [
                'target_type' => 'user',
                'assigned_to' => $user1->id,
            ])
            ->assertJson(['status' => 'success']);

        $this->actingAsForApi($actor)
            ->postJson(route('api.licenses.checkout', $license->id), [
                'target_type' => 'user',
                'assigned_to' => $user2->id,
            ])
            ->assertJson(['status' => 'success']);

        $assignedTo = $license->licenseseats()->pluck('assigned_to');
        $this->assertCount(2, $assignedTo->filter());
        $this->assertEquals(2, $assignedTo->unique()->count());

        Event::assertDispatched(CheckoutableCheckedOut::class, 2);
    }

    // ---------------------------------------------------------------------------
    // Checkin
    // ---------------------------------------------------------------------------

    #[Test]
    public function checkin_requires_checkin_permission(): void
    {
        $license = License::factory()->create(['seats' => 1]);
        $seat = $license->licenseseats()->first();
        $seat->update(['assigned_to' => User::factory()->create()->id]);

        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.licenses.checkin', $license->id), [
                'seat_id' => $seat->id,
            ])
            ->assertForbidden();
    }

    #[Test]
    public function checkin_clears_assigned_user(): void
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $license = License::factory()->create(['seats' => 1, 'reassignable' => true]);
        $user = User::factory()->create();
        $seat = $license->licenseseats()->first();
        $seat->update(['assigned_to' => $user->id]);

        $this->actingAsForApi(User::factory()->checkinLicenses()->create())
            ->postJson(route('api.licenses.checkin', $license->id), [
                'seat_id' => $seat->id,
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertNull($seat->fresh()->assigned_to);
        $this->assertFalse((bool) $seat->fresh()->unreassignable_seat);

        Event::assertDispatched(CheckoutableCheckedIn::class);
    }

    #[Test]
    public function checkin_clears_assigned_asset(): void
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $license = License::factory()->create(['seats' => 1, 'reassignable' => true]);
        $asset = Asset::factory()->create();
        $seat = $license->licenseseats()->first();
        $seat->update(['asset_id' => $asset->id]);

        $this->actingAsForApi(User::factory()->checkinLicenses()->create())
            ->postJson(route('api.licenses.checkin', $license->id), [
                'seat_id' => $seat->id,
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertNull($seat->fresh()->asset_id);

        Event::assertDispatched(CheckoutableCheckedIn::class);
    }

    #[Test]
    public function checkin_marks_seat_unreassignable_when_license_is_not_reassignable(): void
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $license = License::factory()->create(['seats' => 1, 'reassignable' => false]);
        $user = User::factory()->create();
        $seat = $license->licenseseats()->first();
        $seat->update(['assigned_to' => $user->id]);

        $this->actingAsForApi(User::factory()->checkinLicenses()->create())
            ->postJson(route('api.licenses.checkin', $license->id), [
                'seat_id' => $seat->id,
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertNull($seat->fresh()->assigned_to);
        $this->assertTrue((bool) $seat->fresh()->unreassignable_seat);

        Event::assertDispatched(CheckoutableCheckedIn::class);
    }

    #[Test]
    public function checkin_returns_error_for_unassigned_seat(): void
    {
        $license = License::factory()->create(['seats' => 1]);
        $seat = $license->licenseseats()->first();

        $this->actingAsForApi(User::factory()->checkinLicenses()->create())
            ->postJson(route('api.licenses.checkin', $license->id), [
                'seat_id' => $seat->id,
            ])
            ->assertJson(['status' => 'error']);
    }

    #[Test]
    public function checkin_returns_error_for_seat_not_belonging_to_license(): void
    {
        $license1 = License::factory()->create(['seats' => 1]);
        $license2 = License::factory()->create(['seats' => 1]);
        $seat2 = $license2->licenseseats()->first();
        $seat2->update(['assigned_to' => User::factory()->create()->id]);

        $this->actingAsForApi(User::factory()->checkinLicenses()->create())
            ->postJson(route('api.licenses.checkin', $license1->id), [
                'seat_id' => $seat2->id,
            ])
            ->assertJson(['status' => 'error']);
    }

    #[Test]
    public function checkout_then_checkin_frees_the_seat(): void
    {
        Event::fake([CheckoutableCheckedOut::class, CheckoutableCheckedIn::class]);

        $license = License::factory()->create(['seats' => 1, 'reassignable' => true]);
        $user = User::factory()->create();
        $actor = User::factory()->checkoutLicenses()->checkinLicenses()->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.licenses.checkout', $license->id), [
                'target_type' => 'user',
                'assigned_to' => $user->id,
            ])
            ->assertJson(['status' => 'success']);

        $seat = $license->licenseseats()->first();
        $this->assertEquals($user->id, $seat->fresh()->assigned_to);

        $this->actingAsForApi($actor)
            ->postJson(route('api.licenses.checkin', $license->id), [
                'seat_id' => $seat->id,
            ])
            ->assertJson(['status' => 'success']);

        $this->assertNull($seat->fresh()->assigned_to);

        Event::assertDispatched(CheckoutableCheckedOut::class);
        Event::assertDispatched(CheckoutableCheckedIn::class);
    }
}
