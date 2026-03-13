<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;


class AddKitsLicensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		if (!Schema::hasTable('kits_licenses')) {
            Schema::create('kits_licenses', function ($table) {
                $table->increments('id');
				$table->integer('kit_id')->nullable()->default(NULL); 
				$table->integer('license_id')->nullable()->default(NULL);    
                $table->integer('quantity')->default(1);
                $table->timestamps();
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
		if (Schema::hasTable('kits_licenses')) {
           Schema::drop('kits_licenses');
        }
	}

}
