<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLabel2Settings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('label2_enable')->default(false);
            $table->string('label2_template')->nullable()->default('DefaultLabel');
            $table->string('label2_title')->nullable()->default(null);
            $table->boolean('label2_asset_logo')->default(false);
            $table->string('label2_1d_type')->default('default');
            $table->string('label2_2d_type')->default('default');
            $table->string('label2_2d_target')->default('hardware_id');
            $table->string('label2_fields')->default('name=name;serial=serial;model=model.name;');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'label2_enable'))     $table->dropColumn('label2_enable');
            if (Schema::hasColumn('settings', 'label2_template'))   $table->dropColumn('label2_template');
            if (Schema::hasColumn('settings', 'label2_title'))      $table->dropColumn('label2_title');
            if (Schema::hasColumn('settings', 'label2_asset_logo')) $table->dropColumn('label2_asset_logo');
            if (Schema::hasColumn('settings', 'label2_1d_type'))    $table->dropColumn('label2_1d_type');
            if (Schema::hasColumn('settings', 'label2_2d_type'))    $table->dropColumn('label2_2d_type');
            if (Schema::hasColumn('settings', 'label2_2d_target'))  $table->dropColumn('label2_2d_target');
            if (Schema::hasColumn('settings', 'label2_fields'))     $table->dropColumn('label2_fields');
        });
    }
}
