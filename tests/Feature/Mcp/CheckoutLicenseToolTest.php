<?php

namespace Tests\Feature\Mcp;

use App\Events\CheckoutableCheckedOut;
use App\Mcp\Tools\CheckoutLicenseTool;
use App\Models\Asset;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CheckoutLicenseToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->checkoutLicenses()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new CheckoutLicenseTool)->handle(new Request($args));
    }

    public function test_checks_out_license_to_user_by_id()
    {
        $license = License::factory()->create(['seats' => 3, 'reassignable' => true]);
        $user = User::factory()->create();

        $content = $this->handle([
            'id' => $license->id,
            'assigned_to' => $user->id,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertEquals('user', $content['assigned_to_type']);
        $this->assertEquals($user->id, $content['assigned_to_id']);
        $this->assertDatabaseHas('license_seats', [
            'license_id' => $license->id,
            'assigned_to' => $user->id,
        ]);
    }

    public function test_checks_out_license_to_user_by_name()
    {
        $license = License::factory()->create(['name' => 'Named Checkout License', 'seats' => 3, 'reassignable' => true]);
        $user = User::factory()->create();

        $content = $this->handle([
            'name' => 'Named Checkout License',
            'assigned_to' => $user->id,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
    }

    public function test_checks_out_license_to_asset()
    {
        $license = License::factory()->create(['seats' => 3, 'reassignable' => true]);
        $asset = Asset::factory()->create();

        $content = $this->handle([
            'id' => $license->id,
            'asset_id' => $asset->id,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertEquals('asset', $content['assigned_to_type']);
        $this->assertEquals($asset->id, $content['assigned_to_id']);
        $this->assertDatabaseHas('license_seats', [
            'license_id' => $license->id,
            'asset_id' => $asset->id,
        ]);
    }

    public function test_response_includes_seat_id_for_checkin()
    {
        $license = License::factory()->create(['seats' => 3, 'reassignable' => true]);
        $user = User::factory()->create();

        $content = $this->handle([
            'id' => $license->id,
            'assigned_to' => $user->id,
        ])->getStructuredContent();

        $this->assertArrayHasKey('seat_id', $content);
        $this->assertIsInt($content['seat_id']);
        $this->assertNotNull(LicenseSeat::find($content['seat_id']));
    }

    public function test_fires_checkout_event()
    {
        Event::fake([CheckoutableCheckedOut::class]);

        $license = License::factory()->create(['seats' => 3, 'reassignable' => true]);
        $user = User::factory()->create();

        $this->handle([
            'id' => $license->id,
            'assigned_to' => $user->id,
        ]);

        Event::assertDispatched(CheckoutableCheckedOut::class);
    }

    public function test_returns_error_when_no_seats_remaining()
    {
        $license = License::factory()->create(['seats' => 1, 'reassignable' => true]);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $seat = $license->freeSeat();
        $seat->assigned_to = $user1->id;
        $seat->save();

        $response = $this->handle([
            'id' => $license->id,
            'assigned_to' => $user2->id,
        ]);

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_returns_error_when_neither_assignee_provided()
    {
        $license = License::factory()->create(['seats' => 3]);

        $this->assertTrue($this->handle(['id' => $license->id])->responses()->first()->isError());
    }

    public function test_returns_error_when_license_not_found()
    {
        $this->assertTrue($this->handle([
            'id' => 999999,
            'assigned_to' => User::factory()->create()->id,
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_not_found()
    {
        $license = License::factory()->create(['seats' => 3]);

        $this->assertTrue($this->handle([
            'id' => $license->id,
            'assigned_to' => 999999,
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $license = License::factory()->create(['seats' => 3, 'reassignable' => true]);
        $user = User::factory()->create();

        $this->assertTrue($this->handle([
            'id' => $license->id,
            'assigned_to' => $user->id,
        ])->responses()->first()->isError());
    }
}
