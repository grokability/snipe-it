<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CreateMaintenanceTypeTool;
use App\Models\MaintenanceType;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CreateMaintenanceTypeToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->editAssets()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new CreateMaintenanceTypeTool)->handle(new Request($args));
    }

    public function test_creates_maintenance_type_with_required_name()
    {
        $this->handle(['name' => 'Preventive Maintenance']);

        $this->assertDatabaseHas('maintenance_types', [
            'name' => 'Preventive Maintenance',
        ]);
    }

    public function test_creates_maintenance_type_with_tag_color()
    {
        $this->handle([
            'name' => 'Urgent Repair',
            'tag_color' => '#ff0000',
        ]);

        $this->assertDatabaseHas('maintenance_types', [
            'name' => 'Urgent Repair',
            'tag_color' => '#ff0000',
        ]);
    }

    public function test_response_includes_id_name_and_tag_color()
    {
        $content = $this->handle([
            'name' => 'Response Check',
            'tag_color' => '#00ff00',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertArrayHasKey('id', $content);
        $this->assertSame('Response Check', $content['name']);
        $this->assertSame('#00ff00', $content['tag_color']);
    }

    public function test_rejects_duplicate_name()
    {
        MaintenanceType::factory()->create(['name' => 'Already Exists']);

        $response = $this->handle(['name' => 'Already Exists']);

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_returns_error_when_name_missing()
    {
        $this->assertTrue($this->handle([])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle(['name' => 'Unauthorized Type'])->responses()->first()->isError());
    }
}
