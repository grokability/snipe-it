<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_signatures', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('document_id')->index();
            $table->string('role', 30); // employee|it_representative|manager|custom
            $table->integer('signer_user_id')->nullable()->index();
            $table->string('signer_name')->nullable();
            $table->string('signature_filename')->nullable(); // private_uploads/signatures/...
            $table->string('method', 10);                     // printed|digital
            $table->timestamp('signed_at')->nullable();
            $table->string('remote_ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->integer('checkout_acceptance_id')->nullable(); // link to native acceptance flow
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['document_id', 'role']); // one signature per role per document
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_signatures');
    }
};
