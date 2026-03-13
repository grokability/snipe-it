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
    public function up()
    {
        // Copy values if target columns are blank
        DB::table('settings')->whereNull('label2_2d_type')->orWhere('label2_2d_type', '')->update([
            'label2_2d_type' => DB::raw('barcode_type')
        ]);

        DB::table('settings')->whereNull('label2_1d_type')->orWhere('label2_1d_type', '')->update([
            'label2_1d_type' => DB::raw('alt_barcode')
        ]);


        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['barcode_type', 'alt_barcode']);
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            // Re-add the columns that were dropped in case of rollback
            $table->string('barcode_type')->nullable();
            $table->string('alt_barcode')->nullable();
        });

        DB::table('settings')->whereNull('barcode_type')->orWhere('barcode_type', '')->update([
            'barcode_type' => DB::raw('label2_2d_type')
        ]);

        DB::table('settings')->whereNull('alt_barcode')->orWhere('alt_barcode', '')->update([
            'alt_barcode' => DB::raw('label2_1d_type')
        ]);
    }
};
