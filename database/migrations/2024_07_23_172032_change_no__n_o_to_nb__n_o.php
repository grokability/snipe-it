<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Setting::where('locale', 'no-NO')->update(['locale' => 'nb-NO']);
        User::where('locale', 'no-NO')->update(['locale' => 'nb-NO']);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
