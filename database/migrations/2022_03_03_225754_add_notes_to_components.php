<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNotesToComponents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('components', function (Blueprint $table) {
            $table->text("notes")->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('components', function (Blueprint $table) {
            if (Schema::hasColumn('components', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
}
