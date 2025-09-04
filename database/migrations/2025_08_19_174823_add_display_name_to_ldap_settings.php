<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {

            if (!Schema::hasColumn('settings', 'ldap_display_name')) {
                $table->string('ldap_display_name', 191)->after('ldap_fname_field')->nullable()->default(null);
            }

            if (!Schema::hasColumn('settings', 'ldap_zip')) {
                $table->string('ldap_zip', 191)->after('ldap_manager')->nullable()->default(null);
            }

            if (!Schema::hasColumn('settings', 'ldap_state')) {
                $table->string('ldap_state', 191)->after('ldap_manager')->nullable()->default(null);
            }

            if (!Schema::hasColumn('settings', 'ldap_city')) {
                $table->string('ldap_city', 191)->after('ldap_manager')->nullable()->default(null);
            }

            if (!Schema::hasColumn('settings', 'ldap_address')) {
                $table->string('ldap_address', 191)->after('ldap_manager')->nullable()->default(null);
            }

            if (!Schema::hasColumn('settings', 'ldap_mobile')) {
                $table->string('ldap_mobile', 191)->after('ldap_phone_field')->nullable()->default(null);
            }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'ldap_display_name')) {
                $table->dropColumn('ldap_display_name');
            }
        });

        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'ldap_zip')) {
                $table->dropColumn('ldap_zip');
            }
        });
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'ldap_address')) {
                $table->dropColumn('ldap_address');
            }
        });
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'ldap_city')) {
                $table->dropColumn('ldap_city');
            }
        });
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'ldap_state')) {
                $table->dropColumn('ldap_state');
            }
        });
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'ldap_mobile')) {
                $table->dropColumn('ldap_mobile');
            }
        });


    }
};
