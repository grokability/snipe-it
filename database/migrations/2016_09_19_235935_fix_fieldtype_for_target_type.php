<?php

use Illuminate\Database\Migrations\Migration;

class FixFieldtypeForTargetType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('action_logs', function ($table) {
            $table->string('target_type')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('action_logs', function ($table) {
            $table->integer('target_type')->nullable()->default(null)->change();
        });
    }
}
