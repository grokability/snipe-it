<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExtendPhoneLengthsInSupplierAndElsewhere extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            //
            $table->string('phone', 35)->nullable()->default(null)->change();
            $table->string('fax', 35)->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            //
            $table->string('phone', 20)->nullable()->default(null)->change();
            $table->string('fax', 20)->nullable()->default(null)->change();
        });
    }
}
