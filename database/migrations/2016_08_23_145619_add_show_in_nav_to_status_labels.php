<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddShowInNavToStatusLabels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('status_labels', function (Blueprint $table) {
            $table->boolean('show_in_nav')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('status_labels', function (Blueprint $table) {
            $table->dropColumn('show_in_nav');
        });
    }
}
