<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToLicenseSeats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('license_seats', function (Blueprint $table) {
            $table->index(['assigned_to','license_id']);
            $table->index(['asset_id','license_id']);
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
            $table->dropIndex(['assigned_to','license_id']);
            $table->dropIndex(['asset_id','license_id']);
        });
    }
}
