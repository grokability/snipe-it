<?php

use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeAssetAssignedToPolymorphic extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('assigned_type')->nullable();
        });
        if (config('database.default') == 'mysql') {
            Asset::whereNotNull('assigned_to')->orWhere('assigned_to', '!=', '')->update(['assigned_type' => User::class]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return voidatom
     */
    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('assigned_type');
        });
    }
}
