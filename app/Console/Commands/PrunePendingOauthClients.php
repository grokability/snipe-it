<?php

namespace App\Console\Commands;

use App\Models\OAuthClient;
use Illuminate\Console\Command;

/**
 * Deletes pending/rejected OAuth client rows whose created_at is
 * older than --days. Prevents the pending-approval queue from
 * growing unbounded from every "oh that's not the app I meant to
 * install" false start and from rejected rows that get kept around
 * for audit but outlive their usefulness.
 *
 * Scheduled daily in Console/Kernel.php at 03:15 with --days=30.
 * Admins can run on-demand with a different --days to clear more /
 * fewer stale rows.
 *
 * Does NOT touch approved clients. Those stay forever until a
 * superuser revokes them via the standard Passport flow.
 */
class PrunePendingOauthClients extends Command
{
    protected $signature = 'snipeit:prune-pending-oauth-clients {--days=30 : Pending/rejected clients older than this many days are deleted}';

    protected $description = 'Delete pending or rejected OAuth clients older than the given age.';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        $deleted = OAuthClient::query()
            ->whereIn('status', [OAuthClient::STATUS_PENDING, OAuthClient::STATUS_REJECTED])
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->info('Pruned '.$deleted.' pending/rejected OAuth client(s) older than '.$days.' day(s).');

        return self::SUCCESS;
    }
}
