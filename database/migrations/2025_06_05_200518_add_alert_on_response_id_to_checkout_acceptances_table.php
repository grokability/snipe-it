<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('checkout_acceptances', function (Blueprint $table) {
            $table->unsignedBigInteger('alert_on_response_id')->nullable()->after('note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checkout_acceptances', function (Blueprint $table) {
            $table->dropColumn('alert_on_response_id');
        });
    }
};
