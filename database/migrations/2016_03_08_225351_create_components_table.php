<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('components', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable()->default(null);
            $table->integer('category_id')->nullable()->default(null);
            $table->integer('location_id')->nullable()->default(null);
            $table->integer('company_id')->nullable()->default(null);
            $table->integer('user_id')->nullable()->default(null);
            $table->integer('total_qty')->default(1);
            $table->integer('order_number')->nullable()->default(null);
            $table->date('purchase_date')->nullable()->default(null);
            $table->decimal('purchase_cost', 13, 4)->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
            $table->engine = 'InnoDB';
        });

        Schema::table('asset_logs', function ($table) {
            $table->integer('component_id')->nullable()->default(null);
        });

        Schema::create('components_assets', function ($table) {
            $table->increments('id');
            $table->integer('user_id')->nullable()->default(null);
            $table->integer('assigned_qty')->nullable()->default(1);
            $table->integer('component_id')->nullable()->default(null);
            $table->integer('asset_id')->nullable()->default(null);
            $table->timestamps();
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
        Schema::dropIfExists('components');
        Schema::dropIfExists('components_assets');

        Schema::table('asset_logs', function ($table) {
            $table->dropColumn('component_id');
        });
    }
}
