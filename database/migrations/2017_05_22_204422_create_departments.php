<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('department_id')->nullable()->default(null);
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('user_id');
            $table->integer('company_id')->nullable()->default(null);
            $table->integer('location_id')->nullable()->default(null);
            $table->integer('manager_id')->nullable()->default(null);
            $table->string('notes')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function ($table) {
            $table->dropColumn('department_id');
        });

        Schema::dropIfExists('departments');
    }
}
