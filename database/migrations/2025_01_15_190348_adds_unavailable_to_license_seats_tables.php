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
        Schema::table('license_seats', function (Blueprint $table) {
           $table->addColumn('boolean', 'unreassignable_seat')->default(false)->after('assigned_to');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('license_seats', function (Blueprint $table) {
            $table->dropColumn('unreassignable_seat');
        });
    }
};
