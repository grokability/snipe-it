<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type', 20); // checkout|handover|return
            $table->string('language', 10)->default('en');
            $table->string('page_size', 10)->default('A4'); // A4|LETTER|LEGAL
            $table->string('orientation', 2)->default('P'); // P|L
            $table->json('header_config')->nullable();
            $table->json('footer_config')->nullable();
            $table->json('signature_config')->nullable();
            $table->json('asset_columns')->nullable();
            $table->text('body')->nullable(); // controlled placeholder text (see PlaceholderRenderer)
            $table->boolean('active')->default(true);
            $table->integer('created_by')->nullable()->index();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
