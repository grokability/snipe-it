<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEulaToCheckoutAcceptance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('checkout_acceptances', 'stored_eula')) {
            Schema::table('checkout_acceptances', function (Blueprint $table) {
                $table->text('stored_eula')->nullable()->default(null);
                $table->string('stored_eula_file')->nullable()->default(null);
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
        if (Schema::hasColumn('checkout_acceptances', 'stored_eula')) {
            Schema::table('checkout_acceptances', function (Blueprint $table) {
                if (Schema::hasColumn('checkout_acceptances', 'stored_eula')) {
                    $table->dropColumn('stored_eula');
                }
                if (Schema::hasColumn('checkout_acceptances', 'stored_eula_file')) {
                    $table->dropColumn('stored_eula_file');
                }
            });
        }
    }
}