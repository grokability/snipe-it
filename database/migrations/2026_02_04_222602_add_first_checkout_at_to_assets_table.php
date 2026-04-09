<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table("assets", function (Blueprint $table) {
            $table->timestamp('first_checkout_at')->after('next_audit_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("assets", function (Blueprint $table) {
            $table->dropColumn('first_checkout_at');
        });
    }
};
