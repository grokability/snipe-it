<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrimaryKeyToCustomFieldsPivot extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // Check if the ID primary key already exists, if not, add it
        Schema::table('custom_field_custom_fieldset', function (Blueprint $table) {
            if (!Schema::hasColumn('custom_field_custom_fieldset', 'id')) {
                $table->bigIncrements('id')->first();
            }
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('custom_field_custom_fieldset', function (Blueprint $table) {
            if (Schema::hasColumn('custom_field_custom_fieldset', 'id')) {
                $table->dropColumn('id');
            }
        });
    }
}
