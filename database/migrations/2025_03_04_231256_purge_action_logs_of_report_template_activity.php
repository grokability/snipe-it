<?php

use App\Models\ReportTemplate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table("action_logs")
            ->where("item_type", ReportTemplate::class)
            ->whereIn("action_type", ["create", "update", "delete"])
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
