<?php

namespace Tests\Feature\Assets\Api;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;
use App\Models\CustomField;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class AuditAssetTest extends TestCase
{
    public function testThatANonExistentAssetIdReturnsError()
    {
        $this->actingAsForApi(User::factory()->auditAssets()->create())
            ->postJson(route('api.asset.audit', 123456789))
            ->assertStatusMessageIs('error');
    }

    public function testRequiresPermissionToAuditAsset()
    {
        $asset = Asset::factory()->create();
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.asset.audit', $asset))
            ->assertForbidden();
    }

    public function testLegacyAssetAuditIsSaved()
    {
        $asset = Asset::factory()->create();
        $this->actingAsForApi(User::factory()->auditAssets()->create())
            ->postJson(route('api.asset.audit.legacy'), [
                'asset_tag' => $asset->asset_tag,
                'note' => 'test',
            ])
            ->assertStatusMessageIs('success')
            ->assertJson(
                [
                    'messages' =>trans('admin/hardware/message.audit.success'),
                    'payload' => [
                        'id' => $asset->id,
                        'asset_tag' => $asset->asset_tag,
                        'note' => 'test'
                    ],
                ])
            ->assertStatus(200);

    }


    public function testAssetAuditIsSaved()
    {
        $asset = Asset::factory()->create();
        $this->actingAsForApi(User::factory()->auditAssets()->create())
            ->postJson(route('api.asset.audit', $asset), [
                'note' => 'test'
            ])
            ->assertStatusMessageIs('success')
            ->assertJson(
                [
                    'messages' =>trans('admin/hardware/message.audit.success'),
                    'payload' => [
                        'id' => $asset->id,
                        'asset_tag' => $asset->asset_tag,
                        'note' => 'test'
                    ],
                ])
            ->assertStatus(200);
        $this->assertHasTheseActionLogs($asset, ['create', 'audit']);
    }


}
