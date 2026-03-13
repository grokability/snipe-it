<?php

namespace Tests\Unit\Labels;

use App\Models\Asset;
use App\Models\Location;
use App\Models\Setting;
use App\View\Label;
use Tests\TestCase;
use function Livewire\invade;

class LabelTest extends TestCase
{
    /**
     * @link https://app.shortcut.com/grokability/story/29302
     */
    public function test_handles_location_not_being_set_on_asset_gracefully()
    {
        $this->settings->set([
            'label2_enable' => 1,
            'label2_2d_type' => 'QRCODE',
            'label2_2d_target' => 'location',
        ]);

        $location = Location::factory()->create();
        $assets = Asset::factory()->count(2)->create(['location_id' => $location->id]);
        $assets->first()->update(['location_id' => null]);

        // pulled from BulkAssetsController@edit method
        $label = (new Label)
            ->with('assets', $assets)
            ->with('settings', Setting::getSettings())
            ->with('bulkedit', true)
            ->with('count', 0);

        // a simple way to avoid flooding test output with PDF characters.
        invade($label)->destination = 'S';

        $label->render();

        $this->assertTrue(true, 'Label rendering should not throw an error when location is not set on an asset.');
    }
}
