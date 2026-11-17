<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CheckinAccessoryTool;
use App\Mcp\Tools\CheckoutAccessoryTool;
use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CheckinAccessoryToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->checkoutAccessories()->checkinAccessories()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new CheckinAccessoryTool)->handle(new Request($args));
    }

    private function checkoutToUser(Accessory $accessory, User $user): AccessoryCheckout
    {
        $response = (new CheckoutAccessoryTool)->handle(new Request([
            'id' => $accessory->id,
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
        ]));

        $checkoutId = $response->getStructuredContent()['checkout_id'];

        return AccessoryCheckout::find($checkoutId);
    }

    public function test_checks_in_accessory_by_checkout_id()
    {
        $user = User::factory()->create();
        $accessory = Accessory::factory()->create(['qty' => 5]);
        $checkout = $this->checkoutToUser($accessory, $user);

        $content = $this->handle(['checkout_id' => $checkout->id])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseMissing('accessories_checkout', ['id' => $checkout->id]);
    }

    public function test_response_includes_accessory_name()
    {
        $user = User::factory()->create();
        $accessory = Accessory::factory()->create(['name' => 'Checkin Test Accessory', 'qty' => 5]);
        $checkout = $this->checkoutToUser($accessory, $user);

        $content = $this->handle(['checkout_id' => $checkout->id])->getStructuredContent();

        $this->assertEquals('Checkin Test Accessory', $content['accessory_name']);
        $this->assertEquals($accessory->id, $content['accessory_id']);
    }

    public function test_returns_error_when_checkout_not_found()
    {
        $this->assertTrue($this->handle(['checkout_id' => 999999])->responses()->first()->isError());
    }

    public function test_creates_checkin_action_log_entry()
    {
        $user = User::factory()->create();
        $accessory = Accessory::factory()->create(['qty' => 5]);
        $checkout = $this->checkoutToUser($accessory, $user);

        $this->handle(['checkout_id' => $checkout->id]);

        $this->assertDatabaseHas('action_logs', [
            'item_type' => Accessory::class,
            'item_id' => $accessory->id,
            'action_type' => 'checkin from',
        ]);
    }

    public function test_checkout_record_from_factory_can_be_checked_in()
    {
        $user = User::factory()->create();
        $accessory = Accessory::factory()->checkedOutToUser($user)->create();

        $checkout = AccessoryCheckout::where('accessory_id', $accessory->id)->first();

        $content = $this->handle(['checkout_id' => $checkout->id])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseMissing('accessories_checkout', ['id' => $checkout->id]);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $user = User::factory()->create();
        $accessory = Accessory::factory()->checkedOutToUser($user)->create();
        $checkout = AccessoryCheckout::where('accessory_id', $accessory->id)->first();

        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle(['checkout_id' => $checkout->id])->responses()->first()->isError());
        $this->assertDatabaseHas('accessories_checkout', ['id' => $checkout->id]);
    }
}
