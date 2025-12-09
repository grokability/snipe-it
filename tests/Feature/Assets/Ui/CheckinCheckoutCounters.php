<?php

namespace Tests\Feature\Assets\Ui;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Statuslabel;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckinCheckoutCounters extends TestCase
{
    #[Test]
    function counters()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        // create an asset using the GUI
        $this->actingAs($admin)
            ->post(route('hardware.store'), [
                'asset_tags' => ['1' => '1234'],
                'model_id' => AssetModel::factory()->create()->id,
                'status_id' => Statuslabel::factory()->readyToDeploy()->create()->id,
            ])->assertRedirect();

        $asset = Asset::where('asset_tag', '1234')->sole();

        //ensure counters are initialized properly
        $this->assertEquals(0,$asset->checkout_counter);
        $this->assertEquals(0,$asset->checkin_counter);

        //perform a checkout
        $this->actingAs($admin)
            ->post(route('hardware.checkout.store', $asset), [
                'checkout_to_type' => 'user',
                // overwrite the value from the default fields set above
                'assigned_user' => (string) $user->id,
                'name' => 'Changed Name',
                'checkout_at' => '2024-03-18',
                'expected_checkin' => '2024-03-28',
                'note' => 'An awesome note',
            ])->assertRedirect()->assertSessionHasNoErrors();

        $asset->refresh();
//        dump($asset);
        $this->assertEquals(1,$asset->checkout_counter);
        $this->assertEquals(0,$asset->checkin_counter);

        //perform a check-in
        $this->actingAs($admin)
            ->post(
                route('hardware.checkin.store', [$asset]),
                [
                    'name' => 'Changed Name Again',
                ],
            )->assertRedirect()->assertSessionHasNoErrors();

        $asset->refresh();
//        dump($asset);
        $this->assertEquals(1,$asset->checkout_counter);
        $this->assertEquals(1,$asset->checkin_counter);
    }
}