<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWarrantyUrlToManufacturers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('manufacturers', function (Blueprint $table) {
            $table->string('warranty_lookup_url')->after('support_url')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('manufacturers', function (Blueprint $table) {
            $table->dropColumn('warranty_lookup_url');
        });
    }
}
