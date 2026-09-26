<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('document_id')->index();
            $table->integer('item_id')->nullable(); // assets.id at generation time
            // Denormalized on purpose: a signed document must not change when
            // the asset row changes later (immutability requirement).
            $table->string('asset_tag')->nullable();
            $table->string('name')->nullable();
            $table->string('model_name')->nullable();
            $table->string('manufacturer_name')->nullable();
            $table->string('serial')->nullable();
            $table->string('status_name')->nullable();
            $table->string('condition', 20)->nullable(); // new|good|fair|damaged|needs_repair
            $table->text('condition_notes')->nullable();
            $table->json('snapshot')->nullable(); // full asset field snapshot
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_items');
    }
};
