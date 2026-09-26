<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number', 40)->unique(); // CO-2026-000142
            $table->string('type', 20);              // checkout|handover|return
            $table->string('status', 30)->default('draft'); // draft|generated|pending_signature|partially_signed|signed|cancelled
            $table->integer('document_template_version_id')->index();
            $table->integer('assigned_to_id')->nullable()->index(); // employee user
            $table->integer('company_id')->nullable()->index();     // FMCS scoping
            $table->integer('checkout_log_id')->nullable();         // action_logs row
            $table->json('data')->nullable();                       // placeholder snapshot at generation
            $table->string('pdf_path')->nullable();                 // private_uploads/documents/...
            $table->string('pdf_sha256', 64)->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->text('notes')->nullable();
            $table->integer('created_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
