<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // We check to see if this table exists before attempting the migration since
        // upgraded installs would have this table, but new installs wouldn't.
        // We had to change the name of the table in the older migrations
        // to handle a MySQl 8+ compatibility issue related to reserved words.
        // Without going back in time in migrations, this would fail since the groups table
        // would never be allowed to be created in the first place on MySql 8+.
        //
        // So... if an upgrade, let's rename that table.
        // If a new install, the migration was already changed, so the table isn't
        // called that anymore and we can skip this migration.

        if (Schema::hasTable('groups')) {
            Schema::rename('groups', 'permission_groups');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('permission_groups')) {
            Schema::rename('permission_groups', 'groups');
        }
    }
}
