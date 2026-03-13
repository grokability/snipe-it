<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImagesUploadsToLocationsManufacturersEtc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('image')->nullable()->default(null);
        });
        Schema::table('companies', function (Blueprint $table) {
            $table->string('image')->nullable()->default(null);
        });
        Schema::table('components', function (Blueprint $table) {
            $table->string('image')->nullable()->default(null);
        });
        Schema::table('consumables', function (Blueprint $table) {
            $table->string('image')->nullable()->default(null);
        });
        Schema::table('departments', function (Blueprint $table) {
            $table->string('image')->nullable()->default(null);
        });
        Schema::table('locations', function (Blueprint $table) {
            $table->string('image')->nullable()->default(null);
        });
        Schema::table('manufacturers', function (Blueprint $table) {
            $table->string('image')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('image');
        });
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('image');
        });
        Schema::table('components', function (Blueprint $table) {
            $table->dropColumn('image');
        });
        Schema::table('consumables', function (Blueprint $table) {
            $table->dropColumn('image');
        });
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('image');
        });
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('image');
        });
        Schema::table('manufacturers', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
}
