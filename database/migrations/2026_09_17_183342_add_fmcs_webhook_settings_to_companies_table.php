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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('webhook_selected')->nullable();
            $table->text('webhook_endpoint')->nullable();
            $table->string('webhook_channel')->nullable();
            $table->string('webhook_botname')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('webhook_selected');
            $table->dropColumn('webhook_endpoint');
            $table->dropColumn('webhook_channel');
            $table->dropColumn('webhook_botname');
        });
    }
};
