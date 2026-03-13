<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageAndSupplierToAccessories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accessories', function (Blueprint $table) {
            $table->string('image')->nullable()->default(null);
            $table->integer('supplier_id')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accessories', function (Blueprint $table) {
            $table->dropColumn('image');
            $table->dropColumn('supplier_id');
        });
    }
}
