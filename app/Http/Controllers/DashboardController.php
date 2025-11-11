<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\RedirectResponse;
use \Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;


/**
 * This controller handles all actions related to the Admin Dashboard
 * for the Snipe-IT Asset Management application.
 *
 * @author A. Gianotto <snipe@snipe.net>
 * @version v1.0
 */
class DashboardController extends Controller
{
    /**
     * Check authorization and display admin dashboard, otherwise display
     * the user's checked-out assets.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     */
    public function index() : View | RedirectResponse
    {
        // Show the page
        if (auth()->user()->hasAccess('admin')) {
            $asset_stats = null;

            $counts['asset'] = \App\Models\Asset::count();
            $counts['accessory'] = \App\Models\Accessory::count();
            $counts['license'] = \App\Models\License::assetcount();
            $counts['consumable'] = \App\Models\Consumable::count();
            $counts['component'] = \App\Models\Component::count();
            $counts['user'] = \App\Models\Company::scopeCompanyables(auth()->user())->count();
            $counts['grand_total'] = $counts['asset'] + $counts['accessory'] + $counts['license'] + $counts['consumable'];

            // Work Orders statistics - with error handling
            try {
                $counts['workorder_pending'] = \App\Models\WorkOrder::where('status', 'pending')->count();
                $counts['workorder_in_progress'] = \App\Models\WorkOrder::where('status', 'in_progress')->count();
                $counts['workorder_overdue'] = \App\Models\WorkOrder::where('status', '!=', 'completed')
                    ->where('scheduled_end', '<', now())
                    ->count();
                $counts['workorder_total'] = \App\Models\WorkOrder::count();
            } catch (\Exception $e) {
                // If WorkOrder table doesn't exist or error occurs, set defaults
                $counts['workorder_pending'] = 0;
                $counts['workorder_in_progress'] = 0;
                $counts['workorder_overdue'] = 0;
                $counts['workorder_total'] = 0;
            }

            // Maintenance Schedule statistics - with error handling
            try {
                $counts['schedule_overdue'] = \App\Models\MaintenanceSchedule::where('next_due_date', '<', now())
                    ->where('status', 'active')
                    ->count();
                $counts['schedule_upcoming'] = \App\Models\MaintenanceSchedule::whereBetween('next_due_date', [now(), now()->addDays(7)])
                    ->where('status', 'active')
                    ->count();
            } catch (\Exception $e) {
                // If MaintenanceSchedule table doesn't exist or error occurs, set defaults
                $counts['schedule_overdue'] = 0;
                $counts['schedule_upcoming'] = 0;
            }

            // Load user's dashboard widget preferences
            $user = auth()->user();
            $widgets = \App\Models\DashboardWidget::where('user_id', $user->id)->get()->keyBy('widget_id');
            
            // If no widgets exist, create defaults
            if ($widgets->isEmpty()) {
                $defaultWidgets = \App\Models\DashboardWidget::getDefaultWidgets();
                foreach ($defaultWidgets as $widget) {
                    $widget['user_id'] = $user->id;
                    \App\Models\DashboardWidget::create($widget);
                }
                $widgets = \App\Models\DashboardWidget::where('user_id', $user->id)->get()->keyBy('widget_id');
            }

            if ((! file_exists(storage_path().'/oauth-private.key')) || (! file_exists(storage_path().'/oauth-public.key'))) {
                Artisan::call('migrate', ['--force' => true]);
                Artisan::call('passport:install', ['--no-interaction' => true]);
            }

            return view('dashboard')
                ->with('asset_stats', $asset_stats)
                ->with('counts', $counts)
                ->with('widgets', $widgets);
        } else {
            Session::reflash();

            // Redirect to the profile page
            return redirect()->intended('account/view-assets');
        }
    }
}
