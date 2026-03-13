<?php

use App\Models\Asset;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNextAutoincrementToSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $assets = Asset::select('asset_tag')->whereNull('deleted_at')->get();
        if (! $next = Asset::nextAutoIncrement($assets)) {
            $next = 1;
        }

        Schema::table('settings', function (Blueprint $table) use ($next) {
            $table->bigInteger('next_auto_tag_base')->default('1');
        });

        //\Log::debug('Setting '.$next.' as default auto-increment');

        if ($settings = App\Models\Setting::first()) {
            $settings->next_auto_tag_base = $next;
            $settings->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function ($table) {
            $table->dropColumn('next_auto_tag_base');
        });
    }
}
