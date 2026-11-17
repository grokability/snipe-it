<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CreateMaintenanceTool;
use App\Models\Asset;
use App\Models\MaintenanceType;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CreateMaintenanceToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->editAssets()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new CreateMaintenanceTool)->handle(new Request($args));
    }

    public function test_creates_maintenance_for_asset_via_asset_id()
    {
        $asset = Asset::factory()->laptopZenbook()->create();
        $type = MaintenanceType::factory()->create();

        $this->handle([
            'asset_id' => $asset->id,
            'maintenance_type_id' => $type->id,
            'title' => 'Test Maintenance',
            'start_date' => '2024-01-01',
        ]);

        // Writes land on the polymorphic native columns, not the legacy
        // asset_id mutator path.
        $this->assertDatabaseHas('maintenances', [
            'item_id' => $asset->id,
            'item_type' => Asset::class,
            'name' => 'Test Maintenance',
        ]);
    }

    public function test_creates_maintenance_for_asset_via_asset_tag()
    {
        $asset = Asset::factory()->laptopZenbook()->create(['asset_tag' => 'TAG-LOOKUP']);
        $type = MaintenanceType::factory()->create();

        $this->handle([
            'asset_tag' => 'TAG-LOOKUP',
            'maintenance_type_id' => $type->id,
            'title' => 'Tag Lookup Maintenance',
        ]);

        $this->assertDatabaseHas('maintenances', [
            'item_id' => $asset->id,
            'item_type' => Asset::class,
            'name' => 'Tag Lookup Maintenance',
        ]);
    }

    public function test_completion_date_input_writes_to_expected_completion_date()
    {
        $asset = Asset::factory()->laptopZenbook()->create();
        $type = MaintenanceType::factory()->create();

        $this->handle([
            'asset_id' => $asset->id,
            'maintenance_type_id' => $type->id,
            'title' => 'Legacy Alias',
            'completion_date' => '2026-12-31',
        ]);

        $this->assertDatabaseHas('maintenances', [
            'name' => 'Legacy Alias',
            'expected_completion_date' => '2026-12-31',
        ]);
    }

    public function test_completed_at_input_marks_maintenance_done_and_sets_completed_by()
    {
        $actor = User::factory()->editAssets()->create();
        $this->actingAs($actor);

        $asset = Asset::factory()->laptopZenbook()->create();
        $type = MaintenanceType::factory()->create();

        $this->handle([
            'asset_id' => $asset->id,
            'maintenance_type_id' => $type->id,
            'title' => 'Already Done',
            // start_date has to be before completed_at per the model's
            // after_or_equal:start_date rule on completed_at.
            'start_date' => '2026-06-01',
            'completed_at' => '2026-06-15 12:00:00',
        ]);

        $this->assertDatabaseHas('maintenances', [
            'name' => 'Already Done',
            'completed_at' => '2026-06-15 12:00:00',
            'completed_by' => $actor->id,
        ]);
    }

    public function test_response_includes_item_fields_not_legacy_asset_id()
    {
        $asset = Asset::factory()->laptopZenbook()->create();
        $type = MaintenanceType::factory()->create();

        $content = $this->handle([
            'asset_id' => $asset->id,
            'maintenance_type_id' => $type->id,
            'title' => 'Test Maintenance Response',
            'start_date' => '2024-01-01',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('name', $content);
        $this->assertSame('Asset', $content['item_type']);
        $this->assertSame($asset->id, $content['item_id']);
        $this->assertSame($asset->asset_tag, $content['asset_tag']);
    }

    public function test_returns_error_when_asset_identifier_missing()
    {
        $type = MaintenanceType::factory()->create();

        $this->assertTrue($this->handle([
            'maintenance_type_id' => $type->id,
            'title' => 'Test',
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_title_missing()
    {
        $asset = Asset::factory()->laptopZenbook()->create();
        $type = MaintenanceType::factory()->create();

        $this->assertTrue($this->handle([
            'asset_id' => $asset->id,
            'maintenance_type_id' => $type->id,
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_maintenance_type_missing()
    {
        $asset = Asset::factory()->laptopZenbook()->create();

        $this->assertTrue($this->handle([
            'asset_id' => $asset->id,
            'title' => 'Test',
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_asset_not_found()
    {
        $type = MaintenanceType::factory()->create();

        $this->assertTrue($this->handle([
            'asset_id' => 999999,
            'maintenance_type_id' => $type->id,
            'title' => 'Test',
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $asset = Asset::factory()->laptopZenbook()->create();
        $type = MaintenanceType::factory()->create();

        $this->assertTrue($this->handle([
            'asset_id' => $asset->id,
            'maintenance_type_id' => $type->id,
            'title' => 'Unauthorized Maintenance',
        ])->responses()->first()->isError());
    }
}
