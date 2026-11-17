<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add `consumed_at` to the consumables_users pivot. Null means the
     * recipient has not yet reported the issued unit as used up, so the
     * row still counts against the parent consumable's "outstanding"
     * total for archive and delete gating. Existing rows backfill as
     * null, which treats every historical distribution as outstanding
     * until an admin marks it consumed.
     */
    public function up(): void
    {
        Schema::table('consumables_users', function (Blueprint $table) {
            $table->timestamp('consumed_at')->nullable()->default(null)->after('note')->index();
        });
    }

    public function down(): void
    {
        Schema::table('consumables_users', function (Blueprint $table) {
            $table->dropColumn('consumed_at');
        });
    }
};
