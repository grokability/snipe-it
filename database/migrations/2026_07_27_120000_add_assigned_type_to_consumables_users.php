<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddAssignedTypeToConsumablesUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('consumables_users', function (Blueprint $table) {
            // Default to User so every existing + untouched write path
            // (kit checkout, importer, factory, seeder) keeps producing
            // user-typed rows with no code change.
            $table->string('assigned_type')->nullable()->default(\App\Models\User::class)->after('assigned_to');
        });

        // Backfill existing rows — all current checkouts are to users.
        DB::table('consumables_users')->whereNull('assigned_type')->update([
            'assigned_type' => \App\Models\User::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('consumables_users', function (Blueprint $table) {
            if (Schema::hasColumn('consumables_users', 'assigned_type')) {
                $table->dropColumn('assigned_type');
            }
        });
    }
}
