<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('accessories_users')) {
            Schema::rename('accessories_users', 'accessories_checkout');

            Schema::table('accessories_checkout', function (Blueprint $table) {
                if (!Schema::hasColumn('accessories_checkout', 'assigned_type')) {
                    $table->string('assigned_type')->nullable();
                }
            });
        }
        
        DB::update('update '.DB::getTablePrefix().'accessories_checkout set assigned_type = \'App\\\\Models\\\\User\' where assigned_type is null', []);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('accessories_checkout')) {
            Schema::table('accessories_checkout', function (Blueprint $table) {
                $table->dropColumn('assigned_type');   
            });

            Schema::rename('accessories_checkout', 'accessories_users');
        }
    }
};
