<?php

namespace App\Notifications;

use App\Models\OAuthClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Heads-up email sent to active superusers whenever a new OAuth
 * client self-registers via DCR and lands in the pending-approval
 * queue. Subject line explicitly says "pending" so admins can filter
 * / rule these in their inbox without drowning the signal.
 *
 * Fired from McpOAuthRegisterController::applyApprovalWorkflow when
 * the just-registered client's status is pending. Auto-approved
 * registrations (loopback, first-party mobile scheme, admin-trusted)
 * do NOT fire this to keep the noise floor sane on busy installs.
 */
class PendingOauthClientNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly OAuthClient $client,
        public readonly ?string $registeredIp,
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
        $redirect = $this->client->primary_redirect_uri ?? '(unknown)';
        $ip = $this->registeredIp ?? '(unknown)';
        $actionUrl = route('settings.oauth.index').'#pending-oauth-clients';

        return (new MailMessage)
            ->subject(trans('admin/settings/mcp.notify_pending_subject'))
            ->greeting(trans('admin/settings/mcp.notify_pending_greeting'))
            ->line(trans('admin/settings/mcp.notify_pending_intro'))
            ->line(trans('admin/settings/mcp.notify_pending_client_name').' '.$this->client->name)
            ->line(trans('admin/settings/mcp.notify_pending_redirect').' '.$redirect)
            ->line(trans('admin/settings/mcp.notify_pending_ip').' '.$ip)
            ->action(trans('admin/settings/mcp.notify_pending_action'), $actionUrl)
            ->line(trans('admin/settings/mcp.notify_pending_outro'));
    }
}
