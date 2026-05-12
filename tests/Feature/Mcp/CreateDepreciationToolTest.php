<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CreateDepreciationTool;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CreateDepreciationToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->createDepreciations()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new CreateDepreciationTool)->handle(new Request($args));
    }

    public function test_creates_depreciation()
    {
        $content = $this->handle(['name' => 'Test Depreciation', 'months' => 36])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('depreciations', [
            'name' => 'Test Depreciation',
            'months' => 36,
        ]);
    }

    public function test_response_includes_id_name_and_months()
    {
        $content = $this->handle(['name' => 'Response Depreciation', 'months' => 24])->getStructuredContent();

        $this->assertArrayHasKey('id', $content);
        $this->assertEquals('Response Depreciation', $content['name']);
        $this->assertEquals(24, $content['months']);
    }

    public function test_returns_error_when_name_missing()
    {
        $this->assertTrue($this->handle(['months' => 36])->responses()->first()->isError());
    }

    public function test_returns_error_when_months_missing()
    {
        $this->assertTrue($this->handle(['name' => 'No Months'])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle(['name' => 'Blocked Depreciation', 'months' => 12])->responses()->first()->isError());
        $this->assertDatabaseMissing('depreciations', ['name' => 'Blocked Depreciation']);
    }
}
