<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan; // Note that this is awful close to 'Users' the namespace above; be careful

class LDAPImportController extends Controller
{
     /**
     * Return view for LDAP import.
     *
     * @author Aladin Alaily
     * @author Wes Hulette <jwhulette@gmail.com>
     *
     * @since 5.0.0
     *
     * @return \Illuminate\Contracts\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create()
    {
        // I guess this prolly oughtta... I dunno. Do something?
        $this->authorize('update', User::class);
        try {
            //$this->ldap->connect(); I don't think this actually exists in LdapAd.php, and we don't really 'persist' LDAP connections anyways...right?
        } catch (\Exception $e) {
            return redirect()->route('users.index')->with('error', $e->getMessage());
        }

        return view('users/ldap');
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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->authorize('update', User::class);
        // Call Artisan LDAP import command.

        Artisan::call('snipeit:ldap-sync', ['--location_id' => $request->input('location_id'), '--json_summary' => true]);

        // Collect and parse JSON summary.
        $ldap_results_json = Artisan::output();
        $ldap_results = json_decode($ldap_results_json, true);
        if (!$ldap_results) {
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
