<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
         * The "options" column was originally json but the migration was amended to change it to a text column
         * since json columns cause issues with older versions of mariadb.
         *
         * This migration definitively changes it to a text column
         * for the systems that had successfully run the migration.
         *
         * https://github.com/grokability/snipe-it/issues/16015
         */
        if (Schema::hasTable('report_templates') && Schema::hasColumn('report_templates', 'options')) {
            Schema::table('report_templates', function (Blueprint $table) {
                $table->text('options')->nullable(false)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_templates', function (Blueprint $table) {
            // Instead of attempting to roll this back to json let's just
            // keep it as text since that works for mysql, mariadb, and sqlite.
        });
    }
};
