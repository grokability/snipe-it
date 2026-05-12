<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\UpdateConsumableTool;
use App\Models\Consumable;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class UpdateConsumableToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->editConsumables()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new UpdateConsumableTool)->handle(new Request($args));
    }

    public function test_updates_consumable_by_id()
    {
        $consumable = Consumable::factory()->create(['qty' => 5]);

        $content = $this->handle([
            'id' => $consumable->id,
            'qty' => 20,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertEquals(20, $content['qty']);
        $this->assertDatabaseHas('consumables', ['id' => $consumable->id, 'qty' => 20]);
    }

    public function test_updates_consumable_by_name()
    {
        $consumable = Consumable::factory()->create(['name' => 'Update By Name', 'qty' => 3]);

        $content = $this->handle([
            'name' => 'Update By Name',
            'qty' => 15,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('consumables', ['id' => $consumable->id, 'qty' => 15]);
    }

    public function test_renames_via_new_name()
    {
        $consumable = Consumable::factory()->create(['name' => 'Old Consumable Name']);

        $content = $this->handle([
            'id' => $consumable->id,
            'new_name' => 'New Consumable Name',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertEquals('New Consumable Name', $content['name']);
        $this->assertDatabaseHas('consumables', ['id' => $consumable->id, 'name' => 'New Consumable Name']);
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999, 'qty' => 5])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $consumable = Consumable::factory()->create(['qty' => 5]);

        $this->assertTrue($this->handle([
            'id' => $consumable->id,
            'qty' => 99,
        ])->responses()->first()->isError());
    }
}
