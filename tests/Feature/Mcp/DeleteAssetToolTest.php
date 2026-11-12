<?php

namespace Tests\Feature\Mcp;

use App\Events\CheckoutableCheckedIn;
use App\Mcp\Tools\DeleteAssetTool;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class DeleteAssetToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->deleteAssets()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new DeleteAssetTool)->handle(new Request($args));
    }

    public function test_deletes_asset_by_asset_tag()
    {
        $asset = Asset::factory()->create();

        $content = $this->handle(['asset_tag' => $asset->asset_tag])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('assets', ['id' => $asset->id]);
    }

    public function test_deletes_asset_by_numeric_id()
    {
        $asset = Asset::factory()->create();

        $content = $this->handle(['id' => $asset->id])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('assets', ['id' => $asset->id]);
    }

    public function test_deletes_asset_by_serial()
    {
        $asset = Asset::factory()->create(['serial' => 'SN-DELETE-001']);

        $content = $this->handle(['serial' => 'SN-DELETE-001'])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('assets', ['id' => $asset->id]);
    }

    public function test_returns_error_when_asset_not_found()
    {
        $this->assertTrue($this->handle(['asset_tag' => 'DOES-NOT-EXIST'])->responses()->first()->isError());
    }

    public function test_checks_in_asset_before_deleting_when_checked_out()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create();

        $this->handle(['asset_tag' => $asset->asset_tag]);

        Event::assertDispatched(CheckoutableCheckedIn::class, function (CheckoutableCheckedIn $event) use ($asset) {
            return $event->checkoutable->id === $asset->id;
        });

        $this->assertSoftDeleted('assets', ['id' => $asset->id]);
    }

    public function test_deletes_unassigned_asset_without_firing_checkin_event()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $asset = Asset::factory()->create();

        $this->handle(['asset_tag' => $asset->asset_tag]);

        Event::assertNotDispatched(CheckoutableCheckedIn::class);
        $this->assertSoftDeleted('assets', ['id' => $asset->id]);
    }

    public function test_response_includes_asset_tag()
    {
        $asset = Asset::factory()->create(['asset_tag' => 'TAG-DEL-001']);

        $content = $this->handle(['asset_tag' => 'TAG-DEL-001'])->getStructuredContent();

        $this->assertEquals('TAG-DEL-001', $content['asset_tag']);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $asset = Asset::factory()->create();

        $this->assertTrue($this->handle(['asset_tag' => $asset->asset_tag])->responses()->first()->isError());
        $this->assertNotSoftDeleted($asset);
    }

    // --- Bulk delete: array-identifier shape ---

    public function test_array_shape_defaults_to_dry_run_and_does_not_delete()
    {
        $a = Asset::factory()->create();
        $b = Asset::factory()->create();

        $content = $this->handle(['ids' => [$a->id, $b->id]])->getStructuredContent();

        $this->assertTrue($content['dry_run']);
        $this->assertSame(2, $content['total_matched']);
        $this->assertSame(2, $content['will_delete']);
        $this->assertNotSoftDeleted($a);
        $this->assertNotSoftDeleted($b);
    }

    public function test_array_shape_commits_when_dry_run_false()
    {
        $a = Asset::factory()->create();
        $b = Asset::factory()->create();

        $content = $this->handle([
            'ids' => [$a->id, $b->id],
            'dry_run' => false,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertFalse($content['dry_run']);
        $this->assertSame(2, $content['deleted_count']);
        $this->assertSoftDeleted('assets', ['id' => $a->id]);
        $this->assertSoftDeleted('assets', ['id' => $b->id]);
    }

    public function test_array_shape_accepts_asset_tags()
    {
        $a = Asset::factory()->create(['asset_tag' => 'BULK-A']);
        $b = Asset::factory()->create(['asset_tag' => 'BULK-B']);

        $this->handle([
            'asset_tags' => ['BULK-A', 'BULK-B'],
            'dry_run' => false,
        ]);

        $this->assertSoftDeleted('assets', ['id' => $a->id]);
        $this->assertSoftDeleted('assets', ['id' => $b->id]);
    }

    // --- Bulk delete: filter shape ---

    public function test_filter_shape_defaults_to_dry_run()
    {
        $a = Asset::factory()->create();
        $b = Asset::factory()->create();

        $content = $this->handle([
            'created_before' => now()->addDay()->toDateString(),
        ])->getStructuredContent();

        $this->assertTrue($content['dry_run']);
        $this->assertGreaterThanOrEqual(2, $content['total_matched']);
        $this->assertNotSoftDeleted($a);
        $this->assertNotSoftDeleted($b);
    }

    public function test_filter_shape_requires_confirm_count_on_commit()
    {
        Asset::factory()->create();

        $response = $this->handle([
            'created_before' => now()->addDay()->toDateString(),
            'dry_run' => false,
        ]);

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_filter_shape_rejects_mismatched_confirm_count()
    {
        $existingCount = Asset::count();
        Asset::factory()->count(3)->create();

        $response = $this->handle([
            'created_before' => now()->addDay()->toDateString(),
            'dry_run' => false,
            'confirm_count' => $existingCount + 999,
        ]);

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertSame($existingCount + 3, Asset::count());
    }

    public function test_filter_shape_commits_when_confirm_count_matches()
    {
        // Use a filter narrow enough to only match our seeded rows.
        $tagPrefix = 'BULK-FILT-';
        $assets = Asset::factory()->count(3)->sequence(
            ['asset_tag' => $tagPrefix.'1'],
            ['asset_tag' => $tagPrefix.'2'],
            ['asset_tag' => $tagPrefix.'3'],
        )->create();

        $content = $this->handle([
            'search' => $tagPrefix,
            'dry_run' => false,
            'confirm_count' => 3,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSame(3, $content['deleted_count']);
        foreach ($assets as $asset) {
            $this->assertSoftDeleted('assets', ['id' => $asset->id]);
        }
    }

    public function test_filter_shape_resolves_company_name_to_id()
    {
        $company = \App\Models\Company::factory()->create(['name' => 'BulkCo']);
        Asset::factory()->create(['company_id' => $company->id]);
        Asset::factory()->create();

        $content = $this->handle(['company_name' => 'BulkCo'])->getStructuredContent();

        $this->assertTrue($content['dry_run']);
        $this->assertSame(1, $content['total_matched']);
    }
}
