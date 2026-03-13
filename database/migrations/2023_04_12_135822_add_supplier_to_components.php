<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSupplierToComponents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('components', function (Blueprint $table) {
            if (!Schema::hasColumn('components', 'supplier_id')) {
                $table->integer('supplier_id')->after('user_id')->nullable()->default(null);
            }
        });

        Schema::table('consumables', function (Blueprint $table) {
            if (!Schema::hasColumn('consumables', 'supplier_id')) {
                $table->integer('supplier_id')->after('user_id')->nullable()->default(null);
            }
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
            if (Schema::hasColumn('components', 'supplier_id')) {
                $table->dropColumn('supplier_id');
            }
        });

        Schema::table('consumables', function (Blueprint $table) {
            if (Schema::hasColumn('consumables', 'supplier_id')) {
                $table->dropColumn('supplier_id');
            }
        });
    }
}
