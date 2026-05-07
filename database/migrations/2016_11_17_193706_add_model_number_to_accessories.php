<?php

use Illuminate\Database\Migrations\Migration;

class AddModelNumberToAccessories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accessories', function ($table) {
            $table->string('model_number')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accessories', function ($table) {
            $table->dropColumn('model_number');
        });
    }
}
