<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SwapTargetTypeIndexOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('action_logs', function (Blueprint $table) {
            $table->dropIndex(['target_id', 'target_type']);
        });

        Schema::table('action_logs', function (Blueprint $table) {
            $table->index(['target_type', 'target_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('action_logs', function (Blueprint $table) {
            $table->dropIndex(['target_type', 'target_id']);
        });

        Schema::table('action_logs', function (Blueprint $table) {
            $table->index(['target_id', 'target_type']);
        });
    }
}
