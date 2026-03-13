<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProviderToOauthTable extends Migration
{
    /**
     * Run the migrations.
     * Sigh. https://github.com/laravel/passport/blob/master/UPGRADE.md#upgrading-to-90-from-8x
     *
     * @return void
     */
    public function up()
    {
        // Add a 'provider' column if not existing or else modify it
        if (! Schema::hasColumn('oauth_clients', 'provider')) {
            Schema::table('oauth_clients', function (Blueprint $table) {
                $table->string('provider')->after('secret')->nullable();
            });
        } else {
            Schema::table('oauth_clients', function (Blueprint $table) {
                $table->string('provider')->after('secret')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('oauth_clients', 'provider')) {
            Schema::table('oauth_clients', function (Blueprint $table) {
                $table->dropColumn('provider');
            });
        }
    }
}
