<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $settings = DB::table('settings')->first();

        if ($settings) {
            /** If webhook settings were cleared via the integration settings page,
             * the webhook_selected was cleared as well when it should have reset to "slack".
             */
            if (
                empty($settings->webhook_selected) &&
                (empty($settings->webhook_botname) && empty($settings->webhook_channel) && empty($settings->webhook_endpoint))
            ) {
                DB::table('settings')->update(['webhook_selected' => 'slack']);
            }

            /** If webhook settings were cleared via the integration settings page,
             * then slack settings were re-added; then webhook_selected was not being set to "slack" as needed.
             */
            if (str_contains($settings->webhook_endpoint, 'slack.com')) {
                DB::table('settings')->update(['webhook_selected' => 'slack']);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
