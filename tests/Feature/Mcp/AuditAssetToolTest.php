<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\AuditAssetTool;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class AuditAssetToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new AuditAssetTool)->handle(new Request($args));
    }

    public function test_records_audit_by_asset_tag()
    {
        $asset = Asset::factory()->create();

        $content = $this->handle(['asset_tag' => $asset->asset_tag])->getStructuredContent();

        $this->assertTrue($content['success']);
    }

    public function test_records_audit_by_numeric_id()
    {
        $asset = Asset::factory()->create();

        $content = $this->handle(['id' => $asset->id])->getStructuredContent();

        $this->assertTrue($content['success']);
    }

    public function test_records_audit_by_serial()
    {
        $asset = Asset::factory()->create(['serial' => 'SN-AUDIT-001']);

        $content = $this->handle(['serial' => 'SN-AUDIT-001'])->getStructuredContent();

        $this->assertTrue($content['success']);
    }

    public function test_returns_error_when_asset_not_found()
    {
        $this->assertTrue($this->handle(['asset_tag' => 'DOES-NOT-EXIST'])->responses()->first()->isError());
    }

    public function test_sets_last_audit_date_to_now()
    {
        $asset = Asset::factory()->create(['last_audit_date' => null]);

        $this->handle(['asset_tag' => $asset->asset_tag]);

        $this->assertNotNull($asset->fresh()->last_audit_date);
    }

    public function test_respects_explicit_next_audit_date()
    {
        $asset = Asset::factory()->create();

        $this->handle([
            'asset_tag' => $asset->asset_tag,
            'next_audit_date' => '2027-01-15',
        ]);

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'next_audit_date' => '2027-01-15',
        ]);
    }

    public function test_updates_location_when_provided()
    {
        $location = Location::factory()->create();
        $asset = Asset::factory()->create();

        $this->handle([
            'asset_tag' => $asset->asset_tag,
            'location_id' => $location->id,
        ]);

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'location_id' => $location->id,
        ]);
    }

    public function test_does_not_change_location_when_not_provided()
    {
        $location = Location::factory()->create();
        $asset = Asset::factory()->create(['location_id' => $location->id]);

        $this->handle(['asset_tag' => $asset->asset_tag]);

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'location_id' => $location->id,
        ]);
    }

    public function test_response_includes_audit_dates_and_asset_tag()
    {
        $asset = Asset::factory()->create();

        $content = $this->handle(['asset_tag' => $asset->asset_tag])->getStructuredContent();

        $this->assertEquals($asset->asset_tag, $content['asset_tag']);
        $this->assertArrayHasKey('last_audit_date', $content);
        $this->assertArrayHasKey('next_audit_date', $content);
        $this->assertNotNull($content['last_audit_date']);
    }

    public function test_creates_audit_log_entry()
    {
        $asset = Asset::factory()->create();

        $this->handle([
            'asset_tag' => $asset->asset_tag,
            'note' => 'MCP audit note',
        ]);

        $this->assertDatabaseHas('action_logs', [
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_type' => 'audit',
        ]);
    }
}
