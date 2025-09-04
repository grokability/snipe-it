<?php

use App\Models\Asset;
use App\Models\DefaultValuesForCustomFields;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeColumnToDefaultValuesForCustomFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('default_values_for_custom_fields', function (Blueprint $table) {
            $table->renameColumn('asset_model_id','item_pivot_id'); //this one works. okay. that's someting.
        });
        Schema::table('default_values_for_custom_fields', function (Blueprint $table) {
            $table->string('type')->nullable();
        });
        DefaultValuesForCustomFields::query()->update(['type' => Asset::class]);
        Schema::table('default_values_for_custom_fields', function (Blueprint $table) {
            //$table->renameColumn('asset_model_id','item_pivot_id'); //this one works. okay. that's someting.
            $table->string('type')->nullable(false)->change();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('default_values_for_custom_fields', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->renameColumn('item_pivot_id','asset_model_id');
        });
    }
}
