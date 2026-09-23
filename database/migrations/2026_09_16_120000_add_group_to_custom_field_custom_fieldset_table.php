<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_field_custom_fieldset', function (Blueprint $table) {
            $table->string('group')->nullable()->after('order');
        });
    }

    public function down(): void
    {
        Schema::table('custom_field_custom_fieldset', function (Blueprint $table) {
            $table->dropColumn('group');
        });
    }
};
