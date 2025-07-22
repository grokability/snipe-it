<?php

namespace Tests\Feature\Assets\Ui;

use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;
use Tests\TestCase;

class BulkDeleteAssetsTest extends TestCase
{
    public function testUserWithPermissionsCanAccessPage()
    {
        $user = User::factory()->viewAssets()->deleteAssets()->editAssets()->create();
        $assets = Asset::factory()->count(2)->create();

        $id_array = $assets->pluck('id')->toArray();

        $this->actingAs($user)->post('/hardware/bulkedit', [
            'ids'          => $id_array,
            'order'        => 'asc',
            'bulk_actions' => 'delete',
            'sort'         => 'id'
        ])->assertStatus(200);
    }

    public function testStandardUserCannotAccessPage()
    {
        $user = User::factory()->create();
        $assets = Asset::factory()->count(2)->create();

        $id_array = $assets->pluck('id')->toArray();

        $this->actingAs($user)->post('/hardware/bulkdelete', [
            'ids'          => $id_array,
            'bulk_actions' => 'delete',
        ])->assertStatus(403);
    }

    public function testPageRedirectFromInterstitialIfNoAssetsSelectedToDelete()
    {
        $user = User::factory()->viewAssets()->deleteAssets()->editAssets()->create();
        $response = $this->actingAs($user)
            ->post('/hardware/bulkdelete', [
                'ids'          => null,
                'bulk_actions' => 'delete',
        ])
        ->assertStatus(302)
        ->assertRedirect(route('hardware.index'));

       $this->followRedirects($response)->assertSee('alert-danger');
    }

    public function testPageRedirectFromInterstitialIfNoAssetsSelectedToRestore()
    {
        $user = User::factory()->viewAssets()->deleteAssets()->editAssets()->create();
        $response = $this->actingAs($user)
            ->from(route('hardware.index'))
            ->post('/hardware/bulkrestore', [
                'ids'          => null,
                'bulk_actions' => 'delete',
            ])
            ->assertStatus(302)
            ->assertRedirect(route('hardware.index'));

        $this->followRedirects($response)->assertSee('alert-danger');
    }


    public function testBulkDeleteSelectedAssetsFromInterstitial()
    {
        $user = User::factory()->viewAssets()->deleteAssets()->editAssets()->create();
        $assets = Asset::factory()->count(2)->create();

        $id_array = $assets->pluck('id')->toArray();

        $response = $this->actingAs($user)
            ->from(route('hardware/bulkedit'))
            ->post('/hardware/bulkdelete', [
            'ids'          => $id_array,
            'bulk_actions' => 'delete',
        ])->assertStatus(302);

        Asset::findMany($id_array)->each(function (Asset $asset)  {
            $this->assertNotNull($asset->deleted_at);
            $this->assertHasTheseActionLogs($asset, ['create', 'delete']);
        });

        $this->followRedirects($response)->assertSee('alert-success');
    }

    public function testBulkRestoreSelectedAssetsFromInterstitial()
    {
        $user = User::factory()->viewAssets()->deleteAssets()->editAssets()->create();
        $asset = Asset::factory()->deleted()->create();
        $this->assertNotNull($asset);

        $asset->refresh();
        $id_array = [$asset->id];
        $this->assertEquals(1, count($id_array));

        $test_ran = false;
        // Check that the assets are deleted
        Asset::whereIn('id', $id_array)->withTrashed()->each(function (Asset $asset) use (&$test_ran) {
            $test_ran = true;
            $this->assertNotNull($asset->deleted_at);
        });
        $this->assertTrue($test_ran, "Test never actually ran!");

        $response = $this->actingAs($user)
            ->from(route('hardware/bulkedit'))
            ->post(route('hardware/bulkrestore'), [
                'ids'          => [$asset->id],
            ])->assertStatus(302);

        $this->followRedirects($response)->assertSee('alert-success');

        $test_ran = false;
        Asset::findMany($id_array)->each(function (Asset $asset) use (&$test_ran) {
            $test_ran = true;
            $this->assertNull($asset->deleted_at);
            $this->assertHasTheseActionLogs($asset, ['create',/* 'delete',*/ 'restore'/*, 'fart'*/]); //SHIT
        });
        $this->assertTrue($test_ran, "Test never actually ran!");
    }


    public function testActionLogCreatedUponBulkDelete()
    {
        $user = User::factory()->viewAssets()->deleteAssets()->editAssets()->create();
        $asset = Asset::factory()->create();

        $this->actingAs($user)
            ->from(route('hardware/bulkedit'))
            ->post('/hardware/bulkdelete', [
                'ids'          => [$asset->id],
                'bulk_actions' => 'delete',
            ]);

        $this->assertDatabaseHas('action_logs',
            [
                'action_type' => 'delete',
                'target_id' => null,
                'target_type' => null,
                'item_id' => $asset->id,
                'item_type' => Asset::class,
            ]
        );

        $asset->refresh();
        $this->assertNull($asset->assigned_to);
        $this->assertNull($asset->assigned_type);
    }

    public function testActionLogCreatedUponBulkRestore()
    {
        $user = User::factory()->viewAssets()->deleteAssets()->editAssets()->create();
        $asset = Asset::factory()->deleted()->create();

        $this->actingAs($user)
            ->from(route('hardware/bulkedit'))
            ->post(route('hardware/bulkrestore'), [
                'ids'          => [$asset->id],
                'bulk_actions' => 'restore',
            ]);

        $this->assertDatabaseHas('action_logs',
            [
                'action_type' => 'restore',
                'target_id' => null,
                'target_type' => null,
                'item_id' => $asset->id,
                'item_type' => Asset::class,
            ]
        );
    }

     public function testBulkDeleteAssignedAssetTriggersError(){
        $user = User::factory()->viewAssets()->deleteAssets()->editAssets()->create();
        $asset = Asset::factory()->create([
            'id' => 5,
            'assigned_to' => $user->id,
            'assigned_type' => User::class,
            'asset_tag' => '12345',
        ]);

        $response = $this->actingAs($user)
                ->from(route('hardware/bulkedit'))
                ->post('/hardware/bulkdelete', [
                    'ids'          => [$asset->id],
                    'bulk_actions' => 'delete',
                ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('hardware.index'), $response->headers->get('Location'));


        $errorMessage = session('error');
        $expectedMessage = trans_choice('admin/hardware/message.delete.assigned_to_error',1, ['asset_tag' => $asset->asset_tag]);
        $this->assertEquals($expectedMessage, $errorMessage);
     }

}
