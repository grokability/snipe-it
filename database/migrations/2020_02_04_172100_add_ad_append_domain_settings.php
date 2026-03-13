<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdAppendDomainSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('ad_append_domain')->nullable(false)->default('0');
        });

        $s = Setting::first(); // we are deliberately *not* using the ::getSettings() method, as it caches things, and our Settings table is being migrated right now
        if ($s && $s->is_ad && $s->ldap_enabled && $s->ad_domain) { //backwards-compatibility setting; < v5 always appended AD Domains
            $s->ad_append_domain = 1;
            $s->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('ad_append_domain');
        });
    }
}
