<?php

use App\Rules\ExternalUrl;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

return new class extends Migration
{
    public function up(): void
    {
        // The webhook endpoint used to accept ftp:// and irc:// schemes and did
        // no host validation at all, so upgrading instances may already have a
        // value pointing at loopback, RFC-1918, or a metadata service. The new
        // ExternalUrl rule blocks new saves in that shape, but the runtime
        // notification senders read the stored value directly on every checkout
        // / checkin, so a bad value sitting in the DB stays exploitable until
        // it's cleared. This migration walks the settings rows once and blanks
        // anything the rule rejects, logging the previous value so an operator
        // who was intentionally pointing at an internal receiver can see what
        // was removed and put it back through a reverse proxy if they want.
        $rows = DB::table('settings')
            ->whereNotNull('webhook_endpoint')
            ->where('webhook_endpoint', '!=', '')
            ->get(['id', 'webhook_endpoint']);

        foreach ($rows as $row) {
            $fails = Validator::make(
                ['webhook_endpoint' => $row->webhook_endpoint],
                ['webhook_endpoint' => [new ExternalUrl]],
            )->fails();

            if (! $fails) {
                continue;
            }

            Log::warning('Clearing unsafe webhook_endpoint during migration', [
                'settings_id' => $row->id,
                'previous_endpoint' => $row->webhook_endpoint,
            ]);

            DB::table('settings')
                ->where('id', $row->id)
                ->update(['webhook_endpoint' => null]);
        }
    }

    public function down(): void
    {
        // No-op: cleared endpoints are logged during up(); we can't restore them.
    }
};
