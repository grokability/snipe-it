<?php

namespace App\Importer;

use App\Models\CustomField;
use App\Models\Department;
use App\Models\Setting;
use App\Models\User;
use Carbon\CarbonImmutable;
use ForceUTF8\Encoding;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use Illuminate\Support\Facades\Log;

abstract class Importer
{
    protected $csv;
    /**
     * Id of User performing import
     * @var
     */
    protected $created_by;
    /**
     * Are we updating items in the import
     * @var bool
     */

    protected $updating;

    /**
     * Default Map of item fields->csv names
     *
     * This has been moved into app/Http/Livewire/Importer.php to be more granular.
     * This private variable is ONLY used for the cli-importer.
     *
     * @todo - find a way to make this less duplicative
     * @var array
     */
    private $defaultFieldMap = [
        'id' => 'id',
        'asset_tag' => 'asset tag',
        'activated' => 'activated',
        'category' => 'category',
        'checkout_class' => 'checkout type', // Supports Location or User for assets.  Using checkout_class instead of checkout_type because type exists on asset already.
        'checkout_location' => 'checkout location',
        'company' => 'company',
        'item_name' => 'item name',
        'item_number' => 'item number',
        'image' => 'image',
        'expiration_date' => 'expiration date',
        'location' => 'location',
        'notes' => 'notes',
        'license_email' => 'licensed to email',
        'license_name' => 'licensed to name',
        'maintained' => 'maintained',
        'manufacturer' => 'manufacturer',
        'asset_model' => 'model name',
        'model_number' => 'model number',
        'order_number' => 'order number',
        'purchase_cost' => 'purchase cost',
        'purchase_date' => 'purchase date',
        'purchase_order' => 'purchase order',
        'qty' => 'quantity',
        'reassignable' => 'reassignable',
        'requestable' => 'requestable',
        'seats' => 'seats',
        'serial' => 'serial number',
        'status' => 'status',
        'supplier' => 'supplier',
        'termination_date' => 'termination date',
        'warranty_months' => 'warranty',
        'full_name' => 'full name',
        'display_name' => 'display name',
        'email' => 'email',
        'username' => 'username',
        'address' => 'address',
        'address2' => 'address2',
        'city' => 'city',
        'state' => 'state',
        'country' => 'country',
        'zip' => 'zip',
        'jobtitle' => 'job title',
        'employee_num' => 'employee number',
        'phone_number' => 'phone number',
        'first_name' => 'first name',
        'last_name' => 'last name',
        'department' => 'department',
        'manager_name' => 'manager full name',
        'manager_username' => 'manager username',
        'manager_employee_num' => 'manager employee number',
        'min_amt' => 'minimum quantity',
        'remote' => 'remote',
        'vip' => 'vip',
    ];
    /**
     * Map of item fields->csv names
     * @var array
     */
    protected $fieldMap = [];
    /**
     * @var callable
     */
    protected $logCallback;
    protected $tempPassword;
    /**
     * @var callable
     */
    protected $progressCallback;
    /**
     * @var null
     */
    protected $usernameFormat;
    /**
     * @var callable
     */
    protected $errorCallback;

    /**
     * ObjectImporter constructor.
     * @param string $file
     */
    public function __construct($file)
    {
        $this->fieldMap = $this->defaultFieldMap;
        if (! ini_get('auto_detect_line_endings')) {
            ini_set('auto_detect_line_endings', '1');
        }
        // By default the importer passes a url to the file.
        // However, for testing we also support passing a string directly
        if (is_file($file)) {
            $this->csv = Reader::createFromPath($file);
        } else {
            $this->csv = Reader::createFromString($file);
        }
        $this->tempPassword = '*** NO PASSWORD - IMPORTED VIA CSV ***';
    }

    // Cached Values for import lookups
    protected $customFields;

    /**
     * Sets up the database transaction and logging for the importer
     *
     * @return void
     * @author Daniel Meltzer
     * @since  5.0
     */
    public function import()
    {
        $headerRow = $this->csv->fetchOne();
        $this->csv->setHeaderOffset(0); //explicitly sets the CSV document header record

        $this->populateCustomFields($headerRow);

        DB::transaction(function () use ($headerRow) {
            $importedItemsCount = 0;
            Model::unguard();

            foreach ($this->csv->getRecords($headerRow) as $row) {
                //Lowercase header values to ensure we're comparing values properly.
                $row = array_change_key_case($row, CASE_LOWER);

                $this->handle($row);

                $importedItemsCount++;

                if ($this->progressCallback) {
                    call_user_func($this->progressCallback, $importedItemsCount);
                }

                $this->log('------------- Action Summary ----------------');
            }
            Model::reguard();
        });
    }

    abstract protected function handle($row);

    /**
     * Fetch custom fields from database and translate/parse them into a format
     * appropriate for use in the importer.
     * @return void
     * @author Daniel Meltzer
     * @since  5.0
     */
    protected function populateCustomFields($headerRow)
    {
        // Stolen From https://adamwathan.me/2016/07/14/customizing-keys-when-mapping-collections/
        // This 'inverts' the fields such that we have a collection of fields indexed by name.
        $this->customFields = CustomField::All()->reduce(function ($nameLookup, $field) {
            $nameLookup[$field['name']] = $field;

            return $nameLookup;
        });
        // Remove any custom fields that do not exist in the header row.  This prevents nulling out values that shouldn't exist.
        // In detail, we compare the lower case name of custom fields (indexed by name) to the keys in the header row.  This
        // results in an array with only custom fields that are in the file.
        if ($this->customFields) {
            $this->customFields = array_intersect_key(
                array_change_key_case($this->customFields),
                array_change_key_case(array_flip($headerRow))
            );
        }
    }

    /**
     * Check to see if the given key exists in the array, and trim excess white space before returning it
     *
     * @author Daniel Melzter
     * @since 3.0
     * @param $array array
     * @param $key string
     * @param $default string
     * @return string
     */
    public function findCsvMatch(array $array, $key, $default = null)
    {
        $val = $default;
        $key = $this->lookupCustomKey($key);

       // $this->log("Custom Key: ${key}");
        if (array_key_exists($key, $array)) {
            $val = Encoding::toUTF8(trim($array[$key]));
        }
        //$this->log("${key}: ${val}");
        return $val;
    }

    /**
     * Looks up A custom key in the custom field map
     *
     * @author Daniel Melzter
     * @since 4.0
     * @param $key string
     * @return string|null
     */
    public function lookupCustomKey($key)
    {
        if (array_key_exists($key, $this->fieldMap)) {
            return $this->fieldMap[$key];
        }
        // Otherwise no custom key, return original.
        return $key;
    }

    /**
     * Figure out the fieldname of the custom field
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since 3.0
     * @param $array array
     * @return string
     */
    public function array_smart_custom_field_fetch(array $array, $key)
    {
        $index_name = strtolower($key->name);

        return array_key_exists($index_name, $array) ? trim($array[$index_name]) : false;
    }

    protected function log($string)
    {
        if ($this->logCallback) {
            call_user_func($this->logCallback, $string);
        }
    }

    protected function logError($item, $field)
    {
        if ($this->errorCallback) {
            call_user_func($this->errorCallback, $item, $field, $item->getErrors());
        }
    }

    protected function addErrorToBag($item, $field,  $error_message)
    {
        if ($this->errorCallback) {
            call_user_func($this->errorCallback, $item, $field, [$field => [$error_message]]);
        }
    }

    /**
     * Finds the user matching given data, or creates a new one if there is no match.
     * This is NOT used by the User Import, only for Asset/Accessory/etc where
     * there are users listed and we have to create them and associate them at
     * the same time. [ALG]
     *
     * @author Daniel Melzter
     * @since 3.0
     * @param $row array
     * @return User Model w/ matching name
     * @internal param array $user_array User details parsed from csv
     */
    protected function createOrFetchUser($row, $type = 'user')
    {

        $user_array = [
            'full_name' => $this->findCsvMatch($row, 'full_name'),
            'first_name' => $this->findCsvMatch($row, 'first_name'),
            'last_name' => $this->findCsvMatch($row, 'last_name'),
            'display_name' => $this->findCsvMatch($row, 'display_name'),
            'email'     => $this->findCsvMatch($row, 'email'),
            'manager_id'=>  '',
            'department_id' =>  '',
            'username'  => $this->findCsvMatch($row, 'username'),
            'activated'  => $this->fetchHumanBoolean($this->findCsvMatch($row, 'activated')),
            'remote'    => $this->fetchHumanBoolean(($this->findCsvMatch($row, 'remote'))),
        ];

        if ($type == 'manager') {
            $user_array['full_name'] = $this->findCsvMatch($row, 'manager');
            $user_array['username'] = $this->findCsvMatch($row, 'manager_username');
        }

        // Maybe we're lucky and the username was passed and it already exists.
        if (!empty($user_array['username'])) {
            if ($user = User::where('username', $user_array['username'])->first()) {
                $this->log('User '.$user_array['username'].' already exists');
                return $user;
            }
        }


        // If the full name and username is empty, bail out--we need this to extract first name (at the very least)
        if ((empty($user_array['username'])) && (empty($user_array['full_name'])) && (empty($user_array['first_name']))) {
            $this->log('Insufficient user data provided (Full name, first name or username is required) - skipping user creation.');
            Log::debug('User array: ');
            Log::debug(print_r($user_array, true));
            Log::debug(print_r($row, true));
            return false;
        }


        // Populate email if it does not exist.
        if (empty($user_array['email'])) {
            $user_array['email'] = User::generateEmailFromFullName($user_array['full_name']);
        }

        // Get some variables for $user_formatted_array in case we need them later
        $user_formatted_array = User::generateFormattedNameFromFullName($user_array['full_name'], Setting::getSettings()->username_format);

        if (empty($user_array['first_name'])) {
            // Get some fields for first name and last name based off of full name
            $user_array['first_name'] = $user_formatted_array['first_name'];
            $user_array['last_name'] = $user_formatted_array['last_name'];
        }

        if (empty($user_array['username'])) {
            $user_array['username'] = $user_formatted_array['username'];
            if ($this->usernameFormat == 'email') {
                $user_array['username'] = $user_array['email'];
            }

            // Check for a matching username one more time after trying to guess username.
            if ($user = User::where('username', $user_array['username'])->first()) {
                $this->log('User '.$user_array['username'].' already exists');
                return $user;
            }
        }

        // If at this point we have not found a username or first name, bail out in shame.
        if (empty($user_array['username']) || empty($user_array['first_name'])) {
            return false;
        }

        // No luck finding a user on username or first name, let's create one.
        $user = new User;

        $user->first_name = $user_array['first_name'];
        $user->last_name = $user_array['last_name'];
        $user->username = $user_array['username'];
        $user->display_name = $user_array['display_name'] ?? null;
        $user->email = $user_array['email'];
        $user->manager_id = $user_array['manager_id'] ?? null;
        $user->department_id = $user_array['department_id'] ?? null;
        $user->activated = 1;
        $user->password = $this->tempPassword;

        Log::debug('Creating a user with the following attributes: '.print_r($user_array, true));

        if ($user->save()) {
            $this->log('User '.$user_array['username'].' created');
            return $user;
        }

        $this->logError($user, 'User "'.$user_array['username'].'" was not able to be created.');

        return false;
    }

    /**
     * Matches a user by created_by if user_name provided is a number
     * @param  string $user_name users full name from csv
     * @return User           User Matching ID
     */
    protected function findUserByNumber($user_name)
    {
        // A number was given instead of a name
        if (is_numeric($user_name)) {
            $this->log('User '.$user_name.' is a number - lets see if it matches a user id');

            return User::find($user_name);
        }
    }

    /**
     * Sets the Id of User performing import.
     *
     * @param mixed $created_by the user id
     *
     * @return self
     */
    public function setCreatedBy($created_by)
    {
        $this->created_by = $created_by;

        return $this;
    }

    /**
     * Sets the Are we updating items in the import.
     *
     * @param bool $updating the updating
     *
     * @return self
     */
    public function setUpdating($updating)
    {
        $this->updating = $updating;

        return $this;
    }

    /**
     * Sets whether or not we should notify the user with a welcome email
     *
     * @param bool $send_welcome the send-welcome flag
     *
     * @return self
     */
    public function setShouldNotify($send_welcome)
    {
        $this->send_welcome = $send_welcome;

        return $this;
    }

    /**
     * Defines mappings of csv fields
     *
     * @param bool $updating the updating
     *
     * @return self
     */
    public function setFieldMappings($fields)
    {
        // Some initial sanitization.
        $fields = array_map('strtolower', $fields);
        $this->fieldMap = array_merge($this->defaultFieldMap, $fields);

        // $this->log($this->fieldMap);
        return $this;
    }

    /**
     * Sets the callbacks for the import
     *
     * @param callable $logCallback Function to call when we have data to log
     * @param callable $progressCallback Function to call to display progress
     * @param callable $errorCallback Function to call when we have errors
     *
     * @return self
     */
    public function setCallbacks(callable $logCallback, callable $progressCallback, callable $errorCallback)
    {
        $this->logCallback = $logCallback;
        $this->progressCallback = $progressCallback;
        $this->errorCallback = $errorCallback;

        return $this;
    }

    /**
     * Sets the value of usernameFormat.
     *
     * @param string $usernameFormat the username format
     *
     * @return self
     */
    public function setUsernameFormat($usernameFormat)
    {
        $this->usernameFormat = $usernameFormat;

        return $this;
    }

    public function fetchHumanBoolean($value)
    {
        $true = [
            'yes',
            'y',
            'true',
        ];

        if (in_array(strtolower($value), $true)) {
            return 1;
        }

        return (int) filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Fetch an existing department, or create new if it doesn't exist
     *
     * @author A. Gianotto
     * @since 4.6.5
     * @param $user_department string
     * @return int id of company created/found
     */
    public function createOrFetchDepartment($user_department_name)
    {
        if ($user_department_name != '') {
            $department = Department::where('name', '=', $user_department_name)->first();

            if ($department) {
                $this->log('A matching Department '.$user_department_name.' already exists');

                return $department->id;
            }

            $department = new Department();
            $department->name = $user_department_name;

            if ($department->save()) {
                $this->log('Department '.$user_department_name.' was created');

                return $department->id;
            }
            $this->logError($department, 'Department');
        }

        return null;
    }


    /**
     * Fetch an existing manager
     *
     * @author A. Gianotto
     * @since 4.6.5
     * @param $user_manager string
     * @return int id of company created/found
     */
    public function fetchManager($user_manager_first_name, $user_manager_last_name)
    {
        $manager = User::where('first_name', '=', $user_manager_first_name)
            ->where('last_name', '=', $user_manager_last_name)->first();
        if ($manager) {
            $this->log('A matching Manager '.$user_manager_first_name.' '.$user_manager_last_name.' already exists');

            return $manager->id;
        }
        $this->log('No matching Manager '.$user_manager_first_name.' '.$user_manager_last_name.' found. If their user account is being created through this import, you should re-process this file again. ');

        return null;
    }

    /**
     * Parse a date or return null
     *
     * @author A. Gianotto
     * @since 7.0.0
     * @param $field
     * @param $format
     * @return string|null

     */
    public function parseOrNullDate($field, $format = 'date') {

        $date_format = 'Y-m-d';

        if ($format == 'datetime') {
            $date_format = 'Y-m-d H:i:s';
        }

        if (array_key_exists($field, $this->item) && $this->item[$field] != '') {

            try {
                $value = CarbonImmutable::parse($this->item[$field])->format($date_format);
                return $value;
            } catch (\Exception $e) {
                $this->log('Unable to parse date: ' . $this->item[$field]);
                return null;
            }
        }
        return null;
    }
}
