<?php

namespace App\Importer;

use App\Models\Asset;
use App\Models\Company;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

/**
 * This is ONLY used for the User Import. When we are importing users
 * via an Asset/etc import, we use createOrFetchUser() in
 * App\Importer.php. [ALG]
 *
 * Class UserImporter
 */
class UserImporter extends ItemImporter
{
    protected $users;

    protected $send_welcome = false;

    public function __construct($filename)
    {
        parent::__construct($filename);
    }

    protected function handle($row)
    {
        // UserImporter deliberately does NOT call parent::handle(). See the
        // other subclass migrations (LicenseImporter / AssetImporter / etc.)
        // for the same pattern: absent CSV columns stay out of $this->item so
        // update mode preserves the DB value, and present-but-empty cells land
        // as null so update mode clears the DB value. The base sanitize's
        // reject-empty pass is suppressed via the sanitizeItemForStoring
        // override below.
        //
        // Company is handled separately (pipe-separated CSV column, synced via
        // pivot rather than a company_id column) and is intentionally not
        // routed through the shared FK lookup loop.
        $this->item = [];

        if ($this->csvRowHas($row, 'location')) {
            $raw = $this->findCsvMatch($row, 'location');
            $this->item['location_id'] = ($raw !== '') ? $this->createOrFetchLocation($raw) : null;
        }

        if ($this->csvRowHas($row, 'department')) {
            $raw = $this->findCsvMatch($row, 'department');
            $this->item['department_id'] = ($raw !== '') ? $this->createOrFetchDepartment($raw) : null;
        }

        // Manager lookup uses whichever identifier the CSV provides, in
        // priority order: username, employee_num, then first + last name pair.
        // Treat any of those columns being present as the presence signal
        // for the whole lookup so an update can clear the manager_id by
        // leaving all four columns present but empty.
        $managerCsvKeys = ['manager_username', 'manager_employee_num', 'manager_first_name', 'manager_last_name'];
        $managerCsvPresent = false;
        foreach ($managerCsvKeys as $key) {
            if ($this->csvRowHas($row, $key)) {
                $managerCsvPresent = true;
                break;
            }
        }
        if ($managerCsvPresent) {
            $managerUsername = $this->findCsvMatch($row, 'manager_username') ?? '';
            $managerEmployeeNum = $this->findCsvMatch($row, 'manager_employee_num') ?? '';
            $managerFirstName = $this->findCsvMatch($row, 'manager_first_name') ?? '';
            $managerLastName = $this->findCsvMatch($row, 'manager_last_name') ?? '';
            $anyPopulated = ($managerUsername !== '' || $managerEmployeeNum !== '' || $managerFirstName !== '' || $managerLastName !== '');
            $this->item['manager_id'] = $anyPopulated
                ? $this->fetchManager($managerUsername, $managerEmployeeNum, $managerFirstName, $managerLastName)
                : null;
        }

        // Straight string fields. Each column absent from the CSV stays out
        // of $this->item; present-but-empty lands as null so sanitize can
        // pass it through and Eloquent clears the DB value on update.
        $this->setItemFromCsvIfPresent($row, 'username');
        $this->setItemFromCsvIfPresent($row, 'display_name');
        $this->setItemFromCsvIfPresent($row, 'first_name');
        $this->setItemFromCsvIfPresent($row, 'last_name');
        $this->setItemFromCsvIfPresent($row, 'email');
        $this->setItemFromCsvIfPresent($row, 'gravatar');
        $this->setItemFromCsvIfPresent($row, 'phone', 'phone_number');
        $this->setItemFromCsvIfPresent($row, 'mobile', 'mobile_number');
        $this->setItemFromCsvIfPresent($row, 'website');
        $this->setItemFromCsvIfPresent($row, 'jobtitle');
        $this->setItemFromCsvIfPresent($row, 'address');
        $this->setItemFromCsvIfPresent($row, 'city');
        $this->setItemFromCsvIfPresent($row, 'state');
        $this->setItemFromCsvIfPresent($row, 'country');
        $this->setItemFromCsvIfPresent($row, 'zip');
        $this->setItemFromCsvIfPresent($row, 'employee_num');
        $this->setItemFromCsvIfPresent($row, 'notes');
        $this->sanitizeAvatarFromCsv($row);
        $this->setItemFromCsvIfPresent($row, 'scim_externalid');
        $this->setItemFromCsvIfPresent($row, 'locale');
        $this->setItemFromCsvIfPresent($row, 'ldap_import');

        // Boolean flags. Present-and-empty means 0 (fetchHumanBoolean returns
        // 0 for empty); present-with-value means the parsed 1/0. Absent
        // means don't touch the DB value on update.
        foreach (['activated', 'remote', 'vip', 'autoassign_licenses'] as $flag) {
            if ($this->csvRowHas($row, $flag)) {
                $raw = $this->findCsvMatch($row, $flag);
                $this->item[$flag] = ($this->fetchHumanBoolean($raw) == 1) ? '1' : 0;
            }
        }

        foreach (['start_date', 'end_date'] as $dateField) {
            if ($this->csvRowHas($row, $dateField)) {
                $raw = $this->findCsvMatch($row, $dateField);
                if ($raw !== '') {
                    $this->item[$dateField] = $raw;
                    $this->item[$dateField] = $this->parseOrNullDate($dateField);
                } else {
                    $this->item[$dateField] = null;
                }
            }
        }

        // id is used only for identity matching (see createUserIfNotExists),
        // never written to the DB. Not fillable on User either, so sanitize's
        // fillable filter would drop it - stash separately.
        $csvId = $this->csvRowHas($row, 'id') ? trim((string) $this->findCsvMatch($row, 'id')) : '';

        // display_name coerces empty to null explicitly - the DB column is
        // nullable and users expect empty CSV to mean "no display name"
        // (falls back to first_name + last_name at read time).
        if (array_key_exists('display_name', $this->item) && $this->item['display_name'] === '') {
            $this->item['display_name'] = null;
        }

        $this->createUserIfNotExists($row, $csvId);
    }

    /**
     * Store the imported avatar cell defensively. Local filenames are reduced
     * to their basename, mirroring how AssetImporter and AccessoryImporter
     * treat their `image` column. Absolute http(s) URLs are preserved so
     * OAuth-sourced avatars (Google, Microsoft, Gravatar) still render
     * correctly when a user record is re-imported, but a URL carrying a
     * `..` segment in its path is rejected.
     */
    private function sanitizeAvatarFromCsv(array $row): void
    {
        if (! $this->csvRowHas($row, 'avatar')) {
            return;
        }

        $raw = (string) $this->findCsvMatch($row, 'avatar');

        if ($raw === '') {
            $this->item['avatar'] = null;

            return;
        }

        if (preg_match('#^https?://#i', $raw) === 1) {
            $parsed = parse_url($raw);
            $path = is_array($parsed) ? ($parsed['path'] ?? '') : '';

            if (!is_array($parsed) || empty($parsed['host']) || str_contains($path, '..')) {
                $this->item['avatar'] = null;

                return;
            }

            $this->item['avatar'] = $raw;

            return;
        }

        $this->item['avatar'] = basename($raw);
    }

    /**
     * Parse a pipe-separated company column value into an array of company IDs,
     * creating companies that do not yet exist. Returns an empty array when the
     * raw value is blank (so callers can treat that as "don't change").
     *
     * @param  string  $raw  Raw cell value, e.g. "Acme Corp|Widget Inc"
     * @return int[]
     */
    private function resolveCompanyIds(string $raw): array
    {
        if ($raw === '') {
            return [];
        }

        $ids = [];
        foreach (array_filter(array_map('trim', explode('|', $raw))) as $name) {
            $id = $this->createOrFetchCompany($name);
            if ($id) {
                $ids[] = (int) $id;
            }
        }

        return Company::getIdsForCurrentUser($ids);
    }

    /**
     * Create a user if a duplicate does not exist.
     *
     * @todo Investigate how this should interact with Importer::createOrFetchUser
     *
     * @author Daniel Melzter
     *
     * @since 4.0
     */
    public function createUserIfNotExists(array $row, string $csvId = '')
    {
        // Resolve pipe-separated company names (e.g. "Acme Corp|Widget Inc") into IDs.
        // Company membership is managed via the pivot; the legacy company_id column
        // mirror is synced separately via syncLegacyCompanyIdMirror() below.
        $companyRaw = $this->csvRowHas($row, 'company')
            ? trim((string) $this->findCsvMatch($row, 'company'))
            : '';
        $companyIds = $this->resolveCompanyIds($companyRaw);

        // Auto-generate a username from first + last name if the CSV did
        // not provide one (either the column was absent or the value was
        // empty). Requires first_name + last_name to have been populated
        // by the CSV; otherwise the generated username will just be the
        // configured format with empty inputs.
        $usernameProvided = array_key_exists('username', $this->item) && $this->item['username'] !== '' && $this->item['username'] !== null;
        if (! $usernameProvided) {
            $firstName = $this->item['first_name'] ?? '';
            $lastName = $this->item['last_name'] ?? '';
            $user_full_name = trim($firstName.' '.$lastName);
            $user_formatted_array = User::generateFormattedNameFromFullName($user_full_name, Setting::getSettings()->username_format);
            $this->item['username'] = $user_formatted_array['username'];
        }

        // Identity match. Prefer a numeric ID from the CSV over the username
        // lookup, so a re-import against a renamed user still lands on the
        // right record. Username is required for the fallback and is
        // guaranteed populated above (either from the CSV or auto-generated).
        if ($csvId !== '' && is_numeric($csvId)) {
            $user = User::find((int) $csvId);
        } else {
            $user = User::where('username', $this->item['username'])->first();
        }

        if ($user) {
            if (! $this->updating) {
                Log::debug('A matching User '.$this->item['username'].' already exists.  ');
                $this->recordSkipped();

                return;
            }

            $this->log('Updating User');

            // CLI imports run unauthenticated and are fully trusted; only restrict web-initiated imports.
            // Note: unset must target $this->item, not the model - sanitizeItemForUpdating() reads from $this->item.
            if (Auth::check() && (! Auth::user()->hasAccess('users.edit') || ! Gate::allows('canEditAuthFields', $user))) {
                // GATED_AUTH_FIELDS is the shared list across the API,
                // web-UI, and importer paths. The importer naturally
                // filters out fields that are not present in the CSV
                // via array_intersect, so `permissions` (which the
                // importer never processes) drops out on its own.
                $deniedAuthFields = array_values(array_intersect(User::GATED_AUTH_FIELDS, array_keys($this->item)));
                foreach ($deniedAuthFields as $field) {
                    unset($this->item[$field]);
                }
                if (! empty($deniedAuthFields)) {
                    // Surface the skip in the import summary rather than
                    // silently persisting a partial row. Halting the whole
                    // import on the first affected row would be worse UX.
                    $this->log(sprintf(
                        'Skipped auth fields (%s) on user %s: caller lacks canEditAuthFields on this target.',
                        implode(', ', $deniedAuthFields),
                        $user->username,
                    ));
                }
            }

            if (! $this->validateFmcsLocation($this->item['location_id'] ?? null, $companyIds)) {
                $loc = Location::find($this->item['location_id']);
                $msg = trans('validation.fmcs_location', [
                    'location' => $loc?->name ?? $this->item['location_id'],
                    'location_company' => $loc?->company?->name ?? trans('general.unassigned'),
                ]);
                $this->log($msg);
                $this->addErrorToBag($user, 'location_id', $msg);
                $this->recordErrored();

                return;
            }

            $user->update($this->sanitizeItemForUpdating($user));

            // Why do we have to do this twice? Update should
            $user->save();

            // Sync company pivot when companies were specified in this row.
            // GHSA-wwp4-qx8p-62g8: route through the FMCS-safe helper so a
            // scoped operator running the interactive importer cannot strip
            // the target's memberships in companies the operator cannot see.
            // The helper short-circuits to a raw sync when the editor is
            // null (CLI-run importer), superuser, or FMCS is off.
            if (! empty($companyIds)) {
                $user->syncCompaniesPreservingInvisibleTo(Auth::user(), $companyIds);
            }

            // Update the location of any assets checked out to this user
            Asset::where('assigned_type', User::class)
                ->where('assigned_to', $user->id)
                ->update(['location_id' => $user->location_id]);

            $this->recordUpdated();

            return;
        }

        // With FMCS enabled, the scoped lookup above only sees users in the current user's companies.
        // If the username exists in another company it would appear as "not found" and fall through
        // to create - but usernames are unique system-wide, so we must skip instead.
        if (Auth::check() && Company::isFullMultipleCompanySupportEnabled()) {
            if (User::withoutGlobalScopes()->where('username', $this->item['username'])->exists()) {
                $this->log('Skipping '.$this->item['username'].': username belongs to a user outside your company scope.');
                $this->recordSkipped();

                return;
            }
        }

        // This needs to be applied after the update logic, otherwise we'll overwrite user passwords
        // Issue #5408
        $this->item['password'] = $this->tempPassword;

        $this->log('No matching user, creating one');

        // Floater-mode escalation guard (#19200). See User::canGrantFloaterStatus.
        if (Auth::check() && empty($companyIds) && ! auth()->user()->canGrantFloaterStatus()) {
            $msg = trans('admin/users/general.cannot_make_floater');
            $this->log('Skipping '.$this->item['username'].': '.$msg);
            $this->addErrorToBag(new User, 'company_id', $msg);
            $this->recordErrored();

            return;
        }

        if (! $this->validateFmcsLocation($this->item['location_id'] ?? null, $companyIds)) {
            $msg = trans('validation.fmcs_location', [
                'location' => Location::find($this->item['location_id'])?->name ?? $this->item['location_id'],
                'location_company' => Location::find($this->item['location_id'])?->company?->name ?? trans('general.unassigned'),
            ]);
            $this->log($msg);
            $this->addErrorToBag(new User, 'location_id', $msg);
            $this->recordErrored();

            return;
        }

        // On create, default absent boolean flags to 0 to preserve backwards-
        // compatible behavior. Under the old unconditional-set pattern these
        // always got fetchHumanBoolean('') = 0 even when the CSV column was
        // absent. Update mode still respects "absent means preserve," so we
        // only apply the default on the create path.
        foreach (['activated', 'remote', 'vip', 'autoassign_licenses'] as $flag) {
            if (! array_key_exists($flag, $this->item)) {
                $this->item[$flag] = 0;
            }
        }

        $user = new User;
        $user->created_by = $this->created_by;

        $user->fill($this->sanitizeItemForStoring($user));

        // TODO - check for gate here I guess

        if ($user->save()) {
            $this->log('User '.$user->username.' was created');
            $this->recordCreated();

            // Sync all resolved companies to the pivot. For single-company rows the
            // User::created event already added company_id, and sync here is idempotent
            // for that case and adds any additional companies for multi-company rows.
            // GHSA-wwp4-qx8p-62g8: same reasoning as the update branch above.
            // Route through the FMCS-safe helper so a scoped operator running
            // the interactive importer cannot strip the target's memberships
            // in companies the operator cannot see. Helper short-circuits to
            // a raw sync when the editor is null (CLI), superuser, or FMCS
            // is off.
            if (! empty($companyIds)) {
                $user->syncCompaniesPreservingInvisibleTo(Auth::user(), $companyIds);
            }

            if (($user->email) && ($user->activated == '1')) {

                if ($this->send_welcome) {

                    try {
                        $user->notify(new WelcomeNotification($user));
                    } catch (\Exception $e) {
                        Log::warning('Could not send welcome notification for user: '.$e->getMessage());
                    }

                }

            }
            $user = null;
            $this->item = [];

            return;
        }

        $this->recordErrored();
        $this->logError($user, 'User');
    }

    public function sendWelcome($send = true)
    {
        $this->send_welcome = $send;
    }

    /**
     * Returns true when the given location is compatible with the given company IDs under
     * FMCS location scoping rules. Mirrors the fmcs_location custom validator.
     *
     * @param  int[]  $companyIds
     */
    private function validateFmcsLocation(?int $locationId, array $companyIds): bool
    {
        $settings = Setting::getSettings();

        if ($settings->full_multiple_companies_support != '1' || $settings->scope_locations_fmcs != '1') {
            return true;
        }

        if (empty($companyIds) || ! $locationId) {
            return true;
        }

        $location = Location::find($locationId);

        if (! $location) {
            return true;
        }

        if ($location->company_id === null) {
            return (bool) $settings->null_company_is_floater;
        }

        return in_array($location->company_id, $companyIds);
    }
}
