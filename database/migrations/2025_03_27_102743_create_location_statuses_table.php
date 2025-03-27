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
        Schema::create('location_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Dodaj kolonu za ime statusa
            $table->timestamps();
        });

        // Popunjavanje tabele po�etnim statusima
        DB::table('location_statuses')->insert([
            ['name' => 'Aktivna'],
            ['name' => 'Zatvorena'],
            ['name' => 'Privremeno zatvorena'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('location_statuses');
    }
}
