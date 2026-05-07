<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

class BlankOutLdapActiveFlag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ($s = Setting::getSettings()) {
            $s->ldap_active_flag = '';
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
        //
    }
}
