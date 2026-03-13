<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSamlFieldsToSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('saml_enabled')->default(0);
            $table->text('saml_idp_metadata')->nullable()->default(null);
            $table->string('saml_attr_mapping_username')->nullable()->default(null);
            $table->boolean('saml_forcelogin')->default(0);
            $table->boolean('saml_slo')->default(0);
            $table->text('saml_sp_x509cert')->nullable()->default(null);
            $table->text('saml_sp_privatekey')->nullable()->default(null);
            $table->text('saml_custom_settings')->nullable()->default(null);
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
            $table->dropColumn('saml_enabled');
            $table->dropColumn('saml_idp_metadata');
            $table->dropColumn('saml_attr_mapping_username');
            $table->dropColumn('saml_forcelogin');
            $table->dropColumn('saml_slo');
            $table->dropColumn('saml_sp_x509cert');
            $table->dropColumn('saml_sp_privatekey');
            $table->dropColumn('saml_custom_settings');
        });
    }
}
