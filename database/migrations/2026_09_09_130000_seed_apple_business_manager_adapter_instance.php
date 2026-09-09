<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('sync_adapter_instances')->where('slug', 'apple_business_manager')->exists()) {
            return;
        }

        $now = now();
        DB::table('sync_adapter_instances')->insert([
            'slug' => 'apple_business_manager',
            'adapter_type' => 'apple_business_manager',
            'label' => 'Apple Business Manager',
            'active' => false,
            'built_in' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('sync_adapter_instances')
            ->where('slug', 'apple_business_manager')
            ->where('built_in', true)
            ->delete();
    }
};
