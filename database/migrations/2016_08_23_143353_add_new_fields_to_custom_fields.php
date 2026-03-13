<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddNewFieldsToCustomFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            //
            $table->string('field_values')->nullable()->default(null);
            $table->boolean('field_encrypted')->default(0);
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
            //
            $table->dropColumn('field_values', 'field_encrypted');
        });
    }
}
