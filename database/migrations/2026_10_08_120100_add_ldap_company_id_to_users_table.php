<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Track which company membership was assigned from LDAP, so a company
     * change in the directory can swap that one membership without touching
     * companies assigned by hand in Snipe-IT.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('ldap_company_id')->after('ldap_import')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('ldap_company_id');
        });
    }
};
