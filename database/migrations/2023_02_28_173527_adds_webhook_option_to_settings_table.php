<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddsWebhookOptionToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * So...you're probably wondering why this isn't all in one Schema::table()...
         * Turns out we'll get the following error:
         * "SQLite doesn't support multiple calls to dropColumn / renameColumn in a single modification."
         * if we're running sqlite so a solution is to make multiple calls.
         * ¯\_(ツ)_/¯
         */
        Schema::table('settings', function (Blueprint $table) {
            $table->string('webhook_selected')->after('slack_botname')->default('slack')->nullable();
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->renameColumn('slack_botname', 'webhook_botname');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->renameColumn('slack_endpoint', 'webhook_endpoint');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->renameColumn('slack_channel', 'webhook_channel');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('webhook_selected');
            $table->renameColumn('webhook_botname', 'slack_botname');
            $table->renameColumn('webhook_endpoint', 'slack_endpoint');
            $table->renameColumn('webhook_channel', 'slack_channel');
        });
    }
}
