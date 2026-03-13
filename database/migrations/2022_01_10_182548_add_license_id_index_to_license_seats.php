<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLicenseIdIndexToLicenseSeats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('license_seats', function (Blueprint $table) {
            $table->index(['license_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('license_seats', function (Blueprint $table) {
            $table->dropIndex(['license_id']);
        });
    }
}
