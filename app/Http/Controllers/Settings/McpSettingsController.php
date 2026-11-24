<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\OAuthClient;
use App\Models\User;
use App\Notifications\OauthClientDecisionNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Approve / reject actions for the MCP OAuth approval workflow. Read-
 * side (pending list) and the trusted-lists form both live inside
 * SettingsController::api, which renders them on the shared admin OAuth
 * settings page. Only the per-client action mutations need their own
 * controller because SettingsController is already large enough.
 *
 * Every action is gated on superuser (see enforceSuperuser). The
 * underlying OAuth plumbing is powerful enough that only superusers
 * should ever touch it, matching how admin/oauth is gated elsewhere.
 * Actions redirect back to settings.oauth.index with the oauth-clients
 * fragment so the admin lands back on the tab they acted from.
 */
class McpSettingsController extends Controller
{
    public function approve(string $client): RedirectResponse
    {
        $this->enforceSuperuser();

        $oauthClient = OAuthClient::find($client);
        if ($oauthClient === null) {
            return redirect()->route('settings.oauth.index', ['#pending-oauth-clients'])
                ->with('error', trans('admin/settings/mcp.client_not_found'));
        }

        $oauthClient->forceFill([
            'status' => OAuthClient::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by_id' => auth()->id(),
            'rejected_reason' => null,
        ])->save();

        Log::info('MCP OAuth client '.$oauthClient->id.' approved by user ID '.auth()->id());

        $this->notifyOtherSuperusers($oauthClient, OauthClientDecisionNotification::DECISION_APPROVED);

        return redirect()->route('settings.oauth.index', ['#pending-oauth-clients'])
            ->with('success', trans('admin/settings/mcp.approved_success'));
    }

    public function reject(Request $request, string $client): RedirectResponse
    {
        $this->enforceSuperuser();

        $data = $request->validate([
            'rejected_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $oauthClient = OAuthClient::find($client);
        if ($oauthClient === null) {
            return redirect()->route('settings.oauth.index', ['#pending-oauth-clients'])
                ->with('error', trans('admin/settings/mcp.client_not_found'));
        }

        $oauthClient->forceFill([
            'status' => OAuthClient::STATUS_REJECTED,
            'approved_at' => null,
            'approved_by_id' => auth()->id(),
            'rejected_reason' => $data['rejected_reason'] ?? null,
        ])->save();

        Log::info('MCP OAuth client '.$oauthClient->id.' rejected by user ID '.auth()->id());

        $this->notifyOtherSuperusers(
            $oauthClient,
            OauthClientDecisionNotification::DECISION_REJECTED,
            $data['rejected_reason'] ?? null,
        );

        return redirect()->route('settings.oauth.index', ['#pending-oauth-clients'])
            ->with('success', trans('admin/settings/mcp.rejected_success'));
    }

    /**
     * Audit-trail email to every OTHER active superuser (the actor
     * excluded) whenever a pending client is approved or rejected.
     * Wrapped in a try/catch so mail-driver misconfig doesn't take
     * down the state transition the admin just made.
     */
    private function notifyOtherSuperusers(OAuthClient $client, string $decision, ?string $reason = null): void
    {
        try {
            $actor = auth()->user();
            if (! $actor instanceof User) {
                return;
            }

            // Active superusers with a usable mail address, minus the
            // acting superuser. Empty / null emails are filtered at
            // the query so the mail driver never gets a blank To.
            $recipients = User::query()
                ->where('activated', 1)
                ->where('id', '!=', $actor->id)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->onlySuperAdmins()
                ->get();

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new OauthClientDecisionNotification($client, $actor, $decision, $reason));
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to notify peer superusers about OAuth client '.$client->id.' '.$decision.': '.$e->getMessage());
        }
    }

    private function enforceSuperuser(): void
    {
        $user = auth()->user();
        if ($user === null || ! $user->isSuperUser()) {
            abort(403, trans('admin/settings/mcp.action_requires_superuser'));
        }
    }
}
