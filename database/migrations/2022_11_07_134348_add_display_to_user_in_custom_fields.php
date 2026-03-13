<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDisplayToUserInCustomFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            $table->boolean('display_in_user_view')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            $table->dropColumn('display_in_user_view');
        });
    }
}
