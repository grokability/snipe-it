<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShowInListViewToCustomFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            $table->boolean('show_in_listview')->nullable()->default(0);
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
            if (Schema::hasColumn('custom_fields', 'show_in_listview')) {
                $table->dropColumn('show_in_listview');
            }
        });
    }
}
