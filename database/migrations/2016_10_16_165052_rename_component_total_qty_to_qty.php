<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RenameComponentTotalQtyToQty extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('components', function (Blueprint $table) {
            //
            $table->renameColumn('total_qty', 'qty');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('components', function (Blueprint $table) {
            //
            $table->renameColumn('qty', 'total_qty');
        });
    }
}
