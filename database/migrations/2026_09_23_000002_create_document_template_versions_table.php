<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_template_versions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('document_template_id')->index();
            $table->integer('version')->default(1);
            // Full frozen copy of the template at publish time:
            // name, header, body, footer, EULA, signature + asset column config
            $table->json('snapshot');
            $table->boolean('eula_enabled')->default(false);
            $table->string('eula_title')->nullable();
            $table->text('eula_body')->nullable();
            $table->string('eula_version', 40)->nullable();
            $table->integer('published_by')->nullable();
            $table->timestamps();

            $table->unique(['document_template_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_template_versions');
    }
};
