<?php

namespace App\Http\Controllers\Users;

use App\Console\Commands\LdapSync;
use App\Http\Controllers\Controller;
use App\Traits\LocksLongRunningOperation;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

// Note that this is awful close to 'Users' the namespace above; be careful

class LDAPImportController extends Controller
{
    use LocksLongRunningOperation;

    /**
     * Return view for LDAP import.
     *
     * @author Aladin Alaily
     * @author Wes Hulette <jwhulette@gmail.com>
     *
     * @since 5.0.0
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function create()
    {
        // Superuser-only: a bulk LDAP sync surfaces users from across
        // the entire directory (all companies, all OUs), so anyone with
        // "users.edit" but no full-directory access shouldn't be able
        // to run it or read the summary that lists them.
        if (! auth()->user()?->isSuperUser()) {
            abort(403);
        }

        // Belt-and-suspenders UI for the shared LDAP sync lock.
        // The command itself refuses a concurrent trigger, this just
        // stops the admin from clicking a button that will immediately
        // fail.
        $ldapSyncRunning = $this->isOperationLocked(LdapSync::LDAP_SYNC_LOCK);

        return view('users/ldap', compact('ldapSyncRunning'));
    }

    /**
     * LDAP form processing.
     *
     * @author Aladin Alaily
     * @author A. Gianotto <snipe@snipe.net>
     * @author Wes Hulette <jwhulette@gmail.com>
     *
     * @since 5.0.0
     *
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        // See create() for the superuser-only rationale.
        if (! auth()->user()?->isSuperUser()) {
            abort(403);
        }
        // Call Artisan LDAP import command.

        Artisan::call('snipeit:ldap-sync', ['--location_id' => $request->input('location_id'), '--json_summary' => true]);

        // Collect and parse JSON summary.
        $ldap_results_json = Artisan::output();
        $ldap_results = json_decode($ldap_results_json, true);
        if (! $ldap_results) {
            return redirect()->back()->withInput()->with('error', trans('general.no_results'));
        }

        // Direct user to appropriate status page.
        if ($ldap_results['error']) {

            return redirect()->back()->withInput()->with('error', $ldap_results['error_message']);
        }

        return redirect()->route('ldap/user')
            ->with('success', 'LDAP Import successful.')
            ->with('summary', $ldap_results['summary']);
    }
}
