<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\DeleteStatusLabelTool;
use App\Models\Asset;
use App\Models\Statuslabel;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class DeleteStatusLabelToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->deleteStatusLabels()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new DeleteStatusLabelTool)->handle(new Request($args));
    }

    public function test_deletes_status_label_by_id()
    {
        $label = Statuslabel::factory()->create();

        $content = $this->handle(['id' => $label->id])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('status_labels', ['id' => $label->id]);
    }

    public function test_deletes_status_label_by_name()
    {
        $label = Statuslabel::factory()->create(['name' => 'Delete By Name Status Label']);

        $content = $this->handle(['name' => 'Delete By Name Status Label'])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('status_labels', ['id' => $label->id]);
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999])->responses()->first()->isError());
    }

    public function test_returns_error_when_status_label_has_assets()
    {
        $label = Statuslabel::factory()->rtd()->create();
        Asset::factory()->create(['status_id' => $label->id]);

        $response = $this->handle(['id' => $label->id]);

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertNotSoftDeleted('status_labels', ['id' => $label->id]);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $label = Statuslabel::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle(['id' => $label->id])->responses()->first()->isError());
        $this->assertNotSoftDeleted('status_labels', ['id' => $label->id]);
    }
}
