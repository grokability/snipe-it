<?php

use Illuminate\Database\Migrations\Migration;

class MigrateDenormedAssetLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // the contents of this migration have moved to the `php artisan snipeit:sync-asset-locations` script
        // I know its gross to edit a migration, but we had to do this for support purposes. - @snipe
        Artisan::call('snipeit:sync-asset-locations', ['--output' => 'all']);
        $output = Artisan::output();
        \Log::info($output);
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
