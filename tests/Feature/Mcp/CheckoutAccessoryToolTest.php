<?php

namespace Tests\Feature\Mcp;

use App\Events\CheckoutableCheckedOut;
use App\Mcp\Tools\CheckoutAccessoryTool;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CheckoutAccessoryToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->checkoutAccessories()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new CheckoutAccessoryTool)->handle(new Request($args));
    }

    public function test_checks_out_accessory_to_user_by_id()
    {
        $accessory = Accessory::factory()->create(['qty' => 5]);
        $user = User::factory()->create();

        $content = $this->handle([
            'id' => $accessory->id,
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('accessories_checkout', [
            'accessory_id' => $accessory->id,
            'assigned_to' => $user->id,
            'assigned_type' => User::class,
        ]);
    }

    public function test_checks_out_accessory_to_user_by_name()
    {
        $accessory = Accessory::factory()->create(['name' => 'Named Checkout Accessory', 'qty' => 5]);
        $user = User::factory()->create();

        $content = $this->handle([
            'name' => 'Named Checkout Accessory',
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
    }

    public function test_checks_out_accessory_to_location()
    {
        $accessory = Accessory::factory()->create(['qty' => 5]);
        $location = Location::factory()->create();

        $content = $this->handle([
            'id' => $accessory->id,
            'checkout_to_type' => 'location',
            'assigned_location' => $location->id,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('accessories_checkout', [
            'accessory_id' => $accessory->id,
            'assigned_to' => $location->id,
            'assigned_type' => Location::class,
        ]);
    }

    public function test_checks_out_accessory_to_asset()
    {
        $accessory = Accessory::factory()->create(['qty' => 5]);
        $asset = Asset::factory()->create();

        $content = $this->handle([
            'id' => $accessory->id,
            'checkout_to_type' => 'asset',
            'assigned_asset' => $asset->id,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('accessories_checkout', [
            'accessory_id' => $accessory->id,
            'assigned_to' => $asset->id,
            'assigned_type' => Asset::class,
        ]);
    }

    public function test_response_includes_checkout_id_for_checkin()
    {
        $accessory = Accessory::factory()->create(['qty' => 5]);
        $user = User::factory()->create();

        $content = $this->handle([
            'id' => $accessory->id,
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
        ])->getStructuredContent();

        $this->assertArrayHasKey('checkout_id', $content);
        $this->assertIsInt($content['checkout_id']);
    }

    public function test_fires_checkout_event()
    {
        Event::fake([CheckoutableCheckedOut::class]);

        $accessory = Accessory::factory()->create(['qty' => 5]);
        $user = User::factory()->create();

        $this->handle([
            'id' => $accessory->id,
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
        ]);

        Event::assertDispatched(CheckoutableCheckedOut::class, function (CheckoutableCheckedOut $event) use ($accessory) {
            return $event->checkoutable->id === $accessory->id;
        });
    }

    public function test_returns_error_when_no_units_remaining()
    {
        $user = User::factory()->create();
        $accessory = Accessory::factory()->withoutItemsRemaining()->create();

        $response = $this->handle([
            'id' => $accessory->id,
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
        ]);

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_returns_error_when_accessory_not_found()
    {
        $this->assertTrue($this->handle([
            'id' => 999999,
            'checkout_to_type' => 'user',
            'assigned_user' => User::factory()->create()->id,
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_target_user_not_found()
    {
        $accessory = Accessory::factory()->create(['qty' => 5]);

        $this->assertTrue($this->handle([
            'id' => $accessory->id,
            'checkout_to_type' => 'user',
            'assigned_user' => 999999,
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $accessory = Accessory::factory()->create(['qty' => 5]);
        $user = User::factory()->create();

        $this->assertTrue($this->handle([
            'id' => $accessory->id,
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
        ])->responses()->first()->isError());
    }
}
