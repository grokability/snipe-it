<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\UpdateDepreciationTool;
use App\Models\Depreciation;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class UpdateDepreciationToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->editDepreciations()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new UpdateDepreciationTool)->handle(new Request($args));
    }

    public function test_updates_depreciation_by_id()
    {
        $dep = Depreciation::factory()->create(['months' => 12]);

        $content = $this->handle([
            'id' => $dep->id,
            'months' => 24,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('depreciations', [
            'id' => $dep->id,
            'months' => 24,
        ]);
    }

    public function test_updates_months()
    {
        $dep = Depreciation::factory()->create(['months' => 36]);

        $content = $this->handle([
            'id' => $dep->id,
            'months' => 48,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertEquals(48, $content['months']);
    }

    public function test_renames_via_new_name()
    {
        $dep = Depreciation::factory()->create();

        $content = $this->handle([
            'id' => $dep->id,
            'new_name' => 'Renamed Depreciation',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertEquals('Renamed Depreciation', $content['name']);
        $this->assertDatabaseHas('depreciations', [
            'id' => $dep->id,
            'name' => 'Renamed Depreciation',
        ]);
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 99999])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $dep = Depreciation::factory()->create();

        $this->assertTrue($this->handle(['id' => $dep->id, 'months' => 99])->responses()->first()->isError());
    }
}
