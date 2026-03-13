<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatedByToPermissionGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permission_groups', function (Blueprint $table) {
            $table->integer('created_by')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permission_groups', function (Blueprint $table) {
            if (Schema::hasColumn('permission_groups', 'created_by')) {
                $table->dropColumn('created_by');
            }
        });
    }
}
