<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class MakePurchaseCostNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //$platform = Schema::getConnection()->getDoctrineSchemaManager()->getDatabasePlatform();
        //$platform->registerDoctrineTypeMapping('enum', 'string');

        Schema::table('assets', function ($table) {
            $table->decimal('purchase_cost', 8, 2)->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
