<?php

use App\Models\Actionlog;
use App\Models\Asset;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReNormalizeLastAudit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('assets', 'last_audit_date')) {

            // Grab the latest info from the Actionlog table where the action is 'audit'
            $audits = Actionlog::selectRaw('MAX(created_at) AS created_at, item_id')
                ->where('action_type', 'audit')
                ->where('item_type', Asset::class)
                ->groupBy('item_id')
                ->orderBy('created_at', 'desc')
                ->get();

            if ($audits) {
                foreach ($audits as $audit) {
                    $asset = Asset::where('id', $audit->item_id)->withTrashed()->first();
                    if ($asset) {
                        $asset->last_audit_date = $audit->created_at;
                        $asset->unsetEventDispatcher();
                        $asset->save();
                    }
                }
            }
        }
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
