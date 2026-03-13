<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddAccessoryConsumablePriceInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accessories', function ($table) {
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 13, 4)->nullable();
            $table->string('order_number')->nullable();
        });

        Schema::table('consumables', function ($table) {
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 13, 4)->nullable();
            $table->string('order_number')->nullable();
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
            $table->dropColumn('purchase_date');
            $table->dropColumn('purchase_cost');
            $table->dropColumn('order_number');
        });

        Schema::table('consumables', function ($table) {
            $table->dropColumn('purchase_date');
            $table->dropColumn('purchase_cost');
            $table->dropColumn('order_number');
        });
    }
}
