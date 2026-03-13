<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhoneFaxToLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->string('phone', 20)->after('zip')->nullable()->default(null);
            $table->string('fax', 20)->after('zip')->nullable()->default(null);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->string('phone', 20)->after('name')->nullable()->default(null);
            $table->string('fax', 20)->after('name')->nullable()->default(null);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->string('phone', 20)->after('name')->nullable()->default(null);
            $table->string('fax', 20)->after('name')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('phone');
            $table->dropColumn('fax');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('phone');
            $table->dropColumn('fax');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('phone');
            $table->dropColumn('fax');
        });
    }
}
