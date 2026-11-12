<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ListStatusLabelsTool;
use App\Models\Statuslabel;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ListStatusLabelsToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewStatusLabels()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ListStatusLabelsTool)->handle(new Request($args));
    }

    public function test_returns_status_labels_with_pagination()
    {
        Statuslabel::factory()->count(3)->create();

        $content = $this->handle()->getStructuredContent();

        $this->assertArrayHasKey('status_labels', $content);
        $this->assertGreaterThanOrEqual(3, $content['total']);
    }

    public function test_each_status_label_has_key_fields()
    {
        Statuslabel::factory()->create(['name' => 'Key Fields Status Label']);

        $content = $this->handle(['search' => 'Key Fields Status Label'])->getStructuredContent();

        $this->assertNotEmpty($content['status_labels']);
        $label = $content['status_labels'][0];
        $this->assertArrayHasKey('id', $label);
        $this->assertArrayHasKey('name', $label);
        $this->assertArrayHasKey('type', $label);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle()->responses()->first()->isError());
    }
}
