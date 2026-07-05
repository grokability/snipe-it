<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('block_api_user_agents')->default(false)->after('null_company_is_floater');
            $table->text('blocked_api_user_agents')->nullable()->after('block_api_user_agents');
            $table->boolean('block_blank_api_user_agents')->default(false)->after('blocked_api_user_agents');
        });

        // Seed the textarea with the suggested defaults so admins don't have to type the list
        // out themselves. Blocking stays off until block_api_user_agents is toggled on, so
        // existing integrations are unaffected by the upgrade.
        DB::table('settings')->update([
            'blocked_api_user_agents' => implode("\n", Setting::DEFAULT_BLOCKED_API_USER_AGENTS),
        ]);
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['block_api_user_agents', 'blocked_api_user_agents', 'block_blank_api_user_agents']);
        });
    }
};
