<?php

namespace Tests\Feature\Mcp;

use App\Events\CheckoutableCheckedIn;
use App\Mcp\Tools\CheckinLicenseTool;
use App\Mcp\Tools\CheckoutLicenseTool;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CheckinLicenseToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->checkoutLicenses()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new CheckinLicenseTool)->handle(new Request($args));
    }

    private function checkoutToUser(License $license, User $user): LicenseSeat
    {
        $response = (new CheckoutLicenseTool)->handle(new Request([
            'id' => $license->id,
            'assigned_to' => $user->id,
        ]));

        $seatId = $response->getStructuredContent()['seat_id'];

        return LicenseSeat::find($seatId);
    }

    public function test_checks_in_seat_by_seat_id()
    {
        $license = License::factory()->create(['seats' => 3, 'reassignable' => true]);
        $user = User::factory()->create();
        $seat = $this->checkoutToUser($license, $user);

        $content = $this->handle(['seat_id' => $seat->id])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('license_seats', [
            'id' => $seat->id,
            'assigned_to' => null,
            'asset_id' => null,
        ]);
    }

    public function test_response_includes_license_info()
    {
        $license = License::factory()->create(['name' => 'Checkin License', 'seats' => 3, 'reassignable' => true]);
        $user = User::factory()->create();
        $seat = $this->checkoutToUser($license, $user);

        $content = $this->handle(['seat_id' => $seat->id])->getStructuredContent();

        $this->assertEquals($seat->id, $content['seat_id']);
        $this->assertEquals($license->id, $content['license_id']);
        $this->assertEquals('Checkin License', $content['license_name']);
    }

    public function test_fires_checkin_event()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $license = License::factory()->create(['seats' => 3, 'reassignable' => true]);
        $user = User::factory()->create();
        $seat = $this->checkoutToUser($license, $user);

        $this->handle(['seat_id' => $seat->id]);

        Event::assertDispatched(CheckoutableCheckedIn::class);
    }

    public function test_returns_error_when_seat_not_found()
    {
        $this->assertTrue($this->handle(['seat_id' => 999999])->responses()->first()->isError());
    }

    public function test_returns_error_when_seat_is_not_checked_out()
    {
        $license = License::factory()->create(['seats' => 3, 'reassignable' => true]);
        $seat = $license->freeSeat();

        $this->assertTrue($this->handle(['seat_id' => $seat->id])->responses()->first()->isError());
    }

    public function test_sets_unreassignable_flag_when_license_not_reassignable()
    {
        $license = License::factory()->create(['seats' => 1, 'reassignable' => false]);
        $user = User::factory()->create();

        $seat = $license->freeSeat();
        $seat->assigned_to = $user->id;
        $seat->save();

        $this->handle(['seat_id' => $seat->id]);

        $this->assertDatabaseHas('license_seats', [
            'id' => $seat->id,
            'unreassignable_seat' => true,
        ]);
    }

    public function test_does_not_set_unreassignable_flag_when_license_is_reassignable()
    {
        $license = License::factory()->create(['seats' => 3, 'reassignable' => true]);
        $user = User::factory()->create();
        $seat = $this->checkoutToUser($license, $user);

        $this->handle(['seat_id' => $seat->id]);

        $refreshed = LicenseSeat::find($seat->id);
        $this->assertFalse((bool) $refreshed->unreassignable_seat);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $license = License::factory()->create(['seats' => 3, 'reassignable' => true]);
        $user = User::factory()->create();
        $seat = $this->checkoutToUser($license, $user);

        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle(['seat_id' => $seat->id])->responses()->first()->isError());
        $this->assertDatabaseHas('license_seats', [
            'id' => $seat->id,
            'assigned_to' => $user->id,
        ]);
    }
}
