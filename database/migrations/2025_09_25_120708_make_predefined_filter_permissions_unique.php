<?php

use App\Models\PredefinedFilterPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakePredefinedFilterPermissionsUnique extends Migration
{
    public function up(): void
    {
        // Step 1: Remove duplicates using Eloquent
        $duplicates = PredefinedFilterPermission::select('predefined_filter_id', 'permission_group_id')
            ->groupBy('predefined_filter_id', 'permission_group_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $records = PredefinedFilterPermission::where('predefined_filter_id', $duplicate->predefined_filter_id)
                ->where('permission_group_id', $duplicate->permission_group_id)
                ->orderBy('id') // Keep the oldest one
                ->get();

            // Keep the first one, delete the rest
            $records->slice(1)->each->delete();
        }

        // Step 2: Add unique constraint
        Schema::table('predefined_filter_permissions', function (Blueprint $table) {
            $table->unique(
                ['predefined_filter_id', 'permission_group_id'],
                'unique_predefined_filter_permission'
            );
        });
    }

    public function down(): void
    {
        Schema::table('predefined_filter_permissions', function (Blueprint $table) {
            $table->dropForeign('predefined_filter_permissions_predefined_filter_id_foreign');
            $table->dropUnique('unique_predefined_filter_permission');
        });
    }
}
;
