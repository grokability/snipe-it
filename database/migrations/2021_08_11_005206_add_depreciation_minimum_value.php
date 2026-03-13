<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDepreciationMinimumValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('depreciations', function (Blueprint $table) {
            $table->decimal('depreciation_min', 8,2)->after('months')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('depreciations', function (Blueprint $table) {
            $table->dropColumn('depreciation_min');
        });
    }
}
