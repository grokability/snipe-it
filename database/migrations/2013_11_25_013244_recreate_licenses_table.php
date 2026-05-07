<?php

use Illuminate\Database\Migrations\Migration;

class ReCreateLicensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('licenses')) {
            Schema::create('licenses', function ($table) {
                $table->increments('id');
                $table->string('name');
                $table->string('serial');
                $table->date('purchase_date')->nullable();
                $table->decimal('purchase_cost', 8, 2)->nullable();
                $table->string('order_number');
                $table->integer('seats');
                $table->text('notes');
                $table->integer('user_id')->nullable();
                $table->integer('depreciation_id');
                $table->timestamps();
                $table->softDeletes();
                $table->engine = 'InnoDB';
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // This was most likely handled in 2013_11_17_054359_drop_licenses_table.php
        Schema::dropIfExists('licenses');
    }
}
