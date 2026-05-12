<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ShowStatusLabelTool;
use App\Models\Statuslabel;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ShowStatusLabelToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewStatusLabels()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ShowStatusLabelTool)->handle(new Request($args));
    }

    public function test_returns_status_label_by_id()
    {
        $label = Statuslabel::factory()->create(['name' => 'Show By ID Status Label']);

        $content = $this->handle(['id' => $label->id])->getStructuredContent();

        $this->assertEquals($label->id, $content['id']);
        $this->assertEquals('Show By ID Status Label', $content['name']);
    }

    public function test_returns_status_label_by_name()
    {
        Statuslabel::factory()->create(['name' => 'Show By Name Status Label']);

        $content = $this->handle(['name' => 'Show By Name Status Label'])->getStructuredContent();

        $this->assertEquals('Show By Name Status Label', $content['name']);
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
        $label = Statuslabel::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle(['id' => $label->id])->responses()->first()->isError());
    }
}
