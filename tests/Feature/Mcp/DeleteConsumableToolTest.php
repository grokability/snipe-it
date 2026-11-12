<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\DeleteConsumableTool;
use App\Models\Consumable;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class DeleteConsumableToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->deleteConsumables()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new DeleteConsumableTool)->handle(new Request($args));
    }

    public function test_deletes_consumable_by_id()
    {
        $consumable = Consumable::factory()->create();

        $content = $this->handle(['id' => $consumable->id])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('consumables', ['id' => $consumable->id]);
    }

    public function test_deletes_consumable_by_name()
    {
        $consumable = Consumable::factory()->create(['name' => 'Delete By Name Consumable']);

        $content = $this->handle(['name' => 'Delete By Name Consumable'])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('consumables', ['id' => $consumable->id]);
    }

    public function test_response_includes_name()
    {
        $consumable = Consumable::factory()->create(['name' => 'Named Delete Consumable']);

        $content = $this->handle(['id' => $consumable->id])->getStructuredContent();

        $this->assertEquals('Named Delete Consumable', $content['name']);
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999])->responses()->first()->isError());
    }

    public function test_returns_error_when_consumable_has_checkouts()
    {
        $consumable = Consumable::factory()->create(['qty' => 5]);

        DB::table('consumables_users')->insert([
            'consumable_id' => $consumable->id,
            'assigned_to' => User::factory()->create()->id,
            'created_at' => now(),
            'updated_at' => now(),
            'note' => null,
            'created_by' => auth()->id(),
        ]);

        $response = $this->handle(['id' => $consumable->id]);

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertNotSoftDeleted('consumables', ['id' => $consumable->id]);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $consumable = Consumable::factory()->create();

        $this->assertTrue($this->handle(['id' => $consumable->id])->responses()->first()->isError());
        $this->assertNotSoftDeleted('consumables', ['id' => $consumable->id]);
    }
}
