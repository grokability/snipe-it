<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CreateStatusLabelTool;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CreateStatusLabelToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->createStatusLabels()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new CreateStatusLabelTool)->handle(new Request($args));
    }

    public function test_creates_deployable_status_label()
    {
        $content = $this->handle(['name' => 'Test Deployable', 'type' => 'deployable'])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('status_labels', [
            'name' => 'Test Deployable',
            'deployable' => 1,
            'pending' => 0,
            'archived' => 0,
        ]);
    }

    public function test_creates_pending_status_label()
    {
        $content = $this->handle(['name' => 'Test Pending', 'type' => 'pending'])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('status_labels', [
            'name' => 'Test Pending',
            'deployable' => 0,
            'pending' => 1,
            'archived' => 0,
        ]);
    }

    public function test_response_includes_id_name_and_type()
    {
        $content = $this->handle(['name' => 'Response Status Label', 'type' => 'deployable'])->getStructuredContent();

        $this->assertArrayHasKey('id', $content);
        $this->assertEquals('Response Status Label', $content['name']);
        $this->assertEquals('deployable', $content['type']);
    }

    public function test_returns_error_when_name_missing()
    {
        $this->assertTrue($this->handle(['type' => 'deployable'])->responses()->first()->isError());
    }

    public function test_returns_error_when_type_missing()
    {
        $this->assertTrue($this->handle(['name' => 'No Type Label'])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle(['name' => 'Blocked Label', 'type' => 'deployable'])->responses()->first()->isError());
        $this->assertDatabaseMissing('status_labels', ['name' => 'Blocked Label']);
    }
}
