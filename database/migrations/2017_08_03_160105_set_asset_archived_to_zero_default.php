<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetAssetArchivedToZeroDefault extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //$platform = Schema::getConnection()->getDoctrineSchemaManager()->getDatabasePlatform();
        //$platform->registerDoctrineTypeMapping('enum', 'string');

        Schema::table('assets', function (Blueprint $table) {
            $table->boolean('archived')->default(0)->nullable()->change();
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
