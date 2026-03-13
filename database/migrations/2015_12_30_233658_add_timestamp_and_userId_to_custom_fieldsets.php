<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddTimestampAndUserIdToCustomFieldsets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('custom_fieldsets', function (Blueprint $table) {
            $table->timestamps();
            $table->integer('user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('custom_fieldsets', function (Blueprint $table) {
            $table->dropTimestamps();
            $table->dropColumn('user_id');
        });
    }
}
