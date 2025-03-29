<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddTypeToLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Kreiramo tabelu location_types ako ne postoji
        if (!Schema::hasTable('location_types')) {
            Schema::create('location_types', function (Blueprint $table) {
                $table->id(); // Auto-increment ID
                $table->string('name'); // Kolona za ime tipa lokacije
                $table->timestamps(); // Automatski created_at i updated_at
            });

            // Dodajemo početne tipove lokacija u location_types tabelu
            DB::table('location_types')->insert([
                ['name' => 'Lokacija platnog prometa'],
                ['name' => 'Zastupnik platnog prometa'],
                ['name' => 'Magacin'],
                ['name' => 'Kancelarija'],
            ]);
        }

        // Dodajemo kolonu type_id u tabelu locations (ako nije već postojala)
        Schema::table('locations', function (Blueprint $table) {
            if (!Schema::hasColumn('locations', 'type_id')) {
                $table->unsignedBigInteger('type_id')->nullable()->default(1); // Podrazumevani tip: 'Lokacija platnog prometa'
                $table->foreign('type_id')->references('id')->on('location_types')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Uklanjamo strani ključ i kolonu type_id iz locations tabele
        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign(['type_id']);
            $table->dropColumn('type_id');
        });

        // Brišemo tabelu location_types
        Schema::dropIfExists('location_types');
    }
}
