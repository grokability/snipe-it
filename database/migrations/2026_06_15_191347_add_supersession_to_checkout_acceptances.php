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
        Schema::table('checkout_acceptances', function (Blueprint $table) {
            $table->integer('superseded_by_id')->unsigned()->nullable()->index()->after('alert_on_response_id');
            $table->timestamp('superseded_at')->nullable()->after('superseded_by_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checkout_acceptances', function (Blueprint $table) {
            $table->dropColumn(['superseded_by_id', 'superseded_at']);
        });
    }
};
