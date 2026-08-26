<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

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
                    // return true;
                }
            } else {
                if (ldap_set_option(null, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_DEMAND)) {
                    // return true;
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
     *
     * @since  [v3.0]
     */
    public static function connectToLdap()
    {
        $ldap_host = Setting::getSettings()->ldap_server;
        $ldap_version = Setting::getSettings()->ldap_version ?: 3;
        $ldap_server_cert_ignore = Setting::getSettings()->ldap_server_cert_ignore;
        $ldap_use_tls = Setting::getSettings()->ldap_tls;

        // If we are ignoring the SSL cert we need to setup the environment variable
        // before we create the connection
        self::ignoreCertificates((bool) $ldap_server_cert_ignore);

        // If the user specifies where CA Certs are, make sure to use them
        if (config('app.ldap_tls_cacert')) {
            putenv('LDAPTLS_CACERT='.config('app.ldap_tls_cacert'));
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

        if ($ldap_use_tls == '1') {
            if (! ldap_start_tls($connection)) {
                throw new Exception('STARTTLS Failed.');
            }
        }

        return $connection;
    }

    /**
     * Finds user via Admin search *first*, and _then_ try to bind as that user, returning the user attributes on success,
     * or false on failure. This enables login when the DN is harder to programmatically 'guess' due to having users in
     * various different OU's or other LDAP entities.
     */
    public static function findAndBindMultiOU(string $baseDn, string $filterQuery, string $password, int $slow_failure = 3): array|false
    {
        /**
         *  If you *don't* set the slow_failure variable, do note that we might permit timing attacks in here - if
         *  your find results come back 'slow' when a user *does* exist, but fast if they *don't* exist, then you
         *  can use this to enumerate users.
         *
         *  Even if that's *not* true, we still might have an issue: if we don't find the user, then we don't even _try_
         *  to bind as them. Again, that could permit a timing attack.
         *
         *  Instead of checking every little thing, we just wrap everything in a try/catch in order to unify the
         *  'slow_failure' treatment. All failures are re-raised as exceptions so that all failures exit from the
         *  same place.
         */
        $connection = null;
        $admin_conn = null;
        try {
            /**
             * First we get an 'admin' connection, which will need search permissions. That was already a requirement
             * here, so that's not a big lift. But it _is_ possible to configure LDAP to only login, and *not* to be
             * able to import lists of users. In that case, this function *will not work* - and you should use the
             * legacy 'findAndBindUserLdap' method, below. Otherwise, it looks like this would attempt an anonymous
             * bind - which you might want, but you probably don't.
             *
             **/
            $admin_conn = self::connectToLdap();
            self::bindAdminToLdap($admin_conn);
            $results = ldap_search($admin_conn, $baseDn, $filterQuery);
            $entry_count = ldap_count_entries($admin_conn, $results);
            if ($entry_count != 1) {
                throw new Exception('Wrong number of entries found: '.$entry_count);
            }
            $entry = ldap_first_entry($admin_conn, $results);
            $user = ldap_get_attributes($admin_conn, $entry);
            $userDn = ldap_get_dn($admin_conn, $entry);
            if (! $userDn) {
                throw new Exception('No user DN found');
            }
            \Log::debug("FOUND DN IS: $userDn");
            // The temptation now is to do ldap_unbind on the $admin_conn, but that gets handled in the 'finally' below.
            // I don't know if that means a separate 'connection' is maintained to the LDAP server or not, and would
            // definitely prefer to not do that if we can avoid it. But I don't know enough about the LDAP protocol to
            // be certain that that happens.

            // now we try to log in (bind) as that found user
            $connection = self::connectToLdap();
            $bind_results = ldap_bind($connection, $userDn, $password);
            if (! $bind_results) {
                throw new Exception('Unable to bind as user');
            }

            return array_change_key_case($user);
        } catch (Exception $e) {
            \Log::debug('Exception on fast find-and-bind: '.$e->getMessage());
            if ($slow_failure) {
                sleep($slow_failure);
            }

            return false; // TODO - make this null instead for a slightly nicer type signature
        } finally {
            if ($admin_conn) {
                ldap_unbind($admin_conn);
            }
            if ($connection) {
                ldap_unbind($connection);
            }
        }
    }

    /**
     * Binds/authenticates the user to LDAP, and returns their attributes
     * (lowercase-keyed) on success or false when the bind or search fails.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return array|false
     */
    public static function findAndBindUserLdap($username, $password)
    {
        $settings = Setting::getSettings();
        $connection = self::connectToLdap();
        $ldap_username_field = $settings->ldap_username_field;
        $baseDn = $settings->ldap_basedn;
        $userDn = $ldap_username_field.'='.ldap_escape($username, '', LDAP_ESCAPE_DN).','.$settings->ldap_basedn;

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

        $filterQuery = $settings->ldap_auth_filter_query.ldap_escape($username, '', LDAP_ESCAPE_FILTER);
        $filter = Setting::getSettings()->ldap_filter; // FIXME - this *does* respect the ldap filter, but I believe that AdLdap2 did *not*.
        $filterQuery = "({$filter}({$filterQuery}))";

        Log::debug('Filter query: '.$filterQuery);

        // only try this if we have an Admin username set; otherwise use the 'legacy' method
        if (($settings->ldap_uname) && ($baseDn)) {
            // in the fallowing call, we pick a slow-failure of 0 because we might need to fall through to 'legacy'
            $fast_bind = self::findAndBindMultiOU($baseDn, $filterQuery, $password, 0);
            if ($fast_bind) {
                \Log::debug('Fast bind worked');

                return $fast_bind;
            }
            \Log::debug('Fast bind failed; falling through to legacy bind');
        }

        if (! $ldapbind = @ldap_bind($connection, $userDn, $password)) {
            Log::debug("Status of binding user: $userDn to directory: (directly!) ".($ldapbind ? 'success' : 'FAILURE'));
            // replicate the old bad-decryption-key detection behavior here
            try {
                Crypt::decrypt(Setting::getSettings()->ldap_pword);
            } catch (Exception $e) {
                throw new Exception('Your app key has changed! Could not decrypt LDAP password using your current app key, so LDAP authentication has been disabled. Login with a local account, update the LDAP password and re-enable it in Admin > Settings.');
            }

            // regardless of anything else; stuff isn't working. Return false.
            return false;
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
     * Returns true when the given config source (Setting model, live
     * Livewire component state, whatever) matches the auto-detect
     * condition that routes bindAdminToLdap through SASL EXTERNAL:
     * client cert + key are populated AND both bind DN and bind
     * password are blank.
     *
     * Runtime, LDAP troubleshooter, and LDAP Livewire wizard all
     * evaluate the same.
     */
    public static function shouldUseSaslExternal(object $config): bool
    {
        return !empty($config->ldap_client_tls_cert)
            && !empty($config->ldap_client_tls_key)
            && empty($config->ldap_uname)
            && empty($config->ldap_pword);
    }

    /**
     * Binds/authenticates an admin to LDAP for LDAP searching/syncing.
     * Modifies state on the passed-in $connection. Throws on any bind
     * failure with a decoded message (including a friendlier "app key
     * changed" message for the encrypted-password decrypt path).
     * Callers wrap in try/catch and rely on the exception, not a
     * return value.
     *
     * @throws Exception on any bind failure
     * @since  [v3.0]
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     */
    public static function bindAdminToLdap($connection): void
    {
        $settings = Setting::getSettings();

        $ldap_username = $settings->ldap_uname;

        if (self::shouldUseSaslExternal($settings)) {
            if (! @ldap_sasl_bind($connection, null, null, 'EXTERNAL')) {
                throw new Exception('Could not bind to LDAP via SASL EXTERNAL: '.self::bindError($connection));
            }
        } elseif ($ldap_username) {
            // Lets return some nicer messages for users who donked their app key, and disable LDAP
            try {
                $ldap_pass = Crypt::decrypt($settings->ldap_pword);
            } catch (Exception $e) {
                throw new Exception('Your app key has changed! Could not decrypt LDAP password using your current app key, so LDAP authentication has been disabled. Login with a local account, update the LDAP password and re-enable it in Admin > Settings.');
            }

            if (! @ldap_bind($connection, $ldap_username, $ldap_pass)) {
                throw new Exception('Could not bind to LDAP: '.self::bindError($connection));
            }
        } else {
            // LDAP should also work with anonymous bind (no dn, no password available)
            if (! @ldap_bind($connection)) {
                throw new Exception('Could not bind to LDAP: '.self::bindError($connection));
            }
        }
    }

    /**
     * Return "<ldap_error message> (<diagnostic message>)" for a failed
     * operation on the given LDAP connection, or just the ldap_error
     * message when no diagnostic is available. Servers often put the
     * real cause (bad DN, missing client cert, expired password, etc.)
     * in LDAP_OPT_DIAGNOSTIC_MESSAGE while ldap_error stays at the
     * generic "Invalid credentials", so surfacing both is the difference
     * between "credentials rejected" and knowing WHY they were rejected.
     */
    public static function bindError($connection): string
    {
        $diag = '';
        @ldap_get_option($connection, LDAP_OPT_DIAGNOSTIC_MESSAGE, $diag);

        return ldap_error($connection).($diag !== '' ? ' ('.$diag.')' : '');
    }

    /**
     * Parse and map LDAP attributes based on settings
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @param  $ldapatttibutes
     * @return array|bool
     */
    /**
     * Single source of truth for the LDAP-attribute mapping. Internal
     * key (used across parseAndMapLdapAttributes' $item, the User field
     * writes in applyLdapAttributesToUser, and LdapSync's specific
     * lookups) => LDAP attribute name.
     *
     * Keys mirror the User model's column names (jobtitle,
     * employee_num, first_name, etc.) so downstream code can walk this
     * map and write straight onto a User without translation.
     *
     * `$source` defaults to the persisted Setting model so backend
     * callers (LdapSync, parseAndMapLdapAttributes, applyLdapAttributesToUser)
     * see saved values. The LDAP wizard Livewire component passes
     * `$this` so its live preview reflects in-flight form edits before
     * they're written to Settings. Any object exposing the same
     * `ldap_*` properties works.
     *
     * @param  object|null  $source  Setting-shaped object; defaults to Setting::getSettings()
     * @return array<string, ?string>
     */
    public static function attributeMap(?object $source = null): array
    {
        $source ??= Setting::getSettings();

        return [
            'username' => $source->ldap_username_field,
            'first_name' => $source->ldap_fname_field,
            'last_name' => $source->ldap_lname_field,
            'employee_num' => $source->ldap_emp_num,
            'display_name' => $source->ldap_display_name,
            'email' => $source->ldap_email,
            'phone' => $source->ldap_phone_field,
            'mobile' => $source->ldap_mobile,
            'jobtitle' => $source->ldap_jobtitle,
            'address' => $source->ldap_address,
            'city' => $source->ldap_city,
            'state' => $source->ldap_state,
            'zip' => $source->ldap_zip,
            'country' => $source->ldap_country,
            'department' => $source->ldap_dept,
            'location' => $source->ldap_location,
            'manager' => $source->ldap_manager,
            // LdapSync-only: activated is consumed by the active-
            // directory sync logic in the console command, which reads
            // the mapped LDAP attribute (or AD's useraccountcontrol)
            // and writes the resulting bool onto user.activated.
            // parseAndMapLdapAttributes does not surface it because
            // the first-login path has no use for it (the user just
            // successfully bound to LDAP, they're active by definition).
            'activated' => $source->ldap_active_flag,
        ];
    }

    /**
     * Companion to attributeMap(): internal key => translated human
     * label. Consumed by the LDAP wizard's step-3 preview table and by
     * the settings-page ldaptest results table, so both render
     * "Employee Number" / "Title" / etc. instead of the raw
     * snake_case internal keys. Adding a new key to attributeMap()
     * should be paired with an entry here so it shows up nicely in
     * both places automatically.
     *
     * @return array<string, string>
     */
    public static function attributeLabels(): array
    {
        return [
            'username' => trans('general.username'),
            'first_name' => trans('general.first_name'),
            'last_name' => trans('general.last_name'),
            'employee_num' => trans('general.employee_number'),
            'display_name' => trans('admin/users/table.display_name'),
            'email' => trans('general.email'),
            'phone' => trans('general.phone'),
            'mobile' => trans('admin/users/table.mobile'),
            'jobtitle' => trans('admin/users/table.title'),
            'address' => trans('general.address'),
            'city' => trans('general.city'),
            'state' => trans('general.state'),
            'zip' => trans('general.zip'),
            'country' => trans('general.country'),
            'department' => trans('general.department'),
            'location' => trans('general.location'),
            'manager' => trans('admin/users/table.manager'),
            'activated' => trans('admin/users/table.activated'),
        ];
    }

    public static function parseAndMapLdapAttributes($ldapattributes)
    {
        $item = [];
        foreach (self::attributeMap() as $key => $ldapAttr) {
            // activated is LdapSync's concern. See attributeMap().
            if ($key === 'activated') {
                continue;
            }
            $item[$key] = $ldapAttr ? ($ldapattributes[$ldapAttr][0] ?? '') : '';
        }

        return $item;
    }

    /**
     * Copy the parseAndMapLdapAttributes() output onto a User row.
     * Called by both createUserFromLdap (first login, new user) and
     * LoginController::loginViaLdap (existing user re-login), so the
     * mapping list lives in exactly one place.
     *
     * Each optional field is gated on its LDAP mapping being non-blank
     * so unset mappings don't overwrite existing values with empty
     * strings. Department and Location are firstOrCreate'd only when
     * both the mapping is set and the LDAP payload actually carried a
     * value, so a blank attribute doesn't accrete a nameless row.
     *
     * Manager is intentionally out of scope: LdapSync's manager
     * resolution needs an admin re-bind + LDAP re-query to translate
     * the DN into a Snipe-IT user id, and that's best done in bulk.
     * ldap_import users get their manager populated on the next
     * `snipe-it:ldap-sync` run.
     */
    public static function applyLdapAttributesToUser(User $user, array $ldapAttr): void
    {
        $map = self::attributeMap();

        // Always-written identity fields. These have no per-field gate
        // because Snipe-IT considers username / first name / last name /
        // email important for every user, if a mapping's blank the
        // LDAP payload just gives us an empty string, matching the
        // pre-fix behavior on the create path.
        $user->username = $ldapAttr['username'];
        $user->first_name = $ldapAttr['first_name'];
        $user->last_name = $ldapAttr['last_name'];
        $user->email = $ldapAttr['email'];

        if ($map['display_name'] != '') {
            $user->display_name = $ldapAttr['display_name'];
        }
        if ($map['employee_num'] != '') {
            $user->employee_num = e($ldapAttr['employee_num']);
        }
        if ($map['phone'] != '') {
            $user->phone = $ldapAttr['phone'];
        }
        if ($map['mobile'] != '') {
            $user->mobile = $ldapAttr['mobile'];
        }
        if ($map['jobtitle'] != '') {
            $user->jobtitle = $ldapAttr['jobtitle'];
        }
        if ($map['address'] != '') {
            $user->address = $ldapAttr['address'];
        }
        if ($map['city'] != '') {
            $user->city = $ldapAttr['city'];
        }
        if ($map['state'] != '') {
            $user->state = $ldapAttr['state'];
        }
        if ($map['zip'] != '') {
            $user->zip = $ldapAttr['zip'];
        }
        if ($map['country'] != '') {
            $user->country = $ldapAttr['country'];
        }
        if ($map['department'] != '' && $ldapAttr['department'] !== '') {
            $department = Department::firstOrCreate(['name' => $ldapAttr['department']]);
            $user->department_id = $department->id;
        }
        if ($map['location'] != '' && $ldapAttr['location'] !== '') {
            $location = Location::firstOrCreate(['name' => $ldapAttr['location']]);
            $user->location_id = $location->id;
        }
    }

    /**
     * Create user from LDAP attributes
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return User | bool
     */
    public static function createUserFromLdap($ldapatttibutes, $password)
    {
        $item = self::parseAndMapLdapAttributes($ldapatttibutes);

        if (empty($item['username'])) {
            return false;
        }

        $settings = Setting::getSettings();

        $user = new User;
        self::applyLdapAttributesToUser($user, $item);

        $user->locale = app()->getLocale();
        $user->password = $user->noPassword();
        if ($settings->ldap_pw_sync == '1') {
            $user->password = bcrypt($password);
        }

        $user->activated = 1;
        $user->ldap_import = 1;
        $user->notes = 'Imported on first login from LDAP';

        if (! $user->save()) {
            Log::debug('Could not create user.'.$user->getErrors());
            throw new Exception('Could not create user: '.$user->getErrors());
        }

        // Attach the configured Default Permissions Group to newly-
        // created LDAP users so first-login users land with the same
        // baseline permissions bulk-synced users get. Matches
        // LdapSync::handle()'s post-save group attachment. Skipped when
        // the setting points at a deleted group.
        if ($settings->ldap_default_group) {
            $default = Group::find($settings->ldap_default_group);
            if ($default !== null && ! $user->groups()->where('group_id', $default->id)->exists()) {
                $user->groups()->attach($default->id);
            }
        }

        return $user;
    }

    /**
     * Searches LDAP
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
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
        if ($filter === null) {
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
            // if($count == -1) { //count is -1 means we have to employ paging to query the entire directory
            $ldap_controls = [['oid' => LDAP_CONTROL_PAGEDRESULTS, 'iscritical' => false, 'value' => ['size' => $count == -1 || $count > $page_size ? $page_size : $count, 'cookie' => $cookie]]];
            // }
            $search_results = ldap_search($ldapconn, $base_dn, $filter, $attributes, 0, /* $page_size */ -1, -1, LDAP_DEREF_NEVER, $ldap_controls); // TODO - I hate the @, and I hate that we get a full page even if we ask for 10 records. Can we use an ldap_control?
            Log::debug('LDAP search executed successfully.');
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
                Log::debug('okay, at least one more page to go!!!');
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
