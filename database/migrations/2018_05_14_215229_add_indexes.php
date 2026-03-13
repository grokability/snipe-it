<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('action_logs', function (Blueprint $table) {
            $table->index(['target_id', 'target_type']);
            $table->index('created_at');
            $table->index(['item_type', 'item_id', 'action_type']);
            $table->index(['target_type', 'target_id', 'action_type']);
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
            $table->dropIndex(['target_id', 'target_type']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['item_type', 'item_id', 'action_type']);
            $table->dropIndex(['target_type', 'target_id', 'action_type']);
        });
    }
}
