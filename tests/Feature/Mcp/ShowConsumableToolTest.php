<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ShowConsumableTool;
use App\Models\Consumable;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ShowConsumableToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewConsumables()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ShowConsumableTool)->handle(new Request($args));
    }

    public function test_returns_consumable_by_id()
    {
        $consumable = Consumable::factory()->create(['name' => 'Find By ID Consumable']);

        $content = $this->handle(['id' => $consumable->id])->getStructuredContent();

        $this->assertEquals($consumable->id, $content['id']);
        $this->assertEquals('Find By ID Consumable', $content['name']);
    }

    public function test_returns_consumable_by_name()
    {
        Consumable::factory()->create(['name' => 'Find By Name Consumable']);

        $content = $this->handle(['name' => 'Find By Name Consumable'])->getStructuredContent();

        $this->assertEquals('Find By Name Consumable', $content['name']);
    }

    public function test_response_includes_qty_and_users_count()
    {
        $consumable = Consumable::factory()->create(['qty' => 5]);

        $content = $this->handle(['id' => $consumable->id])->getStructuredContent();

        $this->assertEquals(5, $content['qty']);
        $this->assertArrayHasKey('users_count', $content);
        $this->assertEquals(0, $content['users_count']);
    }

    public function test_returns_error_when_no_identifier_provided()
    {
        $this->assertTrue($this->handle()->responses()->first()->isError());
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $consumable = Consumable::factory()->create();

        $this->assertTrue($this->handle(['id' => $consumable->id])->responses()->first()->isError());
    }
}
