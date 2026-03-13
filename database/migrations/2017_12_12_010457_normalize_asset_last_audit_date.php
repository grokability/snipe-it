<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NormalizeAssetLastAuditDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('assets', 'last_audit_date')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->datetime('last_audit_date')->after('assigned_type')->nullable()->default(null);
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
        if (Schema::hasColumn('assets', 'last_audit_date')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->dropColumn('last_audit_date');
            });
        }
    }
}
