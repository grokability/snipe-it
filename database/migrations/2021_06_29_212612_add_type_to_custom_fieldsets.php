<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\CustomFieldset;
use App\Models\Asset;

class AddTypeToCustomFieldsets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('custom_fieldsets', function (Blueprint $table) {
            //
            $table->text('type')->default('App\\Models\\Asset');
        });
        CustomFieldset::query()->update(['type' => Asset::class]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('custom_fieldsets', function (Blueprint $table) {
            //
            $table->dropColumn('type');
        });
    }
}
