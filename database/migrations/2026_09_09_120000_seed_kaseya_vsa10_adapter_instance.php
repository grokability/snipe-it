<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('sync_adapter_instances')->where('slug', 'kaseya_vsa10')->exists()) {
            return;
        }

        $now = now();
        DB::table('sync_adapter_instances')->insert([
            'slug' => 'kaseya_vsa10',
            'adapter_type' => 'kaseya_vsa10',
            'label' => 'Kaseya VSA 10',
            'active' => false,
            'built_in' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('sync_adapter_instances')
            ->where('slug', 'kaseya_vsa10')
            ->where('built_in', true)
            ->delete();
    }
};
