<?php

namespace App\Console\Commands;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Group;
use App\Models\Ldap;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LdapSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:ldap-sync {--location=} {--location_id=*} {--base_dn=} {--filter=} {--delete} {--summary} {--json_summary}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command line LDAP sync';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        // If LDAP enabled isn't set to 1 (ldap_enabled!=1) then we should cut this short immediately without going any further
        if (Setting::getSettings()->ldap_enabled != '1') {
            $this->error('LDAP is not enabled. Aborting. See Settings > LDAP to enable it.');
            exit();
        }

        // Tag every action_log written during this run (direct writes
        // and the observer-driven writes User saves trigger) as `ldap`
        // so the activity report distinguishes LDAP-created entries
        // from gui / api / sync-adapter origins.
        return Actionlog::withActionSource('ldap', fn () => $this->runSync());
    }

    private function runSync()
    {
        ini_set('max_execution_time', config('app.ldap_time_limit')); // 600 seconds = 10 minutes
        ini_set('memory_limit', config('app.ldap_memory_limit'));

        // Single source of truth for internal-key => LDAP-attribute-name
        // lives on the Ldap model so parseAndMapLdapAttributes and this
        // command can't drift. Used here for the LDAP query attribute
        // list plus a handful of specific-lookup gates (activated,
        // manager, location, username) that only LdapSync needs.
        $ldap_map = Ldap::attributeMap();

        $ldap_default_group = Setting::getSettings()->ldap_default_group;
        $search_base = Setting::getSettings()->ldap_base_dn;

        try {
            $ldapconn = Ldap::connectToLdap();
            Ldap::bindAdminToLdap($ldapconn);
        } catch (\Exception $e) {
            if ($this->option('json_summary')) {
                $json_summary = ['error' => true, 'error_message' => $e->getMessage(), 'summary' => []];
                $this->info(json_encode($json_summary));
            }
            Log::info($e);

            return [];
        }

        $summary = [];
        $seen_ldap_usernames = [];

        try {

            /**
             * if a location ID has been specified, use that OU
             */
            if ($this->option('location_id')) {

                foreach ($this->option('location_id') as $location_id) {
                    $location_ou = Location::where('id', '=', $location_id)->value('ldap_ou');
                    $search_base = $location_ou;
                    Log::debug('Importing users from specified location OU: \"'.$search_base.'\".');
                }
            }

            /**
             *  if a manual base DN has been specified, use that. Allow the Base DN to override
             *  even if there's a location-based DN - if you picked it, you must have picked it for a reason.
             */
            if ($this->option('base_dn') != '') {
                $search_base = $this->option('base_dn');
                Log::debug('Importing users from specified base DN: \"'.$search_base.'\".');
            }

            /**
             * If a filter has been specified, use that, otherwise default to null
             */
            if ($this->option('filter') != '') {
                $filter = $this->option('filter');
            } else {
                $filter = null;
            }

            /**
             * We only need to request the LDAP attributes that we process
             */
            $attributes = array_values(array_filter($ldap_map));

            // empty() rather than is_null() because the LDAP wizard
            // (App\Livewire\LdapSettings) persists an unset mapping as ''
            // whereas the legacy form path routes through
            // ConvertEmptyStringsToNull and persists it as null.
            if (Setting::getSettings()->is_ad === 1 && empty($ldap_map['activated'])) {
                $attributes[] = 'useraccountcontrol';
            }

            $results = Ldap::findLdapUsers($search_base, -1, $filter, $attributes);

        } catch (\Exception $e) {
            if ($this->option('json_summary')) {
                $json_summary = ['error' => true, 'error_message' => $e->getMessage(), 'summary' => []];
                $this->info(json_encode($json_summary));
            }
            Log::info($e);

            return [];
        }

        /* Determine which location to assign users to by default. */
        $default_location = null;
        if ($this->option('location') != '') {
            if ($default_location = Location::where('name', '=', $this->option('location'))->first()) {
                Log::debug('Location name '.$this->option('location').' passed');
                Log::debug('Importing to '.$default_location->name.' ('.$default_location->id.')');
            }

        } elseif ($this->option('location_id')) {
            // TODO - figure out how or why this is an array?
            foreach ($this->option('location_id') as $location_id) {
                if ($default_location = Location::where('id', '=', $location_id)->first()) {
                    Log::debug('Location ID '.$location_id.' passed');
                    Log::debug('Importing to '.$default_location->name.' ('.$default_location->id.')');
                }

            }
        }
        if (! isset($default_location)) {
            Log::debug('That location is invalid or a location was not provided, so no location will be assigned by default.');
        }

        /* Process locations with explicitly defined OUs, if doing a full import. */
        if ($this->option('base_dn') == '' && $this->option('filter') == '') {
            // Retrieve locations with a mapped OU, and sort them from the shallowest to deepest OU (see #3993)
            $ldap_ou_locations = Location::where('ldap_ou', '!=', '')->get()->toArray();
            $ldap_ou_lengths = [];

            foreach ($ldap_ou_locations as $ou_loc) {
                $ldap_ou_lengths[] = strlen($ou_loc['ldap_ou']);
            }

            array_multisort($ldap_ou_lengths, SORT_ASC, $ldap_ou_locations);

            if (count($ldap_ou_locations) > 0) {
                Log::debug('Some locations have special OUs set. Locations will be automatically set for users in those OUs.');
            }

            // Inject location information fields
            for ($i = 0; $i < $results['count']; $i++) {
                $results[$i]['ldap_location_override'] = false;
                $results[$i]['location_id'] = null;
            }

            // Grab subsets based on location-specific DNs, and overwrite location for these users.
            foreach ($ldap_ou_locations as $ldap_loc) {
                try {
                    $location_users = Ldap::findLdapUsers($ldap_loc['ldap_ou']);
                } catch (\Exception $e) { // TODO: this is stolen from line 77 or so above
                    if ($this->option('json_summary')) {
                        $json_summary = ['error' => true, 'error_message' => trans('admin/users/message.error.ldap_could_not_search').' Location: '.$ldap_loc['name'].' (ID: '.$ldap_loc['id'].') cannot connect to "'.$ldap_loc['ldap_ou'].'" - '.$e->getMessage(), 'summary' => []];
                        $this->info(json_encode($json_summary));
                    }
                    Log::info($e);

                    return [];
                }
                $usernames = [];
                for ($i = 0; $i < $location_users['count']; $i++) {
                    if (array_key_exists($ldap_map['username'], $location_users[$i])) {
                        $location_users[$i]['ldap_location_override'] = true;
                        $location_users[$i]['location_id'] = $ldap_loc['id'];
                        $usernames[] = $location_users[$i][$ldap_map['username']][0];
                    }
                }

                // Delete located users from the general group.
                foreach ($results as $key => $generic_entry) {
                    if ((is_array($generic_entry)) && (array_key_exists($ldap_map['username'], $generic_entry))) {
                        if (in_array($generic_entry[$ldap_map['username']][0], $usernames)) {
                            unset($results[$key]);
                        }
                    }
                }

                $global_count = $results['count'];
                $results = array_merge($location_users, $results);
                $results['count'] = $global_count;
            }
        }

        $manager_cache = [];

        if ($ldap_default_group != null) {

            $default = Group::find($ldap_default_group);
            if (! $default) {
                $ldap_default_group = null; // un-set the default group if that group doesn't exist
            }

        }

        // Assign the mapped LDAP attributes for each user to the Snipe-IT user fields
        for ($i = 0; $i < $results['count']; $i++) {
            // parseAndMapLdapAttributes is the shared parser used by the
            // first-login create path too, so the two flows can't drift
            // on field names / lookup shape. The two OU-override keys
            // (ldap_location_override, location_id) are LdapSync-only,
            // injected earlier by the OU sweep at line 191 or so, so we
            // stitch them back on here.
            $item = Ldap::parseAndMapLdapAttributes($results[$i]);
            $item['ldap_location_override'] = $results[$i]['ldap_location_override'] ?? null;
            $item['location_id'] = $results[$i]['location_id'] ?? null;

            $user = self::findLocalUserForLdapUsername((string) $item['username'], withTrashed: true);
            if (! empty($item['username'])) {
                $seen_ldap_usernames[] = $item['username'];
            }
            if ($user) {
                if ($user->trashed()) {
                    $user->restore();
                }
                // Updating an existing user.
                $item['createorupdate'] = 'updated';
            } else {
                // Creating a new user.
                $user = new User;
                $user->password = $user->noPassword();
                $user->locale = app()->getLocale();
                $user->activated = 1; // newly created users can log in by default, unless AD's UAC is in use, or an active flag is set (below)
                $item['createorupdate'] = 'created';
            }

            // Shared field-write path with the first-login create flow.
            // Handles every mapped scalar field, plus Department and
            // Location firstOrCreate for the LDAP-derived values. The
            // three LdapSync-only concerns (manager LDAP re-query,
            // activated / UAC, OU location override) are handled
            // inline below because they don't apply to the first-login
            // path.
            Ldap::applyLdapAttributesToUser($user, $item);

            if ($ldap_map['manager'] != null) {
                if ($item['manager'] != null) {
                    // Check Cache first
                    if (isset($manager_cache[$item['manager']])) {
                        // found in cache; use that and avoid extra lookups
                        $user->manager_id = $manager_cache[$item['manager']];
                    } else {
                        // Get the LDAP Manager
                        try {
                            $ldap_manager = Ldap::findLdapUsers($item['manager'], -1, $this->option('filter'));
                        } catch (\Exception $e) {
                            Log::warning('Manager lookup caused an exception: '.$e->getMessage().'. Falling back to direct username lookup');
                            // Hail-mary for Okta manager 'shortnames' - will only work if
                            // Okta configuration is using full email-address-style usernames
                            $ldap_manager = [
                                'count' => 1,
                                0 => [
                                    $ldap_map['username'] => [$item['manager']],
                                ],
                            ];
                        }

                        $add_manager_to_cache = true;
                        if ($ldap_manager['count'] > 0) {
                            try {
                                // Get the Manager's username
                                // PHP LDAP returns every LDAP attribute as an array, and 90% of the time it's an array of just one item. But, hey, it's an array.
                                $ldapManagerUsername = $ldap_manager[0][$ldap_map['username']][0];

                                $ldap_manager = self::findLocalUserForLdapUsername((string) $ldapManagerUsername);

                                if ($ldap_manager && isset($ldap_manager->id)) {
                                    // Link user to manager id.
                                    $user->manager_id = $ldap_manager->id;
                                }
                            } catch (\Exception $e) {
                                $add_manager_to_cache = false;
                                \Log::warning('Handling ldap manager '.$item['manager'].' caused an exception: '.$e->getMessage().'. Continuing synchronization.');
                            }
                        }
                        if ($add_manager_to_cache) {
                            $manager_cache[$item['manager']] = $ldap_manager && isset($ldap_manager->id) ? $ldap_manager->id : null; // Store results in cache, even if 'failed'
                        }

                    }
                }
            }

            // Sync activated state for Active Directory.
            if (! empty($ldap_map['activated'])) { // IF we have an 'active' flag set....
                // ....then *most* things that are truthy will activate the user. Anything falsey will deactivate them.
                // (Specifically, we don't handle a value of '0.0' correctly)
                $raw_value = @$results[$i][$ldap_map['activated']][0];
                $filter_var = filter_var($raw_value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

                $boolean_cast = (bool) $raw_value;

                if (Setting::getSettings()->ldap_invert_active_flag === 1) {
                    // Because ldap_active_flag is set, if filter_var is true or boolean_cast is true, then user is suspended
                    $user->activated = ! ($filter_var ?? $boolean_cast);
                } else {
                    $user->activated = $filter_var ?? $boolean_cast; // if filter_var() was true or false, use that. If it's null, use the $boolean_cast
                }

            } elseif (array_key_exists('useraccountcontrol', $results[$i])) {
                // ....otherwise, (ie if no 'active' LDAP flag is defined), IF the UAC setting exists,
                // ....then use the UAC setting on the account to determine can-log-in vs. cannot-log-in

                /* The following is _probably_ the correct logic, but we can't use it because
                    some users may have been dependent upon the previous behavior, and this
                    could cause additional access to be available to users they don't want
                    to allow to log in.

                 $useraccountcontrol = $results[$i]['useraccountcontrol'][0];
                 if(
                     // based on MS docs at: https://support.microsoft.com/en-us/help/305144/how-to-use-useraccountcontrol-to-manipulate-user-account-properties
                     ($useraccountcontrol & 0x200) && // is a NORMAL_ACCOUNT
                     !($useraccountcontrol & 0x02) && // *and* _not_ ACCOUNTDISABLE
                     !($useraccountcontrol & 0x10)    // *and* _not_ LOCKOUT
                 ) {
                     $user->activated = 1;
                 } else {
                     $user->activated = 0;
                 } */
                $enabled_accounts = [
                    '512',    //     0x200 NORMAL_ACCOUNT
                    '544',    //     0x220 NORMAL_ACCOUNT, PASSWD_NOTREQD
                    '66048',  //   0x10200 NORMAL_ACCOUNT, DONT_EXPIRE_PASSWORD
                    '66080',  //   0x10220 NORMAL_ACCOUNT, PASSWD_NOTREQD, DONT_EXPIRE_PASSWORD
                    '262656', //   0x40200 NORMAL_ACCOUNT, SMARTCARD_REQUIRED
                    '262688', //   0x40220 NORMAL_ACCOUNT, PASSWD_NOTREQD, SMARTCARD_REQUIRED
                    '328192', //   0x50200 NORMAL_ACCOUNT, SMARTCARD_REQUIRED, DONT_EXPIRE_PASSWORD
                    '328224', //   0x50220 NORMAL_ACCOUNT, PASSWD_NOT_REQD, SMARTCARD_REQUIRED, DONT_EXPIRE_PASSWORD
                    '4194816', //  0x400200 NORMAL_ACCOUNT, DONT_REQ_PREAUTH
                    '4260352', // 0x410200 NORMAL_ACCOUNT, DONT_EXPIRE_PASSWORD, DONT_REQ_PREAUTH
                    '1049088', // 0x100200 NORMAL_ACCOUNT, NOT_DELEGATED
                    '1114624', // 0x110200 NORMAL_ACCOUNT, DONT_EXPIRE_PASSWORD, NOT_DELEGATED,
                ];
                $user->activated = (in_array($results[$i]['useraccountcontrol'][0], $enabled_accounts)) ? 1 : 0;

                // If we're not using AD, and there isn't an activated flag set, activate all users
            } /* implied 'else' here - leave the $user->activated flag alone. Newly-created accounts will be active.
            already-existing accounts will be however the administrator has set them */

            // Location resolution: applyLdapAttributesToUser above has
            // already written location_id from the LDAP payload when a
            // value was present. This block layers on the two overrides
            // it doesn't know about: the OU-based override wins over
            // everything when set, and the --location CLI flag fills in
            // when neither the OU override nor an LDAP-derived location
            // applied to this run.
            $ldapProvidedLocation = $ldap_map['location'] !== null && $item['location'] !== '';

            if ($item['ldap_location_override'] == true) {
                $user->location_id = $item['location_id'];
            } elseif (! $ldapProvidedLocation && ! empty($default_location)) {
                $user->location_id = is_array($default_location)
                    ? $default_location['id']
                    : $default_location->id;
            }
            // TODO - should we be NULLING locations when neither the OU
            // override, the LDAP payload, nor --location produced a
            // location for this user? Currently we leave whatever they
            // had, matching the pre-refactor behavior. Changing that
            // could clobber a location an admin set by hand.

            $user->ldap_import = 1;

            $errors = '';

            if ($user->save()) {
                Ldap::applyLdapCompanyToUser($user, $item);
                $item['id'] = $user->id;
                $item['note'] = $item['createorupdate'];
                $item['status'] = 'success';
                if ($item['createorupdate'] === 'created' && $ldap_default_group) {
                    // Check if the relationship already exists
                    if (! $user->groups()->where('group_id', $ldap_default_group)->exists()) {
                        $user->groups()->attach($ldap_default_group);
                        $user->logGroupAttached((int) $ldap_default_group);
                    }
                }

                // updates assets location based on user's location
                if ($user->wasChanged('location_id')) {
                    foreach ($user->assets as $asset) {
                        $asset->location_id = $user->location_id;
                        // TODO: somehow add note? "Asset Location Changed because of thing"
                        $asset->save();
                    }
                }

            } else {
                foreach ($user->getErrors()->getMessages() as $key => $err) {
                    $errors .= $err[0];
                }
                $item['note'] = $errors;
                $item['status'] = 'error';
            }

            array_push($summary, $item);
        }

        // Optionally soft-delete LDAP-imported users that are no longer present in LDAP.
        // users with assests etc. are not deletable and skipped
        if ($this->option('delete')) {
            $missing_ldap_users = User::where('ldap_import', 1);
            $missing_ldap_users = $missing_ldap_users->whereNotIn('username', $seen_ldap_usernames);
            $missing_ldap_users = $missing_ldap_users->get();

            foreach ($missing_ldap_users as $missing_user) {
                // Match the rule a manual "delete user" click uses. We
                // can't call User::isDeletable() directly here because
                // it wraps a Gate::allows('delete', $user) check that
                // needs an authenticated web-session user, and this
                // command runs from cron with no such user
                $is_deletable = $missing_user->hasNoAssignmentBlockers();

                $missing_item = [
                    'id' => $missing_user->id,
                    'username' => $missing_user->username,
                    'first_name' => $missing_user->first_name,
                    'last_name' => $missing_user->last_name,
                    'email' => $missing_user->email,
                    'createorupdate' => 'skipped',
                    'status' => 'info',
                    'deletable' => $is_deletable,
                    'note' => $is_deletable ? 'missing from LDAP' : 'missing from LDAP, but not deletable',
                ];

                if ($is_deletable) {
                    $missing_user->delete();
                    $missing_item['createorupdate'] = 'deleted';
                    $missing_item['status'] = 'success';
                    $missing_item['note'] = 'deleted_missing_from_ldap';
                }

                $summary[] = $missing_item;
            }
        }

        if ($this->option('summary')) {
            $rows = array_map(fn ($row) => [
                $row['username'] ?? '',
                trim(($row['first_name'] ?? '').' '.($row['last_name'] ?? '')),
                strtoupper($row['createorupdate'] ?? ''),
                strtoupper($row['status'] ?? ''),
                $row['note'] ?? '',
            ], $summary);

            $this->table(
                ['Username', 'Name', 'Action', 'Status', 'Note'],
                $rows,
            );
        } elseif ($this->option('json_summary')) {
            $json_summary = ['error' => false, 'error_message' => '', 'summary' => $summary]; // hardcoding the error to false and the error_message to blank seems a bit weird
            $this->info(json_encode($json_summary));
        } else {
            return $summary;
        }
    }

    /**
     * Resolve a local user by the LDAP-supplied username. See GHSA-97h5-f5j2-6v99.
     */
    public static function findLocalUserForLdapUsername(string $username, bool $withTrashed = false): ?User
    {
        if ($username === '') {
            return null;
        }

        $query = $withTrashed ? User::withTrashed() : User::query();
        $user = $query->where('username', $username)->first();

        return User::verifyExactUsernameMatch($user, $username);
    }
}
