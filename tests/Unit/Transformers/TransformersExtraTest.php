<?php

namespace Tests\Unit\Transformers;

use App\Http\Transformers\AssetsTransformer;
use App\Http\Transformers\ProfileTransformer;
use App\Http\Transformers\UploadedFilesTransformer;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\LicenseSeat;
use App\Models\User;
use Tests\TestCase;

class TransformersExtraTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_assets_datatable_and_compact_and_requested(): void
    {
        $assets = Asset::factory()->count(2)->create();
        $transformer = new AssetsTransformer;

        $this->assertIsArray($transformer->transformAssetsDatatable($assets));
        $this->assertIsArray($transformer->transformAssetCompact($assets->first()));
        $this->assertArrayHasKey('rows', $transformer->transformRequestedAssets($assets, $assets->count()));
    }

    public function test_assigned_to_for_user_assigned_asset(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create(['assigned_to' => $user->id, 'assigned_type' => User::class]);

        $result = (new AssetsTransformer)->transformAssignedTo($asset);
        $this->assertTrue(is_array($result) || is_null($result));
    }

    public function test_licenses_checked_to_asset(): void
    {
        $seats = LicenseSeat::factory()->count(2)->create();

        $result = (new AssetsTransformer)->transformLicensesCheckedToAsset($seats, $seats->count());

        $this->assertArrayHasKey('rows', $result);
    }

    public function test_profile_and_uploaded_files_transformers(): void
    {
        $asset = Asset::factory()->create();
        $logs = Actionlog::factory()->count(2)->create([
            'action_type' => 'uploaded',
            'item_id' => $asset->id,
            'item_type' => Asset::class,
            'filename' => 'file.pdf',
        ]);

        $this->assertArrayHasKey('rows', (new ProfileTransformer)->transformFiles($logs, $logs->count()));
        $this->assertArrayHasKey('rows', (new UploadedFilesTransformer)->transformFiles($logs, $logs->count()));
    }
}
