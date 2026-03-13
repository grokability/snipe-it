<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddKitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		if (!Schema::hasTable('kits')) {
            Schema::create('kits', function ($table) {
                $table->increments('id');
				$table->string('name')->nullable()->default(NULL);
                $table->timestamps();
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
		if (Schema::hasTable('kits')) {
           Schema::drop('kits');
        }

	}

}
