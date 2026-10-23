<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\DatatablesTransformer;
use App\Http\Transformers\LoginAttemptsTransformer;
use App\Models\Ldap;
use App\Models\Setting;
use App\Notifications\MailTest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SettingsController extends Controller
{
    public function ldaptest(): JsonResponse
    {
        $settings = Setting::getSettings();

        if ($settings->ldap_enabled != '1') {
            Log::debug('LDAP is not enabled cannot test.');

            return response()->json(['message' => 'LDAP is not enabled, cannot test.'], 400);
        }
        return Helper::EqualTiming(5, function () {

            Log::debug('Preparing to test LDAP connection');

            $message = []; // where we collect together test messages
            try {
                $connection = Ldap::connectToLdap();
                $message['bind'] = ['message' => 'Successfully bound to LDAP server.'];
                Log::debug('attempting to bind to LDAP for LDAP test');
                Ldap::bindAdminToLdap($connection);
                $message['login'] = [
                    'message' => 'Successfully connected to LDAP server.',
                ];

                // Shape is driven by Ldap::parseAndMapLdapAttributes so
                // this endpoint stays in sync with the sync command and
                // the first-login create path. Blank fields collapse to
                // null in the JSON so the JS side's `?? NULL` fallback
                // renders "NULL" for missing values.
                $users = collect(Ldap::findLdapUsers(null, 10))->filter(function ($value, $key) {
                    return is_int($key);
                })->slice(0, 10)->map(function ($item) {
                    $mapped = Ldap::parseAndMapLdapAttributes($item);
                    return (object) array_map(fn($value) => $value === '' ? null : $value, $mapped);
                });
                if ($users->count() > 0) {
                    // `fields` is the ordered internal_key => translated
                    // label map the JS iterates to build the results
                    // table's header + per-row cells. Same shape drives
                    // the LDAP wizard's step-3 preview, so both stay
                    // consistent as attributeMap() grows.
                    $labels = Ldap::attributeLabels();
                    $fields = collect(array_keys(Ldap::parseAndMapLdapAttributes([])))
                        ->mapWithKeys(fn($key) => [$key => $labels[$key] ?? $key])
                        ->all();
                    $message['user_sync'] = [
                        'fields' => $fields,
                        'users' => $users,
                    ];
                } else {
                    $message['user_sync'] = [
                        'message' => 'Connection to LDAP was successful, however there were no users returned from your query. You should confirm the Base Bind DN above.',
                    ];

                    return response()->json($message, 400);
                }

                return response()->json($message, 200);
            } catch (\Exception $e) {
                Log::debug('Connection failed but we cannot debug it any further on our end.');

                return response()->json(['message' => $e->getMessage()], 400);
            }
        });
    }

    public function ldaptestlogin(Request $request): JsonResponse
    {

        if (Setting::getSettings()->ldap_enabled != '1') {
            Log::debug('LDAP is not enabled. Cannot test.');

            return response()->json(['message' => 'LDAP is not enabled, cannot test.'], 400);
        }

        $rules = [
            'ldaptest_user' => 'required',
            'ldaptest_password' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            Log::debug('LDAP Validation test failed.');
            $validation_errors = implode(' ', $validator->errors()->all());

            return response()->json(['message' => $validator->errors()->all()], 400);
        }

        Log::debug('Preparing to test LDAP login');
        try {
            $connection = Ldap::connectToLdap();
            try {
                Ldap::bindAdminToLdap($connection);
                Log::debug('Attempting to bind to LDAP for LDAP test');
                try {
                    $ldap_user = Ldap::findAndBindUserLdap($request->input('ldaptest_user'), $request->input('ldaptest_password'));
                    if ($ldap_user) {
                        Log::debug('It worked! '.$request->input('ldaptest_user').' successfully binded to LDAP.');

                        return response()->json(['message' => 'It worked! '.$request->input('ldaptest_user').' successfully binded to LDAP.'], 200);
                    }

                    return response()->json(['message' => 'Login Failed. '.$request->input('ldaptest_user').' did not successfully bind to LDAP.'], 400);

                } catch (\Exception $e) {
                    Log::debug('LDAP login failed');

                    return response()->json(['message' => $e->getMessage()], 400);
                }

            } catch (\Exception $e) {
                Log::debug('Bind failed');

                return response()->json(['message' => $e->getMessage()], 400);
                // return response()->json(['message' => $e->getMessage()], 500);
            }
        } catch (\Exception $e) {
            Log::debug('Connection failed');

            return response()->json(['message' => $e->getMessage()], 500);
        }

    }

    /**
     * Test the email configuration
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v3.0]
     */
    public function ajaxTestEmail(): JsonResponse
    {
        if (! config('app.lock_passwords')) {

            if (config('mail.reply_to.address') == '') {
                Log::debug('MAIL_REPLYTO_ADDR not set in env. Skipping mail test.');

                return response()->json(['message' => trans('admin/settings/general.mail_test_no_email')], 403);
            }

            try {
                Notification::send(Setting::first(), new MailTest);
                Log::debug('Attempting to sending to '.config('mail.reply_to.address'));

                return response()->json(['message' => 'Mail sent to '.config('mail.reply_to.address')], 200);
            } catch (\Exception $e) {
                Log::error('Mail sent error using '.config('mail.reply_to.address').': '.$e->getMessage());
                Log::debug($e);

                return response()->json(['message' => $e->getMessage()], 500);
            }
        }

        return response()->json(['message' => 'Mail would have been sent, but this application is in demo mode! '], 200);

    }

    /**
     * Delete server-cached barcodes
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v5.0.0]
     */
    public function purgeBarcodes(): JsonResponse
    {
        $file_count = 0;
        $files = Storage::disk('public')->files('barcodes');

        foreach ($files as $file) { // iterate files

            $file_parts = explode('.', $file);
            $extension = end($file_parts);
            Log::debug($extension);

            // Only generated barcodes would have a .png file extension
            if ($extension == 'png') {
                Log::debug('Deleting: '.$file);

                try {
                    Storage::disk('public')->delete($file);
                    Log::debug('Deleting: '.$file);
                    $file_count++;
                } catch (\Exception $e) {
                    Log::debug($e);
                }
            }
        }

        return response()->json(['message' => 'Deleted '.$file_count.' barcodes'], 200);
    }

    /**
     * Get a list of login attempts
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v5.0.0]
     */
    public function showLoginAttempts(Request $request): array
    {
        $allowed_columns = ['id', 'username', 'remote_ip', 'user_agent', 'successful', 'created_at'];

        $login_attempts = DB::table('login_attempts');
        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';

        $total = $login_attempts->count();
        $login_attempts->orderBy($sort, $order);
        $login_attempt_results = $login_attempts->skip(request('offset', 0))->take(request('limit', 20))->get();

        return (new LoginAttemptsTransformer)->transformLoginAttempts($login_attempt_results, $total);
    }

    /**
     * Lists backup files
     *
     * @author [A. Gianotto]
     */
    public function listBackups(): array
    {
        $settings = Setting::getSettings();
        // Same disk and name the web backup page uses (and the one the backups are written to).
        $disk = Storage::disk('backup');
        $path = config('backup.backup.name', 'backups');
        $backup_files = $disk->files($path);
        $files_raw = [];
        $count = 0;

        if (count($backup_files) > 0) {

            for ($f = 0; $f < count($backup_files); $f++) {

                // Skip dotfiles like .gitignore and .DS_STORE
                if ((substr(basename($backup_files[$f]), 0, 1) != '.')) {
                    $file_timestamp = $disk->lastModified($backup_files[$f]);

                    $files_raw[] = [
                        'filename' => basename($backup_files[$f]),
                        'filesize' => Setting::fileSizeConvert($disk->size($backup_files[$f])),
                        'modified_value' => $file_timestamp,
                        'modified_display' => date($settings->date_display_format.' '.$settings->time_display_format, $file_timestamp),
                        'backup_url' => config('app.url').'/settings/backups/download/'.basename($backup_files[$f]),

                    ];
                    $count++;
                }

            }
        }

        $files = array_reverse($files_raw);

        return (new DatatablesTransformer)->transformDatatables($files, $count);

    }

    /**
     * Downloads a backup file from the backup disk. StorageHelper::downloader() uses
     * response()->download() on a local disk (Storage::download() exhausts memory on larger files)
     * and a short-lived presigned URL on S3.
     *
     * @author [A. Gianotto]
     */
    public function downloadBackup($file): JsonResponse|BinaryFileResponse|RedirectResponse|StreamedResponse
    {
        $file = $this->sanitizeBackupFilename($file);

        if ($file === null) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.file_not_found')), 404);
        }

        $key = config('backup.backup.name', 'backups').'/'.$file;

        if (Storage::disk('backup')->exists($key)) {
            return StorageHelper::downloader($key, 'backup');
        } else {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.file_not_found')), 404);
        }

    }

    /**
     * Determines and downloads the latest backup
     *
     * @author [A. Gianotto]
     *
     * @since [v6.3.1]
     */
    public function downloadLatestBackup(): JsonResponse|BinaryFileResponse|RedirectResponse|StreamedResponse
    {
        $disk = Storage::disk('backup');

        $fileData = collect();
        foreach ($disk->files(config('backup.backup.name', 'backups')) as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) == 'zip') {
                $fileData->push([
                    'file' => $file,
                    'date' => $disk->lastModified($file),
                ]);
            }
        }

        // Newest first; when the times are equal the file name decides, so the answer is always the same.
        $newest = $fileData->sort(function (array $a, array $b) {
            return [$b['date'], $b['file']] <=> [$a['date'], $a['file']];
        })->first();

        if ($newest !== null && $disk->exists($newest['file'])) {
            return StorageHelper::downloader($newest['file'], 'backup');
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.file_not_found')), 404);
    }

    private function sanitizeBackupFilename(mixed $filename): ?string
    {
        $filename = trim((string) $filename);

        if ($filename === '' || str_contains($filename, "\0")) {
            return null;
        }

        $sanitized = basename($filename);

        if (($sanitized === '') || ($sanitized === '.') || ($sanitized === '..')) {
            return null;
        }

        return ($sanitized === $filename) ? $sanitized : null;
    }
}
