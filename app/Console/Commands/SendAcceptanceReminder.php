<?php

namespace App\Console\Commands;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\LicenseSeat;
use App\Notifications\UnacceptedAssetReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Exception\TransportException;

class SendAcceptanceReminder extends Command
{
    protected $signature = 'snipeit:acceptance-reminder';

    protected $description = 'This will resend users with unaccepted items a reminder to accept or decline them.';

    public function handle()
    {
        $result = self::dispatchAll();

        $this->info($result['sent'].' users notified.');
        if ($result['failed'] > 0) {
            $this->warn($result['failed'].' reminder(s) failed to send due to mail transport errors. See log for details.');
        }

        if (! empty($result['no_email'])) {
            $this->info('The following users do not have an email address:');
            $this->table(['ID', 'Name'], array_map(fn ($u) => [$u['id'], $u['name']], $result['no_email']));
        }

        return 0;
    }

    /**
     * Nudge every assignee who still has a pending acceptance.
     * Shared between the artisan command and the API's send-all
     * path so both land in one implementation. Returns a tally so
     * the API can surface the sent count in its JSON response.
     *
     * @return array{sent: int, failed: int, no_email: array<int, array{id: ?int, name: ?string}>}
     */
    public static function dispatchAll(): array
    {
        $pending = CheckoutAcceptance::query()
            ->with([
                'checkoutable' => function (MorphTo $morph) {
                    $morph->morphWith([
                        Asset::class => ['model.category', 'assignedTo', 'adminuser', 'company', 'checkouts'],
                        Accessory::class => ['category', 'company', 'checkouts'],
                        LicenseSeat::class => ['user', 'license', 'checkouts'],
                        Component::class => ['assignedTo', 'company', 'checkouts'],
                        Consumable::class => ['company', 'checkouts'],
                    ]);
                },
                'assignedTo',
            ])
            ->whereHasMorph(
                'checkoutable',
                [Asset::class, Accessory::class, LicenseSeat::class, Component::class, Consumable::class],
                fn ($q) => $q->whereNull('accepted_at')
                    ->whereNull('declined_at')
            )
            ->pending()
            ->get();

        $byAssignee = $pending
            ->map(fn ($acceptance) => ['assetItem' => $acceptance->checkoutable, 'acceptance' => $acceptance])
            ->groupBy(fn ($item) => $item['acceptance']->assignedTo ? $item['acceptance']->assignedTo->id : '');

        $sent = 0;
        $failed = 0;
        $noEmail = [];

        foreach ($byAssignee as $group) {
            // The [0] grab here is intentional. groupBy keeps the
            // group as a Collection, and we need both one
            // representative acceptance (header for the mail) and the
            // group's size (count for the mail). flattening either
            // way drops one of the two.
            $acceptance = $group[0]['acceptance'];
            $user = $acceptance->assignedTo;

            if (! $user?->email) {
                $noEmail[] = [
                    'id' => $user?->id,
                    'name' => $user?->display_name,
                ];

                continue;
            }

            try {
                $user->notify(new UnacceptedAssetReminderNotification($acceptance, $group->count()));
                $sent++;
            } catch (TransportException $e) {
                $failed++;
                Log::warning('Failed to send acceptance reminder to '.$user->email.': '.$e->getMessage());
            }
        }

        return [
            'sent' => $sent,
            'failed' => $failed,
            'no_email' => $noEmail,
        ];
    }
}
