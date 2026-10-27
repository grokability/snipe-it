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
        Schema::table('models', function (Blueprint $table) {
            //decimal consistent with purchase_cost on asset model
            $table->decimal('default_purchase_cost', 20, 2)->nullable()->after('min_amt');
            $table->string('default_currency', 10)->nullable()->after('default_purchase_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('models', function (Blueprint $table) {
            $table->dropColumn('default_purchase_cost');
            $table->dropColumn('default_currency');
        });
    }
};
