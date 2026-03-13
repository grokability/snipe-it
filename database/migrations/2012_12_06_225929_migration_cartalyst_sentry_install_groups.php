<?php
/**
 * Part of the Sentry package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @version    2.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Illuminate\Database\Migrations\Migration;

class MigrationCartalystSentryInstallGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permission_groups', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->text('permissions')->nullable();
            $table->timestamps();
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // See 2014_11_04_231416_update_group_field_for_reporting.php and 2019_06_12_184327_rename_groups_table.php
        Schema::dropIfExists('permission_groups');
        Schema::dropIfExists('groups');
    }
}
