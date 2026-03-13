<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeSettingsTableIncreaseSamlIdpMetadataSize extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration changes the format of the saml_idp_metadata field to MEDIUMTEXT
     * to avoid truncating
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->mediumText('saml_idp_metadata')->nullable()->default(null)->change();
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
            $table->text('saml_idp_metadata')->nullable()->default(null)->change();
        });
    }
}
