<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ShowDepreciationTool;
use App\Models\Depreciation;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ShowDepreciationToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewDepreciations()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ShowDepreciationTool)->handle(new Request($args));
    }

    public function test_returns_depreciation_by_id()
    {
        $dep = Depreciation::factory()->create();

        $content = $this->handle(['id' => $dep->id])->getStructuredContent();

        $this->assertEquals($dep->id, $content['id']);
        $this->assertEquals($dep->name, $content['name']);
    }

    public function test_returns_depreciation_by_name()
    {
        $dep = Depreciation::factory()->create();

        $content = $this->handle(['name' => $dep->name])->getStructuredContent();

        $this->assertEquals($dep->id, $content['id']);
        $this->assertEquals($dep->name, $content['name']);
    }

    public function test_returns_error_when_no_identifier_provided()
    {
        $this->assertTrue($this->handle()->responses()->first()->isError());
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 99999])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $dep = Depreciation::factory()->create();

        $this->assertTrue($this->handle(['id' => $dep->id])->responses()->first()->isError());
    }
}
