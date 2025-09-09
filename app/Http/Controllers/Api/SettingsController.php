<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Transformers\DatatablesTransformer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Ldap;
use App\Models\Setting;
use App\Notifications\MailTest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Transformers\LoginAttemptsTransformer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


class SettingsController extends Controller
{


    public function ldaptest() : JsonResponse
    {
        $settings = Setting::getSettings();

        if ($settings->ldap_enabled!='1') {
            Log::debug('LDAP is not enabled cannot test.');
            return response()->json(['message' => 'LDAP is not enabled, cannot test.'], 400);
        }

        Log::debug('Preparing to test LDAP connection');

        $message = []; //where we collect together test messages
        try {
            $connection = Ldap::connectToLdap();
            try {
                $message['bind'] = ['message' => 'Successfully bound to LDAP server.'];
                Log::debug('attempting to bind to LDAP for LDAP test');
                Ldap::bindAdminToLdap($connection);
                $message['login'] = [
                    'message' => 'Successfully connected to LDAP server.',
                ];

                $users = collect(Ldap::findLdapUsers(null,10))->filter(function ($value, $key) {
                    return is_int($key);
                })->slice(0, 10)->map(function ($item) use ($settings) {
                    return (object) [
                        'username'        => $item[$settings['ldap_username_field']][0] ?? null,
                        'display_name'    => $item[$settings['ldap_display_name']][0] ?? null,
                        'employee_number' => $item[$settings['ldap_emp_num']][0] ?? null,
                        'lastname'        => $item[$settings['ldap_lname_field']][0] ?? null,
                        'firstname'       => $item[$settings['ldap_fname_field']][0] ?? null,
                        'email'           => $item[$settings['ldap_email']][0] ?? null,
                        'phone'           => $item[$settings['ldap_phone_field']][0] ?? null,
                        'mobile'          => $item[$settings['ldap_mobile']][0] ?? null,
                        'jobtitle'        => $item[$settings['ldap_jobtitle']][0] ?? null,
                        'department'      => $item[$settings['ldap_department']][0] ?? null,
                        'manager'         => $item[$settings['ldap_manager']][0] ?? null,
                        'address'         => $item[$settings['ldap_address']][0] ?? null,
                        'city'            => $item[$settings['ldap_city']][0] ?? null,
                        'state'           => $item[$settings['ldap_state']][0] ?? null,
                        'zip'             => $item[$settings['ldap_zip']][0] ?? null,
                        'country'         => $item[$settings['ldap_country']][0] ?? null,
                        'location'        => $item[$settings['ldap_location']][0] ?? null,
                    ];
                });
                if ($users->count() > 0) {
                    $message['user_sync'] = [
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
                Log::debug('Bind failed');
                Log::debug("Exception was: ".$e->getMessage());
                return response()->json(['message' => $e->getMessage()], 400);
                //return response()->json(['message' => $e->getMessage()], 500);
            }
        } catch (\Exception $e) {
            Log::debug('Connection failed but we cannot debug it any further on our end.');
            return response()->json(['message' => $e->getMessage()], 400);
        }


    }

    public function ldaptestlogin(Request $request) : JsonResponse
    {

        if (Setting::getSettings()->ldap_enabled != '1') {
            Log::debug('LDAP is not enabled. Cannot test.');
            return response()->json(['message' => 'LDAP is not enabled, cannot test.'], 400);
        }


        $rules = array(
            'ldaptest_user' => 'required',
            'ldaptest_password' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            Log::debug('LDAP Validation test failed.');
            $validation_errors = implode(' ',$validator->errors()->all());
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
                        Log::debug('It worked! '. $request->input('ldaptest_user').' successfully binded to LDAP.');
                        return response()->json(['message' => 'It worked! '. $request->input('ldaptest_user').' successfully binded to LDAP.'], 200);
                    }
                    return response()->json(['message' => 'Login Failed. '. $request->input('ldaptest_user').' did not successfully bind to LDAP.'], 400);

                } catch (\Exception $e) {
                    Log::debug('LDAP login failed');
                    return response()->json(['message' => $e->getMessage()], 400);
                }

            } catch (\Exception $e) {
                Log::debug('Bind failed');
                return response()->json(['message' => $e->getMessage()], 400);
                //return response()->json(['message' => $e->getMessage()], 500);
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
     * @since [v3.0]
     */
    public function ajaxTestEmail() : JsonResponse
    {
        if (!config('app.lock_passwords')) {
            try {
                Notification::send(Setting::first(), new MailTest());
                Log::debug('Attempting to sending to '.config('mail.reply_to.address'));
                return response()->json(['message' => 'Mail sent to '.config('mail.reply_to.address')], 200);
            } catch (\Exception $e) {
                Log::error('Mail sent error using '.config('mail.reply_to.address') .': '. $e->getMessage());
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
     * @since [v5.0.0]
     */
    public function purgeBarcodes() : JsonResponse
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
     * @since [v5.0.0]
     * @param  \Illuminate\Http\Request  $request
     */
    public function showLoginAttempts(Request $request) : array
    {
        $allowed_columns = ['id', 'username', 'remote_ip', 'user_agent', 'successful', 'created_at'];

        $login_attempts = DB::table('login_attempts');
        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->get('sort'), $allowed_columns) ? $request->get('sort') : 'created_at';

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
    public function listBackups() : array
    {
        $settings = Setting::getSettings();
        $path = 'app/backups';
        $backup_files = Storage::files($path);
        $files_raw = [];
        $count = 0;

        if (count($backup_files) > 0) {

            for ($f = 0; $f < count($backup_files); $f++) {

                // Skip dotfiles like .gitignore and .DS_STORE
                if ((substr(basename($backup_files[$f]), 0, 1) != '.')) {
                    $file_timestamp = Storage::lastModified($backup_files[$f]);

                    $files_raw[] = [
                        'filename' => basename($backup_files[$f]),
                        'filesize' => Setting::fileSizeConvert(Storage::size($backup_files[$f])),
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
     * Downloads a backup file.
     * We use response()->download() here instead of Storage::download() because Storage::download()
     * exhausts memory on larger files.
     *
     * @author [A. Gianotto]
     */
    public function downloadBackup($file) : JsonResponse | BinaryFileResponse
    {

        $path = storage_path('app/backups');
        
        if (Storage::exists('app/backups/'.$file)) {
            $headers = ['ContentType' => 'application/zip'];
            return response()->download($path.'/'.$file, $file, $headers);
        } else {
            return response()->json(Helper::formatStandardApiResponse('error', null,  trans('general.file_not_found')), 404);
        }

    }

    /**
     * Determines and downloads the latest backup
     *
     * @author [A. Gianotto]
     * @since [v6.3.1]
     */
    public function downloadLatestBackup() : JsonResponse | BinaryFileResponse
    {

        $fileData = collect();
        foreach (Storage::files('app/backups') as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) == 'zip') {
                $fileData->push([
                    'file' => $file,
                    'date' => Storage::lastModified($file)
                ]);
            }
        }

        $newest = $fileData->sortByDesc('date')->first();
        if (Storage::exists($newest['file'])) {
            $headers = ['ContentType' => 'application/zip'];
            return response()->download(storage_path($newest['file']), basename($newest['file']), $headers);
        } else {
            return response()->json(Helper::formatStandardApiResponse('error', null,  trans('general.file_not_found')), 404);
        }


    }


}
