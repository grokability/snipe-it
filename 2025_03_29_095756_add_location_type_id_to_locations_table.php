<?php

// database/migrations/xxxx_xx_xx_add_location_type_id_to_locations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationTypeIdToLocationsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('locations', 'location_type_id')) {
            Schema::table('locations', function (Blueprint $table) {
                $table->unsignedBigInteger('type_id');
                $table->foreignId('type_id')->constrained('location_types')->onDelete('set null')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign(['location_type_id']);
            $table->dropColumn('location_type_id');
        });
    }
}
;
