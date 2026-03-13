<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNotesDenormToConsumablesUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('consumables_users', function (Blueprint $table) {
            $table->text('note')->nullable()->default(null);
        });

        Schema::table('components_assets', function (Blueprint $table) {
            $table->text('note')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('consumables_users', function (Blueprint $table) {
            if (Schema::hasColumn('consumables_users', 'note')) {
                $table->dropColumn('note');
            }
        });

        Schema::table('components_assets', function (Blueprint $table) {
            if (Schema::hasColumn('components_assets', 'note')) {
                $table->dropColumn('note');
            }
        });
    }
}
