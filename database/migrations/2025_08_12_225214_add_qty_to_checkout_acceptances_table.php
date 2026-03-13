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
        Schema::whenTableDoesntHaveColumn('checkout_acceptances', 'qty', function () {
            Schema::table('checkout_acceptances', function (Blueprint $table) {
                $table->unsignedInteger('qty')->nullable()->after('assigned_to_id')->default(null);
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::whenTableHasColumn('checkout_acceptances', 'qty', function () {
            Schema::table('checkout_acceptances', function (Blueprint $table) {
                $table->dropColumn('qty');
            });
        });
    }
};
