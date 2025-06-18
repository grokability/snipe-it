<?php

namespace App\Livewire;

use App\Helpers\Helper;
use App\Models\Setting;
use Livewire\Component;

class LocationScopeCheck extends Component
{
    public $mismatched = [];
    public $setting;

    public function check_locations()
    {
        $this->mismatched = Helper::test_locations_fmcs(false);
    }

    public function mount() {
        $this->setting = Setting::getSettings();
        $this->mismatched = Helper::test_locations_fmcs(false);
    }

    public function render()
    {
        return view('livewire.location-scope-check');
    }
}
