<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCanceledAtAndFulfilledAtInRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('checkout_requests', function (Blueprint $table) {
            $table->dateTime('canceled_at')->nullable()->default(null);
            $table->dateTime('fulfilled_at')->nullable()->default(null);
            $table->dateTime('deleted_at')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('checkout_requests', function (Blueprint $table) {
            $table->dropColumn('canceled_at');
            $table->dropColumn('fulfilled_at');
            $table->dropColumn('deleted_at');
        });
    }
}
