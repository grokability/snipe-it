<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\DeleteComponentTool;
use App\Models\Asset;
use App\Models\Component;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class DeleteComponentToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new DeleteComponentTool)->handle(new Request($args));
    }

    public function test_deletes_component_by_id()
    {
        $component = Component::factory()->create();

        $content = $this->handle(['id' => $component->id])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('components', ['id' => $component->id]);
    }

    public function test_deletes_component_by_name()
    {
        $component = Component::factory()->create(['name' => 'Delete By Name Component']);

        $content = $this->handle(['name' => 'Delete By Name Component'])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('components', ['id' => $component->id]);
    }

    public function test_response_includes_name()
    {
        $component = Component::factory()->create(['name' => 'Named Component']);

        $content = $this->handle(['id' => $component->id])->getStructuredContent();

        $this->assertEquals('Named Component', $content['name']);
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999])->responses()->first()->isError());
    }

    public function test_returns_error_when_component_has_checkouts()
    {
        $asset = Asset::factory()->create();
        $component = Component::factory()->checkedOutToAsset($asset)->create();

        $response = $this->handle(['id' => $component->id]);

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertNotSoftDeleted('components', ['id' => $component->id]);
    }
}
