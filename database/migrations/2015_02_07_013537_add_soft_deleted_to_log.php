<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSoftDeletedToLog extends Migration
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

        Schema::table('asset_logs', function ($table) {
            $table->string('asset_type', 100)->nullable()->change();
        });

        // DB::statement('ALTER TABLE ' . DB::getTablePrefix() . 'asset_logs MODIFY column asset_type varchar(100) null');
        // DB::statement('ALTER TABLE ' . DB::getTablePrefix() . 'asset_logs MODIFY column added_on timestamp default "0000-00-00 00:00:00"');
        // Schema::table('asset_logs', function ($table) {
        // 	$table->renameColumn('added_on', 'created_at');
        // 	$table->timestamp('updated_at');
    //         $table->softDeletes();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('asset_logs', function ($table) {
        // 	$table->renameColumn('created_at', 'added_on');
        // 	$table->dropColumn('updated_at');
        // 	$table->dropSoftDeletes();
        // });
    }
}
