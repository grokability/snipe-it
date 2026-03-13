<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationIndicesToAssets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->index('rtd_location_id');
            $table->index(['assigned_type', 'assigned_to']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            //
            $table->dropIndex(['rtd_location_id']);
            $table->dropIndex(['assigned_type', 'assigned_to']);
        });
    }
}
