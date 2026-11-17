<?php

namespace App\Http\Controllers\Api;

use App\Console\Commands\SendAcceptanceReminder;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\AcceptancesTransformer;
use App\Models\CheckoutAcceptance;
use App\Notifications\UnacceptedAssetReminderNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * JSON endpoint powering /unaccepted-acceptances (via
 * UnacceptedAcceptancesController's Blade view).
 *
 * Query scoping, morph eager-loading, and the per-row FMCS filter
 * live on CheckoutAcceptanceQueryBuilder::visibleToCurrentUser so
 * the controller stays thin and the scoping decisions have one home.
 */
class UnacceptedAcceptancesController extends Controller
{
    public function index(Request $request): array
    {
        $this->authorize('canCheckoutAtLeastOneItemType');

        $query = CheckoutAcceptance::pending()
            ->visibleToCurrentUser()
            ->orderByDesc('checkout_acceptances.created_at');

        if ($request->boolean('deleted')) {
            // "Show deleted" toggle. Flips the default pending-only
            // scope to deleted-only (not with-trashed) so the view
            // actually surfaces the stale rows for audit without
            // mixing them in with the live pending set.
            $query->onlyTrashed();
        }

        $acceptances = $query->get()
            ->filter(fn (CheckoutAcceptance $acceptance) => $acceptance->checkoutable !== null)
            ->values();

        // Pagination lives in PHP because bs-table-client-side wants
        // the whole page body in one fetch anyway (data-side-pagination
        // was "client" on the old /reports/unaccepted_assets view too).
        $total = $acceptances->count();
        $offset = max(0, (int) $request->input('offset', 0));
        $limit = min(max(1, (int) $request->input('limit', $total ?: 1)), 500);
        $page = $acceptances->slice($offset, $limit)->values();

        return (new AcceptancesTransformer)->transformAcceptances($page, $total);
    }

    /**
     * Send reminder emails to users with unaccepted items. No `ids`
     * supplied: delegates to snipeit:acceptance-reminder so the
     * send-all shape is identical to the console command. With ids:
     * routes through UnacceptedAssetReminderNotification::dispatchForAcceptanceIds
     * which dedupes by assignee and surfaces a skip tally.
     */
    public function sendReminders(Request $request): JsonResponse
    {
        $this->authorize('canCheckoutAtLeastOneItemType');

        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $request->input('ids', [])))));

        if ($ids === []) {
            $result = SendAcceptanceReminder::dispatchAll();

            return response()->json(
                Helper::formatStandardApiResponse(
                    'success',
                    [
                        'mode' => 'all',
                        'sent' => $result['sent'],
                        'skipped' => [
                            'failed' => $result['failed'],
                            'no_email' => count($result['no_email']),
                        ],
                    ],
                    trans_choice('admin/reports/general.reminder_sent_count', $result['sent'], ['count' => $result['sent']]),
                ),
            );
        }

        $result = UnacceptedAssetReminderNotification::dispatchForAcceptanceIds($ids);

        return response()->json(
            Helper::formatStandardApiResponse(
                'success',
                [
                    'mode' => 'ids',
                    'sent' => $result['sent'],
                    'skipped' => $result['skipped'],
                ],
                trans_choice('admin/reports/general.reminder_sent_count', $result['sent'], ['count' => $result['sent']]),
            ),
        );
    }
}
