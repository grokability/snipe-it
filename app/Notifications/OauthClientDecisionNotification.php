<?php

namespace App\Notifications;

use App\Models\OAuthClient;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Peer-superuser audit notification: when one superuser approves or
 * rejects a pending OAuth client, every OTHER active superuser gets
 * an email so the action is visible across the admin team. Single
 * class covers both approve and reject via the $decision enum. The
 * acting superuser is excluded from the recipient list at the send
 * site in McpSettingsController.
 */
class OauthClientDecisionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public const DECISION_APPROVED = 'approved';

    public const DECISION_REJECTED = 'rejected';

    public function __construct(
        public readonly OAuthClient $client,
        public readonly User $actor,
        public readonly string $decision,
        public readonly ?string $reason = null,
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
        $isApproved = $this->decision === self::DECISION_APPROVED;
        $subjectKey = $isApproved
            ? 'admin/settings/mcp.notify_action_approved_subject'
            : 'admin/settings/mcp.notify_action_rejected_subject';
        $introKey = $isApproved
            ? 'admin/settings/mcp.notify_action_approved_intro'
            : 'admin/settings/mcp.notify_action_rejected_intro';

        $redirect = $this->client->primary_redirect_uri ?? '(unknown)';
        $actionUrl = route('settings.oauth.index').'#pending-oauth-clients';

        $message = (new MailMessage)
            ->subject(trans($subjectKey))
            ->greeting(trans('admin/settings/mcp.notify_action_greeting'))
            ->line(trans($introKey, ['actor' => $this->actor->display_name ?: $this->actor->username]))
            ->line(trans('admin/settings/mcp.notify_action_client_name').' '.$this->client->name)
            ->line(trans('admin/settings/mcp.notify_action_redirect').' '.$redirect);

        if (! $isApproved && $this->reason !== null && $this->reason !== '') {
            $message->line(trans('admin/settings/mcp.notify_action_reason').' '.$this->reason);
        }

        $message->action(trans('admin/settings/mcp.notify_action_action'), $actionUrl);

        return $message;
    }
}
