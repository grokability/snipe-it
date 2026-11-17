<?php

namespace App\Notifications;

use App\Models\CheckoutAcceptance;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Nudges a user about pending acceptances still on their plate. One
 * notification per assignee with the total count, not one per
 * acceptance. Replaced the standalone UnacceptedAssetReminderMail so
 * every reminder flow (snipeit:acceptance-reminder console command,
 * the per-user Resend-reminder button on the user show page, the
 * /unaccepted-acceptances bulk UI, and the API send-reminders
 * endpoint) ships through the same Laravel notification channel.
 */
class UnacceptedAssetReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public CheckoutAcceptance $acceptance,
        public int $count,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        // Locale is picked up automatically from User::preferredLocale()
        // (HasLocalePreference contract), so this doesn't need to
        // branch on $notifiable->locale the way the old Mailable did.
        return (new MailMessage)
            ->subject(trans('mail.unaccepted_asset_reminder'))
            ->markdown('notifications.markdown.asset-reminder', [
                'count' => $this->count,
                'assigned_to' => $this->acceptance->assignedTo?->present()->fullName,
                'link' => route('account.accept'),
                'accept_url' => route('account.accept'),
            ]);
    }

    /**
     * Shared dispatch loop for a set of acceptance ids. Dedupes by
     * assignee (five selected acceptances for one user = one
     * notification with count=5), skips assignees without an email
     * or who've been deactivated, and surfaces a per-reason skip
     * tally so the API response can show the admin what happened.
     *
     * @param  array<int>  $acceptanceIds
     * @return array{sent: int, skipped: array<string, int>}
     */
    public static function dispatchForAcceptanceIds(array $acceptanceIds): array
    {
        $acceptancesByAssignee = CheckoutAcceptance::pending()
            ->whereIn('id', $acceptanceIds)
            ->with(['assignedTo' => fn ($q) => $q->withTrashed()])
            ->get()
            ->filter(fn (CheckoutAcceptance $a) => $a->assignedTo && $a->isAccessibleToCurrentUser())
            ->groupBy(fn (CheckoutAcceptance $a) => $a->assignedTo->id);

        $sent = 0;
        $skipped = [
            'no_email' => 0,
            'none_pending' => 0,
            // Any id in the request that didn't survive the pending
            // + FMCS filter above falls out as not_found. Tracked
            // here so the response can tell the caller how many of
            // their ids never reached the dispatch loop.
            'not_found_or_fmcs' => max(0, count($acceptanceIds) - $acceptancesByAssignee->sum(fn ($g) => $g->count())),
        ];

        foreach ($acceptancesByAssignee as $group) {
            /** @var CheckoutAcceptance $first */
            $first = $group->first();
            $assignee = $first->assignedTo;

            if (empty($assignee->email) || $assignee->activated == '0') {
                $skipped['no_email']++;

                continue;
            }

            $pending = $assignee->getAssignedItemsWithPendingAcceptance();
            if ($pending->isEmpty()) {
                $skipped['none_pending']++;

                continue;
            }

            $assignee->notify(new self($first, $pending->count()));
            $sent++;
        }

        return [
            'sent' => $sent,
            'skipped' => $skipped,
        ];
    }
}
