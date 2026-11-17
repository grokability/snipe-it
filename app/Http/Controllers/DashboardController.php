<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;

/**
 * This controller handles all actions related to the Admin Dashboard
 * for the Snipe-IT Asset Management application.
 *
 * @author A. Gianotto <snipe@snipe.net>
 *
 * @version v1.0
 */
class DashboardController extends Controller
{
    /**
     * Check authorization and display the dashboard, otherwise display
     * the user's checked-out assets.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     */
    public function index(): View|RedirectResponse
    {
        if (! Gate::allows('canViewUsersAndCheckoutables')) {
            Session::reflash();

            return Helper::safeIntended('account/view-assets');
        }

        // Top-boxes + empty-inventory shortcut are admin-only in the
        // dashboard blade, so the six count queries only need to run
        // for admins. Non-admins get an empty array and the view's
        // hasAccess('admin') gates short-circuit before touching any
        // $counts key.
        $counts = [];
        if (auth()->user()->hasAccess('admin')) {
            $counts['asset'] = Asset::count();
            // Accessory / Consumable / Component rows are parents with a
            // qty column that represents individual units in stock. Show
            // the sum of units across live non-archived rows instead of
            // the parent-row count so the dashboard reflects actual
            // inventory volume. SoftDeletes drops trashed parents, and
            // notArchived() excludes retired-but-live rows.
            $counts['accessory'] = (int) Accessory::notArchived()->sum('qty');
            $counts['license'] = License::assetcount();
            $counts['consumable'] = (int) Consumable::notArchived()->sum('qty');
            $counts['component'] = (int) Component::notArchived()->sum('qty');
            $counts['user'] = Company::scopeCompanyables(auth()->user())->count();
            $counts['grand_total'] = $counts['asset'] + $counts['accessory'] + $counts['license'] + $counts['consumable'];
        }

        if ((! file_exists(storage_path().'/oauth-private.key')) || (! file_exists(storage_path().'/oauth-public.key'))) {
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('passport:install', ['--no-interaction' => true]);
        }

        return view('dashboard')
            ->with('asset_stats', null)
            ->with('counts', $counts);
    }
}
