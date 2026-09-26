<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('document_prefix_checkout', 10)->default('CO-');
            $table->string('document_prefix_handover', 10)->default('HO-');
            $table->string('document_prefix_return', 10)->default('RT-');
            $table->integer('document_number_seq')->default(0); // global shared sequence
            $table->boolean('require_checkout_document')->default(false);
            $table->string('document_default_language', 10)->default('en');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'document_prefix_checkout',
                'document_prefix_handover',
                'document_prefix_return',
                'document_number_seq',
                'require_checkout_document',
                'document_default_language',
            ]);
        });
    }
};
