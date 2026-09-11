<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('sync_adapter_instances')
            ->where('slug', 'apple_business_manager')
            ->update([
                'slug' => 'abm',
                'adapter_type' => 'abm',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('sync_adapter_instances')
            ->where('slug', 'abm')
            ->update([
                'slug' => 'apple_business_manager',
                'adapter_type' => 'apple_business_manager',
                'updated_at' => now(),
            ]);
    }
};
