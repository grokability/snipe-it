<?php

use App\Models\Consumable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('action_logs')
            ->where([
                'action_type' => 'checkin from',
                'note' => 'Bulk checkin items',
                'item_type' => Consumable::class,
            ])
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // nothing to do here...
    }
};
