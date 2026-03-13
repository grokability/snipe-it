<?php

use App\Models\Accessory;
use App\Models\Actionlog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MoveAccessoryCheckoutNoteToJoinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasColumn('accessories_users', 'note'))
        {
            Schema::table('accessories_users', function (Blueprint $table) {
                $table->string('note')->nullable(true)->default(null);
            });
        }


        

        // Loop through the checked out accessories, find their related action_log entry, and copy over the note
        // to the newly created note field

        $accessories = Accessory::get();
        $count = 0;
        \Log::debug('Accessory Count:  '.$accessories->count());

        // Loop through all of the accessories
        foreach ($accessories as $accessory) {
            $count++;

            \Log::debug('Querying join logs');
            $join_logs = DB::table('accessories_users')->get();

            // Loop through the accessories_users records
            foreach ($join_logs as $join_log) {
                \Log::debug($join_logs->count().' join log records');
                \Log::debug('Looking for accessories_users that match '.$join_log->created_at);

                // Get the records from action_logs so we can copy the notes over to the new notes field
                // on the accessories_users table
                $action_log_entries = Actionlog::where('created_at', '=', $join_log->created_at)
                    ->where('target_id', '=', $join_log->assigned_to)
                    ->where('item_id', '=', $accessory->id)
                    ->where('target_type', '=', \App\Models\User::class)
                    ->where('item_type', '=','App\\Models\\Accessory')
                    ->where('action_type', '=', 'checkout')
                    ->where('note', '!=', '')
                    ->orderBy('created_at', 'DESC')->get();

                \Log::debug($action_log_entries->count().' matching entries in the action_logs table');
                \Log::debug('Looking for action_logs that match '.$join_log->created_at);

                foreach ($action_log_entries as $action_log_entry) {
                    \Log::debug('Checkout date in asset log: '.$action_log_entry->created_at.' against accessories_users: '.$join_log->created_at);
                    \Log::debug('Action log: '.$action_log_entry->created_at);
                    \Log::debug('Join log: '.$join_log->created_at);

                    if ($action_log_entry->created_at == $join_log->created_at) {
                        DB::table('accessories_users')
                            ->where('id', $join_log->id)
                            ->update(['note' => $action_log_entry->note]);
                    } else {
                        \Log::debug('No match');
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

        if (Schema::hasColumn('accessories_users', 'note'))
        {
            Schema::table('accessories_users', function (Blueprint $table)
            {
                $table->dropColumn('note');
            });
        }

    
    }
}
