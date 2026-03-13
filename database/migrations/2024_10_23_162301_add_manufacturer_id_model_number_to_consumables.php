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
        Schema::table('components', function (Blueprint $table) {
            $table->integer('manufacturer_id')->after('purchase_cost')->nullable()->default(null);
            $table->string('model_number')->after('purchase_cost')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('components', function (Blueprint $table) {
            $table->dropColumn('manufacturer_id');
            $table->dropColumn('model_number');
        });
    }
};
