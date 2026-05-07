<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class Alpha042Release extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $prefix = DB::getTablePrefix();
        Schema::table('assets', function (Blueprint $table) {
            //
        });

        // DB::statement('UPDATE '.$prefix.'assets SET status_id="0" where status_id is null');
        // DB::statement('UPDATE '.$prefix.'assets SET purchase_cost=0 where purchase_cost is null');
        // DB::statement('UPDATE '.$prefix.'models SET eol=0 where eol is null');
        // DB::statement('UPDATE '.$prefix.'users SET location_id=0 where location_id is null');
        // DB::statement('UPDATE '.$prefix.'assets SET asset_tag=" " WHERE asset_tag is null');
        // DB::statement('UPDATE '.$prefix.'locations SET state=" " where state is null');
        // DB::statement('UPDATE '.$prefix.'models SET manufacturer_id="0" where manufacturer_id is null');
        // DB::statement('UPDATE '.$prefix.'models SET category_id="0" where category_id is null');

        // DB::statement('ALTER TABLE '.$prefix.'assets '
        //     . 'MODIFY COLUMN name VARCHAR(255) NULL , '
        //     . 'MODIFY COLUMN asset_tag VARCHAR(255) NOT NULL , '
        //     . 'MODIFY COLUMN purchase_cost DECIMAL(13,4) NOT NULL DEFAULT "0" , '
        //     . 'MODIFY COLUMN order_number VARCHAR(255) NULL  , '
        //     . 'MODIFY COLUMN assigned_to INT(11) NULL , '
        //     . 'MODIFY COLUMN notes TEXT NULL , '
        //     . 'MODIFY COLUMN archived TINYINT(1) NOT NULL DEFAULT "0" , '
        //     . 'MODIFY COLUMN depreciate TINYINT(1) NOT NULL DEFAULT "0"');
        //
        // DB::statement('ALTER TABLE '.$prefix.'licenses '
        //     . 'MODIFY COLUMN purchase_cost DECIMAL(13,4) NULL , '
        //     . 'MODIFY COLUMN depreciate TINYINT(1) NULL DEFAULT "0"');
        //
        // DB::statement('ALTER TABLE '.$prefix.'license_seats '
        //     . 'MODIFY COLUMN assigned_to INT(11) NULL ');
        //
        // DB::statement('ALTER TABLE '.$prefix.'locations '
        //     . 'MODIFY COLUMN state VARCHAR(255) NOT NULL ,'
        //     . 'MODIFY COLUMN address2 VARCHAR(255) NULL ,'
        //     . 'MODIFY COLUMN zip VARCHAR(10) NULL ');
        //
        // DB::statement('ALTER TABLE '.$prefix.'models '
        //     . 'MODIFY COLUMN modelno VARCHAR(255) NULL , '
        //     . 'MODIFY COLUMN manufacturer_id INT(11) NOT NULL , '
        //     . 'MODIFY COLUMN category_id INT(11) NOT NULL , '
        //     . 'MODIFY COLUMN depreciation_id INT(11) NOT NULL DEFAULT "0" , '
        //     . 'MODIFY COLUMN eol INT(11) NULL DEFAULT "0"');
        //
        // DB::statement('ALTER TABLE '.$prefix.'users '
        //     . 'MODIFY COLUMN first_name VARCHAR(255) NOT NULL , '
        //     . 'MODIFY COLUMN last_name VARCHAR(255) NOT NULL , '
        //     . 'MODIFY COLUMN location_id INT(11) NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            //
        });
    }
}
