<?php

// A View Composer is a callback Laravel runs right before a specific view renders.
// It's registered in AppServiceProvider bound to 'layouts.default', so it only fires
// when a full page is rendered — not on modal AJAX responses, select2 searches, or
// API requests. This replaces the old AssetCountForSidebar middleware, which ran on
// every web request regardless of what was returned.

namespace App\View\Composers;

use App\Models\Asset;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SidebarComposer
{
    public function compose(View $view): void
    {
        // Guard against the setup wizard, where DB tables may not exist yet
        try {
            $settings = Setting::getSettings();
        } catch (\Exception $e) {
            Log::debug($e);

            return;
        }

        try {
            $due_for_checkin = Asset::DueForCheckin($settings)->count();
            $overdue_for_checkin = Asset::OverdueForCheckin()->count();
            $due_for_audit = Asset::DueForAudit($settings)->count();
            $overdue_for_audit = Asset::OverdueForAudit()->count();

            $view->with([
                'total_assets' => Asset::AssetsForShow()->count(),
                'total_rtd_sidebar' => Asset::RTD()->count(),
                'total_deployed_sidebar' => Asset::Deployed()->count(),
                'total_archived_sidebar' => Asset::Archived()->count(),
                'total_pending_sidebar' => Asset::Pending()->count(),
                'total_undeployable_sidebar' => Asset::Undeployable()->count(),
                'total_byod_sidebar' => Asset::where('byod', 1)->count(),
                'total_due_for_audit' => $due_for_audit,
                'total_overdue_for_audit' => $overdue_for_audit,
                'total_due_for_checkin' => $due_for_checkin,
                'total_overdue_for_checkin' => $overdue_for_checkin,
                'total_due_and_overdue_for_checkin' => $due_for_checkin + $overdue_for_checkin,
                'total_due_and_overdue_for_audit' => $due_for_audit + $overdue_for_audit,
            ]);
        } catch (\Exception $e) {
            Log::debug($e);
        }
    }
}
