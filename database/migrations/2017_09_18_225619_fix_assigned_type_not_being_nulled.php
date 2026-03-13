<?php

use App\Models\Asset;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixAssignedTypeNotBeingNulled extends Migration
{
    /**
     * Run the migrations.
     * There was a point in the v4 beta process where assigned_type was not nulled on checkin
     * This manually nulls all assets where there is an assigned_type but not an assigned_to.
     * @return void
     */
    public function up()
    {
        // There was a point in the v4 beta process where assigned_type was not nulled on checkin
        // This manually nulls all assets where there is an assigned_type but not an assigned_to.
        Asset::whereNotNull('assigned_type')->whereNull('assigned_to')->update(['assigned_type' => null]);

        // Additionally, the importer did not set assigned_type when importing.
        // In the case where we have an assigned_to but not an assigned_type, set the assigned_type to User.
        Asset::whereNotNull('assigned_to')->whereNull('assigned_type')->update(['assigned_type' => \App\Models\User::class]);
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
