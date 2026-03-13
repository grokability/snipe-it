<?php

use App\Models\Actionlog;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class MigrateAssetLogToActionLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        $logs = DB::table('asset_logs')->get();

        foreach ($logs as $log) {
            // protected $fillable   = [ 'created_at', 'asset_type','user_id','asset_id','action_type','note','checkedout_to' ];
            $a = new Actionlog(compact($log));
            // var_dump($log);
            $a->user_id = $log->user_id;

            if (! is_null($log->asset_id)) {
                $a->item_id = $log->asset_id;
                if ($log->asset_type == 'hardware') {
                    $a->item_type = \App\Models\Asset::class;
                } else {
                    $a->item_type = \App\Models\License::class;
                }
            }
            if (! is_null($log->accessory_id)) {
                $a->item_id = $log->accessory_id;
                $a->item_type = \App\Models\Accessory::class;
            } elseif (! is_null($log->consumable_id)) {
                $a->item_id = $log->consumable_id;
                $a->item_type = \App\Models\Consumable::class;
            } elseif (! is_null($log->component_id)) {
                $a->item_id = $log->component_id;
                $a->item_type = \App\Models\Component::class;
            }
            $a->action_type = $log->action_type;
            // $a->checkout_to = $log->checkout_to;
            if (! is_null($log->checkedout_to)) {
                $a->target_id = $log->checkedout_to;
                $a->target_type = User::class;
            }
            if (! is_null($log->accepted_id)) {
                $a->target_id = $log->accepted_id;
                $a->target_type = User::class;
            }
            $a->location_id = $log->location_id;
            $a->created_at = $log->created_at;
            $a->updated_at = $log->updated_at;
            $a->deleted_at = $log->deleted_at;
            $a->note = $log->note;
            $a->expected_checkin = $log->expected_checkin;
            $a->accepted_id = $log->accepted_id;
            $a->filename = $log->filename;

            $a->save();
        }
        // dd($logs);
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
