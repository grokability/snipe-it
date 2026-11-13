<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\UpdateStatusLabelTool;
use App\Models\Statuslabel;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class UpdateStatusLabelToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->editStatusLabels()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new UpdateStatusLabelTool)->handle(new Request($args));
    }

    public function test_updates_status_label_by_id()
    {
        $label = Statuslabel::factory()->create(['name' => 'Original Status Label']);

        $content = $this->handle([
            'id' => $label->id,
            'new_name' => 'Updated Status Label',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('status_labels', ['id' => $label->id, 'name' => 'Updated Status Label']);
    }

    public function test_renames_via_new_name()
    {
        $label = Statuslabel::factory()->create(['name' => 'Old Status Label Name']);

        $content = $this->handle([
            'name' => 'Old Status Label Name',
            'new_name' => 'New Status Label Name',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertEquals('New Status Label Name', $content['name']);
    }

    public function test_updates_type()
    {
        $label = Statuslabel::factory()->rtd()->create(['name' => 'Type Change Label']);

        $content = $this->handle([
            'id' => $label->id,
            'type' => 'pending',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertEquals('pending', $content['type']);
        $this->assertDatabaseHas('status_labels', [
            'id' => $label->id,
            'pending' => 1,
            'deployable' => 0,
            'archived' => 0,
        ]);
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999, 'new_name' => 'Ghost'])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $label = Statuslabel::factory()->create(['name' => 'Unchanged Status Label']);
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle([
            'id' => $label->id,
            'new_name' => 'Should Not Change',
        ])->responses()->first()->isError());

        $this->assertDatabaseHas('status_labels', ['id' => $label->id, 'name' => 'Unchanged Status Label']);
    }
}
