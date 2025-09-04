<?php

namespace Tests\Feature\Assets\Api;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Statuslabel;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * You could argue that this should go somewhere else - that'd be fair.
 * But, as of now, the only way to properly ensure that the counters are set properly
 * is to directly hit the app. So that's what this does - via API.
 */
class CheckinCheckoutCounters extends TestCase
{
    #[Test]
    function counters()
    {
        //make an admin who can check in and out stuff
        $admin = User::factory()->superuser()->create();

        //make a user
        $user = User::factory()->create();

        //need a model for the asset
        $model = AssetModel::factory()->create();

        //need a status for the asset, too
        $status = Statuslabel::factory()->readyToDeploy()->create();


        //make an asset using the API (this is for the API after all!)
        $response = $this->actingAsForApi($admin)
            ->postJson(route('api.assets.store'), [
                'asset_tag' => 'random_string',
                'model_id' => $model->id,
                'status_id' => $status->id,
            ])->assertOk()
            ->assertStatusMessageIs('success')
            ->json();
        \Log::error(print_r($response, true));

        //check the counters
        $asset = Asset::find($response['payload']['id']);
        $this->assertEquals(0, $asset->checkin_counter);
        $this->assertEquals(0, $asset->checkout_counter);

        //do a checkout
        $this->actingAsForApi($admin)
            ->postJson(route('api.asset.checkout', $asset), [
                'checkout_to_type' => 'user',
                'assigned_user' => $user->id,
                'checkout_at' => '2024-04-01',
                'expected_checkin' => '2024-04-08',
                'name' => 'Changed Name',
                'note' => 'Here is a cool note!',
            ])
            ->assertOk();

        $asset->refresh();
        //check the counters. both.
        $this->assertEquals(0, $asset->checkin_counter);
        $this->assertEquals(1, $asset->checkout_counter); //why does _this_ fail?!

        //do a checkin
        $this->actingAsForApi(User::factory()->checkinAssets()->create())
            ->postJson(route('api.asset.checkin', $asset), [
                'name' => 'Changed Name',
                'status_id' => $status->id,
            ])
            ->assertOk();

        //check the counters, again.
        $asset->refresh();
        $this->assertEquals(1, $asset->checkin_counter); //wait, _this_ fails too?! WTH?
        $this->assertEquals(1, $asset->checkout_counter); //okay, _nothing_ works. Now I'm confused.
    }
}
