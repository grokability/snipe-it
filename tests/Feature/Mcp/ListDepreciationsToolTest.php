<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ListDepreciationsTool;
use App\Models\Depreciation;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ListDepreciationsToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewDepreciations()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ListDepreciationsTool)->handle(new Request($args));
    }

    public function test_returns_depreciations_with_pagination()
    {
        Depreciation::factory()->count(3)->create();

        $content = $this->handle()->getStructuredContent();

        $this->assertArrayHasKey('total', $content);
        $this->assertArrayHasKey('depreciations', $content);
        $this->assertGreaterThanOrEqual(3, $content['total']);
    }

    public function test_each_depreciation_has_key_fields()
    {
        Depreciation::factory()->create();

        $content = $this->handle()->getStructuredContent();

        $this->assertNotEmpty($content['depreciations']);
        $first = $content['depreciations'][0];
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('name', $first);
        $this->assertArrayHasKey('months', $first);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle()->responses()->first()->isError());
    }
}
