<?php

namespace App\Models;

use App\Models\Setting;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

/***********************************************
 * TODOS:
 *
 * First off, we should probably make it so that the main LDAP thing we're using is an *instance* of this class,
 * rather than the static methods we use here. We should probably load up that class with its settings, so we
 * don't have to explicitly refer to them so often.
 *
 * Then, we should probably look at embedding some of the logic we use elsewhere into here - the various methods
 * should either return a User or false, or other things like that. Don't make the consumers of this class reach
 * into its guts. While that conflates this model with the User model, I think having the appropriate logic for
 * turning LDAP people into Users ought to belong here, so it's easier on the consumer of this class.
 *
 * We're probably going to have to eventually make it so that Snipe-IT users can define multiple LDAP servers,
 * and having this as a more instance-oriented class will be a step in the right direction.
 ***********************************************/

class Ldap extends Model
{
    public static function ignoreCertificates(bool $ignore_cert = true)
    {
        if (defined('LDAP_OPT_X_TLS_REQUIRE_CERT') && defined('LDAP_OPT_X_TLS_NEVER')) {
            // TODO - we are currently, as a 'safety', doing *both* the following 'new-style' ldap_set_option calls,
            // as well as "falling-through" to the 'old-style' putenv() calls.
            //
            // I *suspect* we can eventually remove the putenv() calls, but I'm just a little nervous about that.
            // According to the PHP docs, the LDAP_OPT_X_TLS_REQUIRE_CERT constant has been available since PHP 7.0.
            // We're currently using PHP versions way, way later than that (v8.2-v8.4 as of this writing). So it's
            // unlikely that these constants wouldn't be defined - unless you didn't have LDAP support in the first
            // place. But if that were to happen, I would hope we would've detected that long, long ago, rather than at
            // this point.
            if ($ignore_cert) {
                if (ldap_set_option(null, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_NEVER)) {
                    //return true;
                }
            } else {
                if (ldap_set_option(null, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_DEMAND)) {
                    //return true;
                }
            }
        }
        if ($ignore_cert) {
            return putenv('LDAPTLS_REQCERT=never');
        } else {
            return putenv('LDAPTLS_REQCERT');
        }
    }

    /**
     * Makes a connection to LDAP using the settings in Admin > Settings.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return connection
     */
    public static function connectToLdap()
    {
        $ldap_host = Setting::getSettings()->ldap_server;
        $ldap_version = Setting::getSettings()->ldap_version ?: 3;
        $ldap_server_cert_ignore = Setting::getSettings()->ldap_server_cert_ignore;
        $ldap_use_tls = Setting::getSettings()->ldap_tls;

        // If we are ignoring the SSL cert we need to setup the environment variable
        // before we create the connection
        self::ignoreCertificates((bool)$ldap_server_cert_ignore);

        // If the user specifies where CA Certs are, make sure to use them
        if (env('LDAPTLS_CACERT')) {
            putenv('LDAPTLS_CACERT='.env('LDAPTLS_CACERT'));
        }
        // You _were_ allowed to do this *after* the ldap_connect() in some versions of PHP, but it's not how they want
        // you to anymore, and it seems to not work at all in later PHP versions.
        if (Setting::getSettings()->ldap_client_tls_cert && Setting::getSettings()->ldap_client_tls_key) {
            ldap_set_option(null, LDAP_OPT_X_TLS_CERTFILE, Setting::get_client_side_cert_path());
            ldap_set_option(null, LDAP_OPT_X_TLS_KEYFILE, Setting::get_client_side_key_path());
        }

        $connection = @ldap_connect($ldap_host);

        if (! $connection) {
            throw new Exception('Could not connect to LDAP server at '.$ldap_host.'. Please check your LDAP server name and port number in your settings.');
        }

        // Needed for AD
        ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, $ldap_version);
        ldap_set_option($connection, LDAP_OPT_NETWORK_TIMEOUT, 20);

        if ($ldap_use_tls=='1') {
            ldap_start_tls($connection);
        }


        return $connection;
    }


    /**
     * Binds/authenticates the user to LDAP, and returns their attributes.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @param  $username
     * @param  $password
     * @param  bool|false $user
     * @return bool true    if the username and/or password provided are valid
     *              false   if the username and/or password provided are invalid
     *         array of ldap_attributes if $user is true
     */
    public static function findAndBindUserLdap($username, $password)
    {
        $settings = Setting::getSettings();
        $connection = self::connectToLdap();
        $ldap_username_field = $settings->ldap_username_field;
        $baseDn = $settings->ldap_basedn;
        $userDn = $ldap_username_field.'='.$username.','.$settings->ldap_basedn;

        if ($settings->is_ad == '1') {
            // Check if they are using the userprincipalname for the username field.
            // If they are, we can skip building the UPN to authenticate against AD
            if ($ldap_username_field == 'userprincipalname') {
                $userDn = $username;
            } else {
                // TODO - we no longer respect the "add AD Domain to username" checkbox, but it still exists in settings.
                // We should probably just eliminate that checkbox to avoid confusion.
                // We let it sit in the DB, unused, to facilitate people downgrading (if they decide to).
                // Hopefully, in a later release, we can remove it from the settings.
                // This logic instead just means that if we're using UPN, we don't append ad_domain, if we aren't, then we do.
                // Hopefully that should handle all of our use cases, but if not we can backport our old logic.
                $userDn = ($settings->ad_domain != '') ? $username.'@'.$settings->ad_domain : $username.'@'.$settings->email_domain;
            }
        }

        $filterQuery = $settings->ldap_auth_filter_query.$username;
        $filter = Setting::getSettings()->ldap_filter; //FIXME - this *does* respect the ldap filter, but I believe that AdLdap2 did *not*.
        $filterQuery = "({$filter}({$filterQuery}))";

        Log::debug('Filter query: '.$filterQuery);

        if (! $ldapbind = @ldap_bind($connection, $userDn, $password)) {
            Log::debug("Status of binding user: $userDn to directory: (directly!) ".($ldapbind ? "success" : "FAILURE"));
            if (! $ldapbind = self::bindAdminToLdap($connection)) {
                /*
                 * TODO PLEASE:
                 *
                 * this isn't very clear, so it's important to note: the $ldapbind value is never correctly returned - we never 'return true' from self::bindAdminToLdap() (the function
                 * just "falls off the end" without ever explictly returning 'true')
                 *
                 * but it *does* have an interesting side-effect of checking for the LDAP password being incorrectly encrypted with the wrong APP_KEY, so I'm leaving it in for now.
                 *
                 * If it *did* correctly return 'true' on a succesful bind, it would _probably_ allow users to log in with an incorrect password. Which would be horrible!
                 *
                 * Let's definitely fix this at the next refactor!!!!
                 *
                 */
                Log::debug("Status of binding Admin user: $userDn to directory instead: ".($ldapbind ? "success" : "FAILURE"));
                return false;
            }
        }

        if (! $results = ldap_search($connection, $baseDn, $filterQuery)) {
            throw new Exception('Could not search LDAP: ');
        }

        if (! $entry = ldap_first_entry($connection, $results)) {
            return false;
        }

        if (! $user = ldap_get_attributes($connection, $entry)) {
            return false;
        }

        return array_change_key_case($user);
    }

    /**
     * Binds/authenticates an admin to LDAP for LDAP searching/syncing.
     * Here we also return a better error if the app key is donked.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @param  bool|false $user
     * @return bool true    if the username and/or password provided are valid
     *              false   if the username and/or password provided are invalid
     */
    public static function bindAdminToLdap($connection)
    {
        $ldap_username = Setting::getSettings()->ldap_uname;

        if ($ldap_username ) {
            // Lets return some nicer messages for users who donked their app key, and disable LDAP
            try {
                $ldap_pass = Crypt::decrypt(Setting::getSettings()->ldap_pword);
            } catch (Exception $e) {
                throw new Exception('Your app key has changed! Could not decrypt LDAP password using your current app key, so LDAP authentication has been disabled. Login with a local account, update the LDAP password and re-enable it in Admin > Settings.');
            }

            if (! $ldapbind = @ldap_bind($connection, $ldap_username, $ldap_pass)) {
                throw new Exception('Could not bind to LDAP: '.ldap_error($connection));
            }
            // TODO - this just "falls off the end" but the function states that it should return true or false
            // unfortunately, one of the use cases for this function is wrong and *needs* for that failure mode to fire
            // so I don't want to fix this right now.
            // this method MODIFIES STATE on the passed-in $connection and just returns true or false (or, in this case, undefined)
            // at the next refactor, this should be appropriately modified to be more consistent.
        } else {
            // LDAP should also work with anonymous bind (no dn, no password available)
            if (! $ldapbind = @ldap_bind($connection)) {
                throw new Exception('Could not bind to LDAP: '.ldap_error($connection));
            }
        }
    }

    /**
     * Parse and map LDAP attributes based on settings
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     *
     * @param  $ldapatttibutes
     * @return array|bool
     */
    public static function parseAndMapLdapAttributes($ldapattributes)
    {
        //Get LDAP attribute config
        $ldap_result_username = Setting::getSettings()->ldap_username_field;
        $ldap_result_emp_num = Setting::getSettings()->ldap_emp_num;
        $ldap_result_last_name = Setting::getSettings()->ldap_lname_field;
        $ldap_result_first_name = Setting::getSettings()->ldap_fname_field;
        $ldap_result_email = Setting::getSettings()->ldap_email;
        $ldap_result_phone = Setting::getSettings()->ldap_phone;
        $ldap_result_jobtitle = Setting::getSettings()->ldap_jobtitle;
        $ldap_result_country = Setting::getSettings()->ldap_country;
        $ldap_result_location = Setting::getSettings()->ldap_location;
        $ldap_result_dept = Setting::getSettings()->ldap_dept;
        $ldap_result_manager = Setting::getSettings()->ldap_manager;
        // Get LDAP user data
        $item = [];
        $item['username'] = $ldapattributes[$ldap_result_username][0] ?? '';
        $item['employee_number'] = $ldapattributes[$ldap_result_emp_num][0] ?? '';
        $item['lastname'] = $ldapattributes[$ldap_result_last_name][0] ?? '';
        $item['firstname'] = $ldapattributes[$ldap_result_first_name][0] ?? '';
        $item['email'] = $ldapattributes[$ldap_result_email][0] ?? '';
        $item['telephone'] = $ldapattributes[$ldap_result_phone][0] ?? '';
        $item['jobtitle'] = $ldapattributes[$ldap_result_jobtitle][0] ?? '';
        $item['country'] = $ldapattributes[$ldap_result_country][0] ?? '';
        $item['department'] = $ldapattributes[$ldap_result_dept][0] ?? '';
        $item['manager'] = $ldapattributes[$ldap_result_manager][0] ?? '';
        $item['location'] = $ldapattributes[$ldap_result_location][0] ?? '';
        $item['locale'] = app()->getLocale();

        return $item;
    }

    /**
     * Create user from LDAP attributes
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @param  $ldapatttibutes
     * @return User | bool
     */
    public static function createUserFromLdap($ldapatttibutes, $password)
    {
        $item = self::parseAndMapLdapAttributes($ldapatttibutes);

        // Create user from LDAP data
        if (! empty($item['username'])) {
            $user = new User;
            $user->first_name = $item['firstname'];
            $user->last_name = $item['lastname'];
            $user->username = $item['username'];
            $user->email = $item['email'];
            $user->locale = $item['locale'];
            $user->password = $user->noPassword();

            if (Setting::getSettings()->ldap_pw_sync == '1') {
                $user->password = bcrypt($password);
            }

            $user->activated = 1;
            $user->ldap_import = 1;
            $user->notes = 'Imported on first login from LDAP';

            if ($user->save()) {
                return $user;
            } else {
                Log::debug('Could not create user.'.$user->getErrors());
                throw new Exception('Could not create user: '.$user->getErrors());
            }
        }

        return false;
    }

    /**
     * Searches LDAP
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @param  $base_dn
     * @param  $count
     * @param  $filter
     * @param  $attributes
     * @return array|bool
     */
    public static function findLdapUsers($base_dn = null, $count = -1, $filter = null, $attributes = [])
    {
        $ldapconn = self::connectToLdap();
        self::bindAdminToLdap($ldapconn);
        // Default to global base DN if nothing else is provided.
        if (is_null($base_dn)) {
            $base_dn = Setting::getSettings()->ldap_basedn;
        }
        if($filter === null) {
            $filter = Setting::getSettings()->ldap_filter;
        }

        // Set up LDAP pagination for very large databases
        $page_size = 500;
        $cookie = '';
        $result_set = [];
        $global_count = 0;

        // Perform the search
        do {

            if ($filter != '' && substr($filter, 0, 1) != '(') { // wrap parens around NON-EMPTY filters that DON'T have them, for back-compatibility with AdLdap2-based filters
                $filter = "($filter)";
            } elseif ($filter == '') {
                $filter = '(cn=*)';
            }

            // HUGE thanks to this article: https://stackoverflow.com/questions/68275972/how-to-get-paged-ldap-queries-in-php-8-and-read-more-than-1000-entries
            // which helped me wrap my head around paged results!
            // if a $count is set and it's smaller than $page_size then use that as the page size
            $ldap_controls = [];
            //if($count == -1) { //count is -1 means we have to employ paging to query the entire directory
                $ldap_controls = [['oid' => LDAP_CONTROL_PAGEDRESULTS, 'iscritical' => false, 'value' => ['size'=> $count == -1||$count>$page_size ? $page_size : $count, 'cookie' => $cookie]]];
            //}
            $search_results = ldap_search($ldapconn, $base_dn, $filter, $attributes, 0, /* $page_size */ -1, -1, LDAP_DEREF_NEVER, $ldap_controls); // TODO - I hate the @, and I hate that we get a full page even if we ask for 10 records. Can we use an ldap_control?
            Log::debug("LDAP search executed successfully.");
            if (! $search_results) {
                return redirect()->route('users.index')->with('error', trans('admin/users/message.error.ldap_could_not_search').ldap_error($ldapconn)); // TODO this is never called in any routed context - only from the Artisan command. So this redirect will never work.
            }

            $errcode = null;
            $matcheddn = null;
            $errmsg = null;
            $referrals = null;
            $controls = [];
            ldap_parse_result($ldapconn, $search_results, $errcode, $matcheddn, $errmsg, $referrals, $controls);
            if (isset($controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'])) {
                // You need to pass the cookie from the last call to the next one
                $cookie = $controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'];
                Log::debug("okay, at least one more page to go!!!");
            } else {
                Log::debug("okay, we're out of pages - no cookie (or empty cookie) was passed");
                $cookie = '';
            }
            // Empty cookie means last page

            // Get results from page
            $results = ldap_get_entries($ldapconn, $search_results);
            if (! $results) {
                return redirect()->route('users.index')->with('error', trans('admin/users/message.error.ldap_could_not_get_entries').ldap_error($ldapconn)); // TODO this is never called in any routed context - only from the Artisan command. So this redirect will never work.
            }

            // Add results to result set
            $global_count += $results['count'];
            $result_set = array_merge($result_set, $results);
            Log::debug("Total count is: $global_count");

        } while ($cookie !== null && $cookie != '' && ($count == -1 || $global_count < $count)); // some servers don't even have pagination, and some will give you more results than you asked for, so just see if you have enough.

        // Clean up after search
        $result_set['count'] = $global_count; // TODO: I would've figured you could just count the array instead?
        $results = $result_set;

        return $results;
    }
}
