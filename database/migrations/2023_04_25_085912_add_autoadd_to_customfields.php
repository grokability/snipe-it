<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAutoaddToCustomfields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            $table->boolean('auto_add_to_fieldsets')->nullable()->default(0);
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
            if (Schema::hasColumn('custom_fields', 'auto_add_to_fieldsets')) {
                $table->dropColumn('auto_add_to_fieldsets');
            }
        });
    }
}
