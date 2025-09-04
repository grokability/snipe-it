<?php

namespace App\Livewire;

use App\Helpers\Helper;
use App\Models\Setting;
use Livewire\Component;

class LocationScopeCheck extends Component
{
    public $mismatched = [];
    public $setting;
    public $is_tested = false;

    public function check_locations()
    {
        $this->mismatched = Helper::test_locations_fmcs(false);
        $this->is_tested = true;
    }

    public function mount() {
        $this->setting = Setting::getSettings();
    }

    public function render()
    {
        return view('livewire.location-scope-check');
    }
}
