<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShowInRequestableToCustomFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            $table->boolean('show_in_requestable_list')->after('show_in_email')->nullable()->default(0);
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
            if (Schema::hasColumn('custom_fields', 'show_in_requestable_list')) {
                $table->dropColumn('show_in_requestable_list');
            }
        });
    }
}
