<?php

use App\Models\Group;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class UpdateGroupFieldForReporting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // This is janky because we had to do a back in time change to handle a MySQL 8+
        // compatibility issue.

        // Ideally we'd be using the model here, but since we can't really know whether this is an upgrade
        // or a fresh install, we have to check which table is being used.

        if (Schema::hasTable('permission_groups')) {
            Group::where('id', 1)->update(['permissions' => '{"users-foo":1,"reports":1}']);
            Group::where('id', 2)->update(['permissions' => '{"users-foo":1,"reports":1}']);
        } elseif (Schema::hasTable('groups')) {
            DB::update('update '.DB::getTablePrefix().'groups set permissions = ? where id = ?', ['{"admin-foo":1,"users":1,"reports":1}', 1]);
            DB::update('update '.DB::getTablePrefix().'groups set permissions = ? where id = ?', ['{"users-foo":1,"reports":1}', 2]);
        }
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
