<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::table('action_logs', function (Blueprint $table) {
            $table->index(['action_date']);
        });

        DB::update('update '.DB::getTablePrefix().'action_logs set action_date = created_at where created_at IS NOT NULL and action_date IS NULL');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
