<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Statuslabel;

class ChangeDefaultLabelToNullable extends Migration
{
    /**
     * Run the migrations.
     * 
     * This is stupid because it has a default valuye of 0 so it *should* 
     * default to 0, but it doesn't on some versions of MySQL. 
     *
     * @return void
     */
    public function up()
    {


        Statuslabel::whereNull('default_label')
        ->update(['default_label' => 0]);

        Statuslabel::whereNull('show_in_nav')
        ->update(['show_in_nav' => 0]);


        Schema::table('status_labels', function (Blueprint $table) {
            $table->boolean('default_label')->nullable()->default(0)->change();
            $table->boolean('show_in_nav')->nullable()->default(0)->change();
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
