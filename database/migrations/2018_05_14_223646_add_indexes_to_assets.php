<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToAssets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->index('created_at');
            $table->index(['deleted_at', 'status_id']);
            $table->index(['deleted_at', 'model_id']);
            $table->index(['deleted_at', 'assigned_type', 'assigned_to']);
            $table->index(['deleted_at', 'supplier_id']);
            $table->index(['deleted_at', 'location_id']);
            $table->index(['deleted_at', 'rtd_location_id']);
            $table->index(['deleted_at', 'asset_tag']);
            $table->index(['deleted_at', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['deleted_at', 'status_id']);
            $table->dropIndex(['deleted_at', 'model_id']);
            $table->dropIndex(['deleted_at', 'assigned_type', 'assigned_to']);
            $table->dropIndex(['deleted_at', 'supplier_id']);
            $table->dropIndex(['deleted_at', 'location_id']);
            $table->dropIndex(['deleted_at', 'rtd_location_id']);
            $table->dropIndex(['deleted_at', 'asset_tag']);
            $table->dropIndex(['deleted_at', 'name']);
        });
    }
}
