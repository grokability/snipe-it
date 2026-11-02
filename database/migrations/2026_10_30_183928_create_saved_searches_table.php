<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_searches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by');
            $table->string('table_name');
            $table->string('model')->nullable();
            $table->string('name');
            $table->text('query_string');
            $table->timestamps();

            $table->index(['created_by', 'table_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_searches');
    }
};
