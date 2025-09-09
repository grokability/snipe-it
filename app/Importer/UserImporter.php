<?php

namespace App\Importer;

use App\Models\Asset;
use App\Models\Department;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

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
        parent::handle($row);
        $this->createUserIfNotExists($row);
    }

    /**
     * Create a user if a duplicate does not exist.
     * @todo Investigate how this should interact with Importer::createOrFetchUser
     *
     * @author Daniel Melzter
     * @since 4.0
     * @param array $row
     */
    public function createUserIfNotExists(array $row)
    {
        // Pull the records from the CSV to determine their values
        $this->item['id'] = trim($this->findCsvMatch($row, 'id'));
        $this->item['username'] = trim($this->findCsvMatch($row, 'username'));
        $this->item['display_name'] = trim($this->findCsvMatch($row, 'display_name'));
        $this->item['first_name'] = trim($this->findCsvMatch($row, 'first_name'));
        $this->item['last_name'] = trim($this->findCsvMatch($row, 'last_name'));
        $this->item['email'] = trim($this->findCsvMatch($row, 'email'));
        $this->item['gravatar'] = trim($this->findCsvMatch($row, 'gravatar'));
        $this->item['phone'] = trim($this->findCsvMatch($row, 'phone_number'));
        $this->item['mobile'] = trim($this->findCsvMatch($row, 'mobile_number'));
        $this->item['website'] = trim($this->findCsvMatch($row, 'website'));
        $this->item['jobtitle'] = trim($this->findCsvMatch($row, 'jobtitle'));
        $this->item['address'] = trim($this->findCsvMatch($row, 'address'));
        $this->item['city'] = trim($this->findCsvMatch($row, 'city'));
        $this->item['state'] = trim($this->findCsvMatch($row, 'state'));
        $this->item['country'] = trim($this->findCsvMatch($row, 'country'));
        $this->item['start_date'] = trim($this->findCsvMatch($row, 'start_date'));
        $this->item['end_date'] = trim($this->findCsvMatch($row, 'end_date'));
        $this->item['zip'] = trim($this->findCsvMatch($row, 'zip'));
        $this->item['activated'] = ($this->fetchHumanBoolean(trim($this->findCsvMatch($row, 'activated'))) == 1) ? '1' : 0;
        $this->item['employee_num'] = trim($this->findCsvMatch($row, 'employee_num'));
        $this->item['department_id'] = trim($this->createOrFetchDepartment(trim($this->findCsvMatch($row, 'department'))));
        $this->item['manager_id'] = $this->fetchManager(trim($this->findCsvMatch($row, 'manager_username')), trim($this->findCsvMatch($row, 'manager_employee_num')), trim($this->findCsvMatch($row, 'manager_first_name')), trim($this->findCsvMatch($row, 'manager_last_name')));
        $this->item['remote'] = ($this->fetchHumanBoolean(trim($this->findCsvMatch($row, 'remote'))) == 1 ) ? '1' : 0;
        $this->item['vip'] = ($this->fetchHumanBoolean(trim($this->findCsvMatch($row, 'vip'))) ==1 ) ? '1' : 0;
        $this->item['autoassign_licenses'] = ($this->fetchHumanBoolean(trim($this->findCsvMatch($row, 'autoassign_licenses'))) ==1 ) ? '1' : 0;

        $this->handleEmptyStringsForDates();

        $user_department = trim($this->findCsvMatch($row, 'department'));
        if ($this->shouldUpdateField($user_department)) {
            $this->item['department_id'] = $this->createOrFetchDepartment($user_department);
        }

        if (is_null($this->item['username']) || $this->item['username'] == "") {
            $user_full_name = $this->item['first_name'] . ' ' . $this->item['last_name'];
            $user_formatted_array = User::generateFormattedNameFromFullName($user_full_name, Setting::getSettings()->username_format);
            $this->item['username'] = $user_formatted_array['username'];
        }


        // Check if a numeric ID was passed. If it does, use that above all else.
        if ((array_key_exists('id', $this->item) && ($this->item['id'] != "") && (is_numeric($this->item['id']))))  {
            $user = User::find($this->item['id']);
        } else {
            $user = User::where('username', $this->item['username'])->first();
        }

        if ($user) {

            // If the user does not want to update existing values, only add new ones, bail out
            if (! $this->updating) {
                Log::debug('A matching User '.$this->item['name'].' already exists.  ');
                return;
            }

            $this->log('Updating User');

            // Todo - check that this works
            if (!Gate::allows('canEditAuthFields', $user)) {
                unset($user->username);
                unset($user->email);
                unset($user->password);
                unset($user->activated);
            }

            $user->update($this->sanitizeItemForUpdating($user));

            // Why do we have to do this twice? Update should
            $user->save();

            // Update the location of any assets checked out to this user
            Asset::where('assigned_type', User::class)
                ->where('assigned_to', $user->id)
                ->update(['location_id' => $user->location_id]);
            
            // Log::debug('UserImporter.php Updated User ' . print_r($user, true));
            return;
        }



        // This needs to be applied after the update logic, otherwise we'll overwrite user passwords
        // Issue #5408
        $this->item['password'] = $this->tempPassword;

        $this->log('No matching user, creating one');
        $user = new User();
        $user->created_by = auth()->id();

        $user->fill($this->sanitizeItemForStoring($user));

        // TODO - check for gate here I guess


        if ($user->save()) {
            $this->log('User '.$this->item['name'].' was created');

            if (($user->email) && ($user->activated == '1')) {

                if ($this->send_welcome) {

                    try {
                        $user->notify(new WelcomeNotification($user));
                    } catch (\Exception $e) {
                        Log::warning('Could not send welcome notification for user: ' . $e->getMessage());
                    }

                }

            }
            $user = null;
            $this->item = null;

            return;
        }

        $this->logError($user, 'User');
    }


    /**
     * Fetch an existing department, or create new if it doesn't exist
     *
     * @author Daniel Melzter
     * @since 5.0
     * @param $department_name string
     * @return int id of department created/found
     */
    public function createOrFetchDepartment($department_name)
    {
        if (is_null($department_name) || $department_name == ''){
            return null;
        }


        $department = Department::where(['name' => $department_name])->first();
        if ($department) {
            $this->log('A matching department ' . $department_name . ' already exists');
            return $department->id;
        }

        $department = new department();
        $department->name = $department_name;
        $department->created_by = $this->created_by;

        if ($department->save()) {
            $this->log('department ' . $department_name . ' was created');
            return $department->id;
        }

        $this->logError($department, 'Department');
        return null;
    }
    
    public function sendWelcome($send = true)
    {
        $this->send_welcome = $send;
    }

    /**
     * Since the findCsvMatch() method will set '' for columns that are present but empty,
     * we need to set those empty strings to null to avoid passing bad data to the database
     * (ie ending up with 0000-00-00 instead of the intended null).
     *
     * @return void
     */
    private function handleEmptyStringsForDates(): void
    {
        if ($this->item['start_date'] === '') {
            $this->item['start_date'] = null;
        }

        if ($this->item['end_date'] === '') {
            $this->item['end_date'] = null;
        }
    }
}
