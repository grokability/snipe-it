<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Kreiramo tabelu location_statuses ako ne postoji
        if (!Schema::hasTable('location_statuses')) {
            Schema::create('location_statuses', function (Blueprint $table) {
                $table->id(); // Auto-increment ID
                $table->string('name'); // Kolona za ime statusa
                $table->timestamps(); // Automatski created_at i updated_at
            });

            // Dodajemo početne statuse u location_statuses tabelu
            DB::table('location_statuses')->insert([
                ['name' => 'Aktivna'],
                ['name' => 'Zatvorena'],
                ['name' => 'Privremeno zatvorena'],
                ['name' => 'Obrisana'],
            ]);
        }

        // Dodajemo kolonu status_id u tabelu locations (ako nije već postojala)
        Schema::table('locations', function (Blueprint $table) {
            if (!Schema::hasColumn('locations', 'status_id')) {
                $table->unsignedBigInteger('status_id')->nullable()->default(1); // Podrazumevani status: 'Aktivna'
                $table->foreign('status_id')->references('id')->on('location_statuses')->onDelete('set null');
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
        // Uklanjamo strani ključ i kolonu status_id iz locations tabele
        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->dropColumn('status_id');
        });

        // Brišemo tabelu location_statuses
        Schema::dropIfExists('location_statuses');
    }
}

