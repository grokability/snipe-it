<?php

namespace App\Http\Controllers;

use App\Models\CheckoutAcceptance;
use App\Notifications\UnacceptedAssetReminderNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Unaccepted-acceptances
 */
class UnacceptedAcceptancesController extends Controller
{
    public function index(): View
    {
        $this->authorize('canCheckoutAtLeastOneItemType');

        $showDeleted = request('deleted') === 'deleted';

        return view('unaccepted-acceptances.index', compact('showDeleted'));
    }

    public function sendReminder(Request $request): RedirectResponse
    {
        $this->authorize('canCheckoutAtLeastOneItemType');

        $id = $request->integer('acceptance_id');
        $sent = $this->dispatchReminders($id > 0 ? [$id] : []);

        return redirect()->route('unaccepted-acceptances.index')->with(
            $sent > 0 ? 'success' : 'error',
            $sent > 0
                ? trans('admin/reports/general.reminder_sent')
                : trans('general.bad_data'),
        );
    }

    public function bulkSendReminders(Request $request): RedirectResponse
    {
        $this->authorize('canCheckoutAtLeastOneItemType');

        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $request->input('ids', [])))));
        $sent = $this->dispatchReminders($ids);

        return redirect()->route('unaccepted-acceptances.index')->with(
            $sent > 0 ? 'success' : 'warning',
            trans_choice('admin/reports/general.reminder_sent_count', $sent, ['count' => $sent]),
        );
    }

    public function destroy(int $acceptanceId): RedirectResponse
    {
        $this->authorize('canCheckoutAtLeastOneItemType');

        $acceptance = CheckoutAcceptance::pending()->find($acceptanceId);

        if (! $acceptance || ! $acceptance->isAccessibleToCurrentUser()) {
            return redirect()->route('unaccepted-acceptances.index')->with('error', trans('general.bad_data'));
        }

        if ($acceptance->delete()) {
            return redirect()->route('unaccepted-acceptances.index')->with('success', trans('admin/reports/general.acceptance_deleted'));
        }

        return redirect()->route('unaccepted-acceptances.index')->with('error', trans('general.deletion_failed'));
    }

    /**
     * Resolve the ids to pending acceptances, FMCS-filter, dedupe
     * by assignee so a user with five selected acceptances gets one
     * notification (with count=5), then fire
     * UnacceptedAssetReminderNotification per unique assignee.
     *
     * @param  array<int>  $acceptanceIds
     */
    private function dispatchReminders(array $acceptanceIds): int
    {
        if ($acceptanceIds === []) {
            return 0;
        }

        $acceptancesByAssignee = CheckoutAcceptance::pending()
            ->whereIn('id', $acceptanceIds)
            ->with(['assignedTo' => fn ($q) => $q->withTrashed()])
            ->get()
            ->filter(fn (CheckoutAcceptance $a) => $a->assignedTo && $a->isAccessibleToCurrentUser())
            ->groupBy(fn (CheckoutAcceptance $a) => $a->assignedTo->id);

        $sent = 0;
        foreach ($acceptancesByAssignee as $group) {
            /** @var CheckoutAcceptance $first */
            $first = $group->first();
            $assignee = $first->assignedTo;

            if (empty($assignee->email) || $assignee->activated == '0') {
                continue;
            }

            $pending = $assignee->getAssignedItemsWithPendingAcceptance();
            if ($pending->isEmpty()) {
                continue;
            }

            $assignee->notify(new UnacceptedAssetReminderNotification($first, $pending->count()));
            $sent++;
        }

        return $sent;
    }
}
