<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeSupplierAddressLength extends Migration
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
            $table->string('address', 250)->nullable()->change();
            $table->string('address2', 250)->nullable()->change();
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
            $table->text('address', 50)->nullable()->default(null)->change();
            $table->text('address2', 50)->nullable()->default(null)->change();
        });
    }
}
