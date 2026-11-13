<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Optional template (e.g. "anydesk:{VALUE}") used to render a custom
    // field's value as a clickable link, so IDs for remote-access tools and
    // similar URL handlers can be launched straight from Snipe-IT.
    public function up(): void
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            $table->string('link_template', 191)->nullable()->after('help_text');
        });
    }

    public function down(): void
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            if (Schema::hasColumn('custom_fields', 'link_template')) {
                $table->dropColumn('link_template');
            }
        });
    }
};
