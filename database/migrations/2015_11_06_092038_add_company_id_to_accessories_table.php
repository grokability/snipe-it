<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddCompanyIdToAccessoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accessories', function (Blueprint $table) {
            $table->integer('company_id')->unsigned()->nullable();
            //$table->foreign('company_id')->references('id')->on('companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accessories', function (Blueprint $table) {
            //$table->dropForeign('accessories_company_id_foreign');
            $table->dropColumn('company_id');
        });
    }
}
