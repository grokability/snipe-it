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
        DB::table('settings')->where('label2_2d_type', 'default')->update([
            'label2_2d_type' => 'QRCODE',
        ]);

        DB::table('settings')->where('label2_1d_type', 'default')->update([
            'label2_1d_type' => 'C128',
        ]);

        DB::table('settings')->whereNull('label2_2d_type')->orWhere('label2_2d_type', '')->update([
            'label2_2d_type' => 'none',
        ]);

        DB::table('settings')->whereNull('label2_1d_type')->orWhere('label2_1d_type', '')->update([
            'label2_1d_type' => 'none',
        ]);

        Schema::table('settings', function (Blueprint $table) {
            $table->string('label2_2d_type')->default('QRCODE')->nullable(false)->change();
            $table->string('label2_1d_type')->default('C128')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
