<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class IncreasePurchaseCostSize extends Migration
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
            $table->decimal('purchase_cost', 20, 2)->nullable()->default(null)->change();
        });

        Schema::table('accessories', function ($table) {
            $table->decimal('purchase_cost', 20, 2)->nullable()->default(null)->change();
        });

        Schema::table('asset_maintenances', function ($table) {
            $table->decimal('cost', 20, 2)->nullable()->default(null)->change();
        });

        Schema::table('components', function ($table) {
            $table->decimal('purchase_cost', 20, 2)->nullable()->default(null)->change();
        });

        Schema::table('consumables', function ($table) {
            $table->decimal('purchase_cost', 20, 2)->nullable()->default(null)->change();
        });

        Schema::table('licenses', function ($table) {
            $table->decimal('purchase_cost', 20, 2)->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //$platform = Schema::getConnection()->getDoctrineSchemaManager()->getDatabasePlatform();
        //$platform->registerDoctrineTypeMapping('enum', 'string');

        Schema::table('assets', function ($table) {
            $table->decimal('purchase_cost', 8, 2)->nullable()->default(null)->change();
        });

        Schema::table('accessories', function ($table) {
            $table->decimal('purchase_cost', 13, 4)->nullable()->default(null)->change();
        });

        Schema::table('asset_maintenances', function ($table) {
            $table->decimal('cost', 10, 2)->nullable()->default(null)->change();
        });

        Schema::table('components', function ($table) {
            $table->decimal('purchase_cost', 13, 4)->nullable()->default(null)->change();
        });

        Schema::table('consumables', function ($table) {
            $table->decimal('purchase_cost', 13, 4)->nullable()->default(null)->change();
        });

        Schema::table('licenses', function ($table) {
            $table->decimal('purchase_cost', 8, 2)->nullable()->default(null)->change();
        });
    }
}
