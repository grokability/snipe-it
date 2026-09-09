<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->date('warranty_expires')->nullable()->after('warranty_months');
            $table->index('warranty_expires');
        });
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::table('assets')
                ->whereNotNull('purchase_date')
                ->whereNotNull('warranty_months')
                ->update([
                    'warranty_expires' => DB::raw(
                        "date(purchase_date, '+' || warranty_months || ' months')"
                    ),
                ]);
        } else {
            DB::table('assets')
                ->whereNotNull('purchase_date')
                ->whereNotNull('warranty_months')
                ->update([
                    'warranty_expires' => DB::raw(
                        'DATE_ADD(purchase_date, INTERVAL warranty_months MONTH)'
                    ),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndex(['warranty_expires']);
            $table->dropColumn('warranty_expires');
        });
    }
};
