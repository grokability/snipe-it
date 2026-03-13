<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHeaderAndFirstRowToImporterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('imports', function (Blueprint $table) {
            // Add a json string representing the header row of the csv, and the first row of the csv.
            $table->text('header_row')->nullable()->default(null);
            $table->text('first_row')->nullable()->default(null);
            $table->text('field_map')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('imports', function (Blueprint $table) {
            //
            $table->dropColumn('header_row');
            $table->dropColumn('first_row');
            $table->dropColumn('field_map');
        });
    }
}
