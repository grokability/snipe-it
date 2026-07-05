<?php

namespace Tests\Feature\Assets\Api;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;
use Tests\TestCase;

class UploadedFilesPaginationTest extends TestCase
{
    private User $user;

    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->superuser()->create();
        $this->asset = Asset::factory()->create();
    }

    private function createUploadLog(string $filename): void
    {
        $log = new Actionlog;
        $log->item_id = $this->asset->id;
        $log->item_type = Asset::class;
        $log->action_type = 'uploaded';
        $log->filename = $filename;
        $log->created_by = $this->user->id;
        $log->save();
    }

    public function test_page_one_returns_first_items()
    {
        foreach (range(1, 10) as $i) {
            $this->createUploadLog(sprintf('PAG-TEST-%03d.jpg', $i));
        }

        $filenames = $this->actingAsForApi($this->user)
            ->getJson(route('api.files.index', [
                'object_type' => 'assets',
                'id' => $this->asset->id,
                'page' => 1,
                'limit' => 5,
                'sort' => 'filename',
                'order' => 'asc',
            ]))
            ->assertOk()
            ->json('rows.*.filename');

        $this->assertEquals(
            ['PAG-TEST-001.jpg', 'PAG-TEST-002.jpg', 'PAG-TEST-003.jpg', 'PAG-TEST-004.jpg', 'PAG-TEST-005.jpg'],
            $filenames
        );
    }

    public function test_page_two_returns_second_set_of_items()
    {
        foreach (range(1, 10) as $i) {
            $this->createUploadLog(sprintf('PAG-TEST-%03d.jpg', $i));
        }

        $filenames = $this->actingAsForApi($this->user)
            ->getJson(route('api.files.index', [
                'object_type' => 'assets',
                'id' => $this->asset->id,
                'page' => 2,
                'limit' => 5,
                'sort' => 'filename',
                'order' => 'asc',
            ]))
            ->assertOk()
            ->json('rows.*.filename');

        $this->assertEquals(
            ['PAG-TEST-006.jpg', 'PAG-TEST-007.jpg', 'PAG-TEST-008.jpg', 'PAG-TEST-009.jpg', 'PAG-TEST-010.jpg'],
            $filenames
        );
    }

    public function test_offset_returns_correct_items()
    {
        foreach (range(1, 10) as $i) {
            $this->createUploadLog(sprintf('PAG-TEST-%03d.jpg', $i));
        }

        $filenames = $this->actingAsForApi($this->user)
            ->getJson(route('api.files.index', [
                'object_type' => 'assets',
                'id' => $this->asset->id,
                'offset' => 5,
                'limit' => 5,
                'sort' => 'filename',
                'order' => 'asc',
            ]))
            ->assertOk()
            ->json('rows.*.filename');

        $this->assertEquals(
            ['PAG-TEST-006.jpg', 'PAG-TEST-007.jpg', 'PAG-TEST-008.jpg', 'PAG-TEST-009.jpg', 'PAG-TEST-010.jpg'],
            $filenames
        );
    }

    public function test_page_param_respects_limit()
    {
        foreach (range(1, 10) as $i) {
            $this->createUploadLog(sprintf('PAG-TEST-%03d.jpg', $i));
        }

        $response = $this->actingAsForApi($this->user)
            ->getJson(route('api.files.index', [
                'object_type' => 'assets',
                'id' => $this->asset->id,
                'page' => 1,
                'limit' => 4,
            ]))
            ->assertOk();

        $this->assertCount(4, $response->json('rows'));
    }

    public function test_page_beyond_results_returns_empty_rows()
    {
        foreach (range(1, 5) as $i) {
            $this->createUploadLog(sprintf('PAG-TEST-%03d.jpg', $i));
        }

        $response = $this->actingAsForApi($this->user)
            ->getJson(route('api.files.index', [
                'object_type' => 'assets',
                'id' => $this->asset->id,
                'page' => 99,
                'limit' => 5,
            ]))
            ->assertOk();

        $this->assertCount(0, $response->json('rows'));
        $this->assertEquals(5, $response->json('total'));
    }
}
