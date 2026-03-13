<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSignatureToAcceptance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function ($table) {
            $table->boolean('require_accept_signature')->default(0);
        });

        Schema::table('action_logs', function ($table) {
            $table->string('accept_signature', 100)->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function ($table) {
            $table->dropColumn('require_accept_signature');
        });

        Schema::table('action_logs', function ($table) {
            $table->dropColumn('accept_signature');
        });
    }
}
