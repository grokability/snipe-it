<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

        DB::table('assets')
            ->whereNotNull('purchase_date')
            ->whereNotNull('warranty_months')
            ->select('id', 'purchase_date', 'warranty_months')
            ->orderBy('id')
            ->chunkById(1000, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('assets')
                        ->where('id', $row->id)
                        ->update([
                            'warranty_expires' => Carbon::parse($row->purchase_date)
                                ->addMonths((int)$row->warranty_months)
                                ->toDateString(),
                        ]);
                }
            });
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
