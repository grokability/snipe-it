<?php

use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

class FixZeroValuesForLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Asset::where('location_id', '=', '0')
            ->update(['location_id' => null]);

        Asset::where('rtd_location_id', '=', '0')
            ->update(['rtd_location_id' => null]);

        User::where('location_id', '=', '0')
            ->update(['location_id' => null]);
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
