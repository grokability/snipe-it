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
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;

abstract class Importer
{
    protected $csv;

    /**
     * Id of User performing import
     */
    protected $created_by;

    /**
     * Are we updating items in the import
     *
     * @var bool
     */
    protected $updating;

    /**
     * True when the operator ticked the "send welcome email to users"
     * wizard checkbox. Set by setShouldNotify from ItemImportRequest.
     * UserImporter overrides this default in its own class body; the
     * base declaration here means subclasses like AssetImporter can
     * read the flag without triggering PHPStan property-not-found on
     * the dynamic assignment.
     *
     * @var bool
     */
    protected $send_welcome = false;

    /**
     * Send the "welcome to Snipe-IT" email to a user that was JUST
     * created as a checkout target in this row, if the operator opted
     * in on the wizard. wasRecentlyCreated is only true on the model
     * instance that a save() call minted, so matched existing users
     * don't retrigger the email on subsequent imports. Guarded on
     * email + activated for parity with UserImporter's welcome path.
     * Called from AssetImporter / AccessoryImporter / ConsumableImporter
     * / LicenseImporter after a successful checkout to a user target.
     */
    protected function maybeSendWelcomeEmail($target): void
    {
        if (! $this->send_welcome) {
            return;
        }

        if (! ($target instanceof User) || ! $target->wasRecentlyCreated) {
            return;
        }

        if (! $target->email || $target->activated != '1') {
            return;
        }

        try {
            $target->notify(new \App\Notifications\WelcomeNotification($target));
        } catch (\Exception $e) {
            Log::warning('Could not send welcome notification for imported user: '.$e->getMessage());
        }
    }

    /**
     * Default Map of item fields->csv names
     *
     * This has been moved into app/Http/Livewire/Importer.php to be more granular.
     * This private variable is ONLY used for the cli-importer.
     *
     * @todo - find a way to make this less duplicative
     *
     * @var array
     */
    private $defaultFieldMap = [
        'id' => 'id',
        'asset_tag' => 'asset tag',
        'activated' => 'activated',
        'category' => 'category',
        'checkout_class' => 'checkout type', // Supports Location, User, or Asset for assets. Using checkout_class instead of checkout_type because type exists on asset already.
        'checkout_location' => 'checkout location',
        'checkout_asset' => 'checkout asset',
        'checkout_user' => 'checkout user',
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
        'archived' => 'archived',
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
        'tag_color' => 'tag color',
    ];

    /**
     * Map of item fields->csv names
     *
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
     *
     * @param  string  $file
     */
    public function __construct($file)
    {
        $this->fieldMap = $this->defaultFieldMap;
        if (! ini_get('auto_detect_line_endings')) {
            ini_set('auto_detect_line_endings', '1');
        }
        // By default the importer passes a url to the file.
        // However, for testing we also support passing a string directly.
        //
        // Memory-conscious construction: this constructor runs once per JS-
        // chunked slice against the same file (see ItemImportRequest::import,
        // which builds a new Importer per POST from the Livewire importer's
        // chunk loop). Buffering the whole file into a string here would
        // multiply peak memory by the chunk count, which defeats the entire
        // point of the chunked-import rework. Common case: sample the head
        // of the file, if it's UTF-8 use Reader::createFromPath so the CSV
        // reader streams from disk. Only fall back to full-file buffering
        // when encoding conversion is actually needed.
        if (is_file($file)) {
            $sample = self::readSampleForEncodingProbe($file);
            if ($sample !== null && ! mb_check_encoding($sample, 'UTF-8')) {
                $contents = file_get_contents($file);
                if ($contents !== false) {
                    $contents = self::convertToUtf8IfNeeded($contents);
                    $this->csv = Reader::createFromString($contents);
                } else {
                    $this->csv = Reader::createFromPath($file);
                }
            } else {
                $this->csv = Reader::createFromPath($file);
            }
        } else {
            // Raw string input (tests, callers that build a CSV string
            // in-process). Already in memory, so run the same encoding
            // guard directly on the string.
            $contents = mb_check_encoding($file, 'UTF-8') ? $file : self::convertToUtf8IfNeeded($file);
            $this->csv = Reader::createFromString($contents);
        }
        $this->tempPassword = '*** NO PASSWORD - IMPORTED VIA CSV ***';
    }

    /**
     * Read a small head-of-file sample cheaply for encoding detection. 8KB
     * is enough to tell UTF-8 from GBK / Windows-1252 / Shift-JIS in
     * practice (CSVs have uniform encoding throughout), and reading a
     * sample this size stays in a single filesystem block on almost every
     * modern deployment.
     *
     * Returns null if the file can't be opened, which callers should
     * treat as "assume UTF-8 and let downstream errors surface".
     */
    private static function readSampleForEncodingProbe(string $path): ?string
    {
        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            return null;
        }
        $sample = @fread($handle, 8192);
        fclose($handle);

        return $sample === false ? null : $sample;
    }

    /**
     * Detect the source encoding of a non-UTF-8 string and convert it to
     * UTF-8. Returns the source unchanged if the contents are already
     * UTF-8, if no source encoding can be determined, or if conversion
     * would drop more than half the source bytes (see
     * conversionExceedsLossThreshold).
     */
    private static function convertToUtf8IfNeeded(string $contents): string
    {
        if (mb_check_encoding($contents, 'UTF-8')) {
            return $contents;
        }

        $encoding = self::detectSourceEncoding($contents);
        if ($encoding === null) {
            return $contents;
        }

        $converted = self::runEncodingConversion($contents, $encoding);
        if ($converted === null) {
            return $contents;
        }

        if (self::conversionExceedsLossThreshold($contents, $converted, $encoding)) {
            return $contents;
        }

        return $converted;
    }

    /**
     * Try the Onnov detector first, falling back to mb_detect only when
     * Onnov abstains. Onnov is usually more reliable when it commits to
     * an answer, and overriding it with the CJK-leaning mb_detect list
     * produces mojibake on short Cyrillic input. Returns null when no
     * usable non-UTF-8 encoding was found.
     */
    private static function detectSourceEncoding(string $contents): ?string
    {
        $encoding = null;
        if (class_exists('\Onnov\DetectEncoding\EncodingDetector')) {
            $detector = new \Onnov\DetectEncoding\EncodingDetector;
            $encoding = $detector->getEncoding($contents);
        }

        if (! $encoding || strcasecmp($encoding, 'UTF-8') === 0) {
            $detected = mb_detect_encoding($contents, ['UTF-8', 'GBK', 'GB2312', 'GB18030', 'BIG5', 'SJIS', 'EUC-JP', 'EUC-KR', 'Windows-1252', 'Windows-1251', 'ISO-8859-1'], true);
            if ($detected) {
                $encoding = $detected;
            }
        }

        if (! $encoding || strcasecmp($encoding, 'UTF-8') === 0) {
            return null;
        }

        return $encoding;
    }

    /**
     * Run the actual byte-level conversion. Prefers iconv with //IGNORE so
     * real-world CSVs with a stray invalid byte still import successfully,
     * falling back to mb_convert_encoding on hosts without iconv. Returns
     * null when no converter is available or the converter refused the
     * input entirely.
     */
    private static function runEncodingConversion(string $contents, string $encoding): ?string
    {
        $converted = null;
        if (function_exists('iconv')) {
            $result = @iconv(strtoupper($encoding), 'UTF-8//IGNORE', $contents);
            $converted = $result === false ? null : $result;
        } elseif (function_exists('mb_convert_encoding')) {
            $converted = @mb_convert_encoding($contents, 'UTF-8', $encoding);
        }

        return ($converted === null || $converted === '') ? null : $converted;
    }

    /**
     * Loss-ratio safety net for the //IGNORE flag on iconv. If the
     * converted output is less than half the source size we treat that as
     * "//IGNORE dropped most of the file", log a warning, and let the
     * caller fall back to unconverted source so downstream sees the
     * problem instead of an eerily-empty import.
     */
    private static function conversionExceedsLossThreshold(string $source, string $converted, string $encoding): bool
    {
        if (strlen($converted) >= intdiv(strlen($source), 2)) {
            return false;
        }

        Log::warning(sprintf(
            'CSV import: refusing lossy encoding conversion (%s -> UTF-8) that kept %d/%d bytes',
            $encoding,
            strlen($converted),
            strlen($source),
        ));

        return true;
    }

    // Cached Values for import lookups
    protected $customFields;

    /**
     * Sets up the database transaction and logging for the importer
     *
     * @return void
     *
     * @author Daniel Meltzer
     *
     * @since  5.0
     */
    public function import(?int $offset = null, ?int $limit = null)
    {
        $headerRow = $this->csv->fetchOne();
        $this->csv->setHeaderOffset(0); // explicitly sets the CSV document header record

        $this->populateCustomFields($headerRow);

        DB::transaction(function () use ($headerRow, $offset, $limit) {
            $importedItemsCount = 0;
            $processedInSlice = 0;
            Model::unguard();

            // Sliced imports: when the caller passes offset+limit we only
            // process rows [offset, offset+limit). Preserves the original
            // "iterate everything" behavior when neither is provided so
            // CLI callers (ObjectImportCommand) and any external caller
            // hitting the API without offset/limit still work unchanged.
            foreach ($this->csv->getRecords($headerRow) as $row) {
                // Fully blank rows (every cell empty, like ",,,,,,,,") carry
                // no importable data. Skipping them before both the offset
                // walk AND handle() means slice math, tallies, and per-row
                // logging all reflect real work rather than padding rows.
                if (self::rowIsBlank($row)) {
                    continue;
                }

                if ($offset !== null && $importedItemsCount < $offset) {
                    $importedItemsCount++;

                    continue;
                }

                if ($limit !== null && $processedInSlice >= $limit) {
                    break;
                }

                // Lowercase header values to ensure we're comparing values properly.
                $row = array_change_key_case($row, CASE_LOWER);

                try {
                    $this->handle($row);
                } catch (\App\Exceptions\ImportRowRejected $e) {
                    $this->recordErrored();
                    $this->log('Row rejected: '.$e->getMessage());
                    $rowLabel = $row['asset tag'] ?? $row['item name'] ?? $row['name'] ?? 'Row #'.($importedItemsCount + 1);
                    $this->addErrorToBag((object) ['name' => $rowLabel], $e->field, $e->getMessage());
                }

                $importedItemsCount++;
                $processedInSlice++;

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
     *
     * @return void
     *
     * @author Daniel Meltzer
     *
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
     *
     * @since 3.0
     *
     * @param  $array  array
     * @param  $key  string
     * @param  $default  string
     * @return string
     */
    public function findCsvMatch(array $array, $key, $default = null)
    {
        $val = $default;
        $key = $this->lookupCustomKey($key);

        // $this->log("Custom Key: ${key}");
        if (array_key_exists($key, $array)) {
            $trimmed = trim($array[$key]);
            if (mb_check_encoding($trimmed, 'UTF-8')) {
                $val = $trimmed;
            } else {
                $val = Encoding::toUTF8($trimmed);
            }
        }

        // $this->log("${key}: ${val}");
        return $val;
    }

    /**
     * True when the CSV row contains a value for the given logical key
     * (whether the value is populated or empty). Callers use this to
     * distinguish "column absent from the CSV" (leave DB alone) from
     * "column present with an empty value" (clear the DB field on update).
     */
    protected function csvRowHas(array $row, string $csvKey): bool
    {
        return array_key_exists($this->lookupCustomKey($csvKey), $row);
    }

    /**
     * True when every cell in the CSV row is empty after trimming.
     * Fully blank rows (like ",,,,,,,") get filtered out before handle()
     * so they do not count toward the tally, appear in the preview,
     * or trigger per-row logging.
     */
    protected static function rowIsBlank(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Assign a value from the CSV row into $this->item under the given item
     * key, only when the CSV row actually contained that column. Empty CSV
     * cells are assigned as null (not as empty strings) so the DB stores
     * NULL when the user explicitly clears a nullable field on update.
     * Columns absent from the CSV row are never touched, so update mode
     * preserves existing DB values for any field the user did not include
     * in their file.
     */
    protected function setItemFromCsvIfPresent(array $row, string $itemKey, ?string $csvKey = null): void
    {
        $csvKey = $csvKey ?? $itemKey;
        if ($this->csvRowHas($row, $csvKey)) {
            $value = $this->findCsvMatch($row, $csvKey);
            $this->item[$itemKey] = ($value === '') ? null : $value;
        }
    }

    /**
     * Looks up A custom key in the custom field map
     *
     * @author Daniel Melzter
     *
     * @since 4.0
     *
     * @param  $key  string
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
     *
     * @since 3.0
     *
     * @param  $array  array
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

    protected function addErrorToBag($item, $field, $error_message)
    {
        if ($this->errorCallback) {
            call_user_func($this->errorCallback, $item, $field, [$field => [$error_message]]);
        }
    }

    /**
     * Per-row tally accumulated across the current slice. The wizard UI adds
     * these across slices so the user sees a real "N created, M updated,
     * K skipped as duplicates" summary at the end of an import instead of
     * a generic success flash. logError() and addErrorToBag() auto-record
     * errored; subclasses call recordCreated/Updated/Skipped explicitly
     * from the branches of their handle() method.
     */
    protected array $tally = [
        'created' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errored' => 0,
    ];

    protected function recordCreated(): void
    {
        $this->tally['created']++;
    }

    protected function recordUpdated(): void
    {
        $this->tally['updated']++;
    }

    protected function recordSkipped(): void
    {
        $this->tally['skipped']++;
    }

    protected function recordErrored(): void
    {
        $this->tally['errored']++;
    }

    public function getTally(): array
    {
        return $this->tally;
    }

    /**
     * Finds the user matching given data, or creates a new one if there is no match.
     * This is NOT used by the User Import, only for Asset/Accessory/etc where
     * there are users listed and we have to create them and associate them at
     * the same time. [ALG]
     *
     * @author Daniel Melzter
     *
     * @since 3.0
     *
     * @param  $row  array
     * @return User Model w/ matching name
     *
     * @internal param array $user_array User details parsed from csv
     */
    protected function createOrFetchUser($row, $type = 'user')
    {

        $user_array = [
            'full_name' => $this->findCsvMatch($row, 'full_name'),
            'first_name' => $this->findCsvMatch($row, 'first_name'),
            'last_name' => $this->findCsvMatch($row, 'last_name'),
            'display_name' => $this->findCsvMatch($row, 'display_name'),
            'email' => $this->findCsvMatch($row, 'email'),
            'manager_id' => '',
            // ItemImporter::handle() has already created the Department (if
            // the CSV row had one) and stored its id on $this->item so it
            // can flow through to the user record. Previously this value
            // was hard-coded to '' and the Department was orphaned.
            'department_id' => $this->item['department_id'] ?? '',
            // Prefer an explicit username column; fall back to checkout_user
            // (single-column shortcut for asset checkout to an existing or
            // newly-created user, keyed on username). This preserves the
            // create-or-fetch path when the operator only maps checkout_user
            // and does not add a separate username column.
            'username' => $this->findCsvMatch($row, 'username') ?: $this->findCsvMatch($row, 'checkout_user'),
            'activated' => $this->fetchHumanBoolean($this->findCsvMatch($row, 'activated')),
            'remote' => $this->fetchHumanBoolean(($this->findCsvMatch($row, 'remote'))),
        ];

        if ($type == 'manager') {
            $user_array['full_name'] = $this->findCsvMatch($row, 'manager');
            $user_array['username'] = $this->findCsvMatch($row, 'manager_username');
        }

        // Maybe we're lucky and the username was passed and it already exists.
        if (! empty($user_array['username'])) {
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

        // Floater-mode escalation guard (#19200). A side-effect of an asset
        // import is that referenced users get minted with no company pivot —
        // under floater mode that promotes them to system-wide visibility.
        // Refuse to create the user when the importer's actor isn't trusted
        // to grant floater status. Same guard the User CSV importer uses.
        if (Auth::check() && ! auth()->user()->canGrantFloaterStatus()) {
            $this->log('Skipping creation of referenced user "'.($user_array['username'] ?? '').'": cannot create a user with no company assignment while floater mode is enabled (#19200).');

            return false;
        }

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
        // Use $this->created_by (set by setCreatedBy() from both
        // ItemImportRequest for web imports and ObjectImportCommand for
        // CLI imports) rather than auth()->id() so CLI-run imports get
        // the --user_id option value instead of null. Without this,
        // users minted as checkout targets during asset import land in
        // the DB with a null created_by, breaking blame in the user list.
        $user->created_by = $this->created_by;

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
     *
     * @param  string  $user_name  users full name from csv
     * @return User User Matching ID
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
     * @param  mixed  $created_by  the user id
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
     * @param  bool  $updating  the updating
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
     * @param  bool  $send_welcome  the send-welcome flag
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
     * @param  callable  $logCallback  Function to call when we have data to log
     * @param  callable  $progressCallback  Function to call to display progress
     * @param  callable  $errorCallback  Function to call when we have errors
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
     * @param  string  $usernameFormat  the username format
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
     *
     * @since 4.6.5
     *
     * @param  $user_department  string
     * @return int id of company created/found
     */
    public function createOrFetchDepartment($user_department_name)
    {
        // Explicit is_null check before the loose equality guard so a null
        // input doesn't trigger PHP 8.x null-to-string deprecation warnings
        // (the previous form was `!= ''` which coerces null to '' first).
        if (is_null($user_department_name) || $user_department_name === '') {
            return null;
        }

        $department = Department::where('name', $user_department_name)->first();

        if ($department) {
            $this->log('A matching Department '.$user_department_name.' already exists');

            return $department->id;
        }

        $department = new Department;
        $department->name = $user_department_name;
        // $this->created_by, not auth()->id(), so CLI-run imports
        // attribute created_by to the --user_id option value rather
        // than null.
        $department->created_by = $this->created_by;

        if ($department->save()) {
            $this->log('Department '.$user_department_name.' was created');

            return $department->id;
        }
        $this->logError($department, 'Department');

        return null;
    }

    /**
     * Fetch an existing manager
     *
     * @author A. Gianotto
     *
     * @since 4.6.5
     *
     * @param  $user_manager  string
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
     *
     * @since 7.0.0
     *
     * @return string|null
     */
    public function parseOrNullDate($field, $format = 'date')
    {

        $date_format = 'Y-m-d';

        if ($format == 'datetime') {
            $date_format = 'Y-m-d H:i:s';
        }

        if (array_key_exists($field, $this->item) && $this->item[$field] != '') {

            try {
                $value = CarbonImmutable::parse($this->item[$field])->format($date_format);

                return $value;
            } catch (\Exception $e) {
                $this->log('Unable to parse date: '.$this->item[$field]);

                return null;
            }
        }

        return null;
    }
}
