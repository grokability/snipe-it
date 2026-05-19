<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id')->index();
            $table->unsignedInteger('user_id')->index();
            $table->timestamps();
            $table->unique(['company_id', 'user_id']);
        });

        // Seed pivot from existing users.company_id values
        DB::table('users')
            ->whereNotNull('company_id')
            ->orderBy('id')
            ->each(function ($user) {
                DB::table('company_user')->insertOrIgnore([
                    'company_id' => $user->company_id,
                    'user_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_user');
    }
};
