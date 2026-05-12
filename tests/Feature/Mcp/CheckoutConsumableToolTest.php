<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CheckoutConsumableTool;
use App\Models\Consumable;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CheckoutConsumableToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->checkoutConsumables()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new CheckoutConsumableTool)->handle(new Request($args));
    }

    public function test_checks_out_consumable_to_user()
    {
        $consumable = Consumable::factory()->create(['qty' => 5]);
        $user = User::factory()->create();

        $content = $this->handle([
            'id' => $consumable->id,
            'assigned_to' => $user->id,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('consumables_users', [
            'consumable_id' => $consumable->id,
            'assigned_to' => $user->id,
        ]);
    }

    public function test_returns_error_when_no_units_remaining()
    {
        $consumable = Consumable::factory()->withoutItemsRemaining()->create();
        $user = User::factory()->create();

        $response = $this->handle([
            'id' => $consumable->id,
            'assigned_to' => $user->id,
        ]);

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_returns_error_when_consumable_not_found()
    {
        $user = User::factory()->create();

        $this->assertTrue($this->handle([
            'id' => 999999,
            'assigned_to' => $user->id,
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_not_found()
    {
        $consumable = Consumable::factory()->create(['qty' => 5]);

        $this->assertTrue($this->handle([
            'id' => $consumable->id,
            'assigned_to' => 999999,
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $consumable = Consumable::factory()->create(['qty' => 5]);
        $user = User::factory()->create();

        $this->assertTrue($this->handle([
            'id' => $consumable->id,
            'assigned_to' => $user->id,
        ])->responses()->first()->isError());
    }
}
