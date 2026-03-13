<?php

use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixZeroValuesForLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        App\Models\Asset::where('location_id', '=', '0')
            ->update(['location_id' => null]);

        App\Models\Asset::where('rtd_location_id', '=', '0')
            ->update(['rtd_location_id' => null]);

        App\Models\User::where('location_id', '=', '0')
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
