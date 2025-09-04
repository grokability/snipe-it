<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\CustomField;
use App\Models\Asset;

class AddTypeToCustomFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            //
            $table->text('type')->default('App\\Models\\Asset'); // TODO this default is needed for a not-nullable column, I guess? I don't like this because it will silently 'fix' errors we should properly 'fix'
        });
        CustomField::query()->update(['type' => Asset::class]); // TODO - is this still necessary with that 'default'?
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            //
            $table->dropColumn('type');
        });
    }
}
