<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('assets')->whereNotNull('assigned_type')->whereNull('assigned_to')->update(['assigned_type' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
