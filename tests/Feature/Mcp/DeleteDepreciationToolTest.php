<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\DeleteDepreciationTool;
use App\Models\Depreciation;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class DeleteDepreciationToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->deleteDepreciations()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new DeleteDepreciationTool)->handle(new Request($args));
    }

    public function test_deletes_depreciation_by_id()
    {
        $dep = Depreciation::factory()->create();

        $content = $this->handle(['id' => $dep->id])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseMissing('depreciations', ['id' => $dep->id]);
    }

    public function test_deletes_depreciation_by_name()
    {
        $dep = Depreciation::factory()->create();

        $content = $this->handle(['name' => $dep->name])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseMissing('depreciations', ['id' => $dep->id]);
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
        $this->assertDatabaseHas('depreciations', ['id' => $dep->id]);
    }
}
