<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

class UpdateSlackSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
         * This is a dumb migration that would only affect a few people that would have been caught in the back-in-time
         * migration change to change Slack to slack
         */
        $settings = Setting::where('webhook_selected', '=', 'Slack')->get();

        foreach($settings as $setting){
            $setting->webhook_selected = 'slack';
            $setting->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
