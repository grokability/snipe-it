<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ListMaintenanceTypesTool;
use App\Models\MaintenanceType;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ListMaintenanceTypesToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewAssets()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ListMaintenanceTypesTool)->handle(new Request($args));
    }

    public function test_returns_total_and_pagination_metadata()
    {
        MaintenanceType::factory()->count(3)->create();

        $content = $this->handle()->getStructuredContent();

        $this->assertArrayHasKey('total', $content);
        $this->assertArrayHasKey('offset', $content);
        $this->assertArrayHasKey('limit', $content);
        $this->assertArrayHasKey('maintenance_types', $content);
        $this->assertGreaterThanOrEqual(3, $content['total']);
    }

    public function test_each_type_includes_id_name_and_tag_color()
    {
        MaintenanceType::factory()->create(['name' => 'UniqueTypeForTest', 'tag_color' => '#ff0000']);

        $content = $this->handle(['limit' => 500])->getStructuredContent();
        $row = collect($content['maintenance_types'])->firstWhere('name', 'UniqueTypeForTest');

        $this->assertNotNull($row);
        $this->assertArrayHasKey('id', $row);
        $this->assertSame('UniqueTypeForTest', $row['name']);
        $this->assertSame('#ff0000', $row['tag_color']);
    }

    public function test_search_narrows_results()
    {
        MaintenanceType::factory()->create(['name' => 'Preventive Maintenance']);
        MaintenanceType::factory()->create(['name' => 'Something Unrelated']);

        $content = $this->handle(['search' => 'Preventive'])->getStructuredContent();
        $names = array_column($content['maintenance_types'], 'name');

        $this->assertContains('Preventive Maintenance', $names);
        $this->assertNotContains('Something Unrelated', $names);
    }

    public function test_results_ordered_alphabetically_by_name()
    {
        MaintenanceType::factory()->create(['name' => 'Zebra']);
        MaintenanceType::factory()->create(['name' => 'Alpha']);
        MaintenanceType::factory()->create(['name' => 'Mango']);

        $content = $this->handle(['limit' => 500])->getStructuredContent();
        $names = array_column($content['maintenance_types'], 'name');

        $alphaPos = array_search('Alpha', $names);
        $mangoPos = array_search('Mango', $names);
        $zebraPos = array_search('Zebra', $names);
        $this->assertLessThan($mangoPos, $alphaPos);
        $this->assertLessThan($zebraPos, $mangoPos);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle()->responses()->first()->isError());
    }
}
