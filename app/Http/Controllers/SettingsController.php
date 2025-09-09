<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Helpers\StorageHelper;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\SettingsSamlRequest;
use App\Http\Requests\SetupUserRequest;
use App\Http\Requests\StoreLdapSettings;
use App\Http\Requests\StoreLocalizationSettings;
use App\Http\Requests\StoreNotificationSettings;
use App\Http\Requests\StoreLabelSettings;
use App\Http\Requests\StoreSecuritySettings;
use App\Models\CustomField;
use App\Models\Group;
use App\Models\Labels\Label as LabelModel;
use App\Models\Setting;
use App\Models\Asset;
use App\Models\User;
use App\Notifications\FirstAdminNotification;
use App\Notifications\MailTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use \Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * This controller handles all actions related to Settings for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class SettingsController extends Controller
{
    /**
     * Checks to see whether or not the database has a migrations table
     * and a user, otherwise display the setup view.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v3.0]
     *
     * @return \Illuminate\Contracts\View\View | \Illuminate\Http\Response
     */
    public function getSetupIndex() : View
    {
        $start_settings['php_version_min'] = false;

        if (version_compare(PHP_VERSION, config('app.min_php'), '<')) {
            return response('<center><h1>This software requires PHP version '.config('app.min_php').' or greater. This server is running '.PHP_VERSION.'. </h1><h2>Please upgrade PHP on this server and try again. </h2></center>', 500);
        }

        try {
            $conn = DB::select('select 2 + 2');
            $start_settings['db_conn'] = true;
            $start_settings['db_name'] = DB::connection()->getDatabaseName();
            $start_settings['db_error'] = null;
        } catch (\PDOException $e) {
            $start_settings['db_conn'] = false;
            $start_settings['db_name'] = config('database.connections.mysql.database');
            $start_settings['db_error'] = $e->getMessage();
        }

        $start_settings['url_config'] = trim(config('app.url'), '/'). '/setup';
        $start_settings['real_url']  = request()->url();
        $start_settings['url_valid'] = $start_settings['url_config'] === $start_settings['real_url'];
        $start_settings['php_version_min'] = true;

        // Curl the .env file to make sure it's not accessible via a browser
        $start_settings['env_exposed'] = $this->dotEnvFileIsExposed();

        if (App::Environment('production') && (true == config('app.debug'))) {
            $start_settings['debug_exposed'] = true;
        } else {
            $start_settings['debug_exposed'] = false;
        }

        $environment = app()->environment();
        if ('production' != $environment) {
            $start_settings['env'] = $environment;
            $start_settings['prod'] = false;
        } else {
            $start_settings['env'] = $environment;
            $start_settings['prod'] = true;
        }

        $start_settings['owner'] = '';

        if (function_exists('posix_getpwuid')) { // Probably Linux
            $owner = posix_getpwuid(fileowner($_SERVER['SCRIPT_FILENAME']));
            // This *should* be an array, but we've seen this return a bool in some chrooted environments
            if (is_array($owner)) {
                $start_settings['owner'] = $owner['name'];
            }
        }

        if (($start_settings['owner'] === 'root') || ($start_settings['owner'] === '0')) {
            $start_settings['owner_is_admin'] = true;
        } else {
            $start_settings['owner_is_admin'] = false;
        }

        $start_settings['writable'] = $this->storagePathIsWritable();

        $start_settings['gd'] = extension_loaded('gd');

        return view('setup/index')
            ->with('step', 1)
            ->with('start_settings', $start_settings)
            ->with('section', 'Pre-Flight Check');
    }

    /**
     * Determine if the .env file accessible via a browser.
     *
     * @return bool This method will return true when exceptions (such as curl exception) is thrown.
     * Check the log files to see more details about the exception.
     */
    protected function dotEnvFileIsExposed() : bool
    {
        try {
            return Http::withoutVerifying()->timeout(10)
                ->accept('*/*')
                ->get(URL::to('.env'))
                ->successful();
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
            return true;
        }
    }

    /**
     * Determine if the app storage path is writable.
     */
    protected function storagePathIsWritable(): bool
    {
        return File::isWritable(storage_path())                  &&
            File::isWritable(storage_path('framework'))          &&
            File::isWritable(storage_path('framework/cache'))    &&
            File::isWritable(storage_path('framework/sessions')) &&
            File::isWritable(storage_path('framework/views'))    &&
            File::isWritable(storage_path('logs'));
    }

    /**
     * Save the first admin user from Setup.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     *
     */
    public function postSaveFirstAdmin(SetupUserRequest $request) : RedirectResponse
    {

        $user = new User();
        $user->first_name = $data['first_name'] = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->email = $data['email'] = $request->input('email');
        $user->activated = 1;
        $permissions = ['superuser' => 1];
        $user->permissions = json_encode($permissions);
        $user->username = $data['username'] = $request->input('username');
        $user->password = bcrypt($request->input('password'));
        $data['password'] = $request->input('password');

        $settings = new Setting();
        $settings->full_multiple_companies_support = $request->input('full_multiple_companies_support', 0);
        $settings->site_name = $request->input('site_name');
        $settings->alert_email = $request->input('email');
        $settings->alerts_enabled = 1;
        $settings->pwd_secure_min = 10;
        $settings->brand = 1;
        $settings->locale = $request->input('locale', 'en-US');
        $settings->default_currency = $request->input('default_currency', 'USD');
        $settings->created_by = 1;
        $settings->email_domain = $request->input('email_domain');
        $settings->email_format = $request->input('email_format');
        $settings->next_auto_tag_base = 1;
        $settings->auto_increment_assets = $request->input('auto_increment_assets', 0);
        $settings->auto_increment_prefix = $request->input('auto_increment_prefix');
        $settings->zerofill_count = $request->input('zerofill_count') ?: 0;

        if ((! $user->isValid()) || (! $settings->isValid())) {
            return redirect()->back()->withInput()->withErrors($user->getErrors())->withErrors($settings->getErrors());
        } else {
            $user->save();
            Auth::login($user, true);
            $settings->save();

            if ($request->input('email_creds') == '1') {
                $data = [];
                $data['email'] = $user->email;
                $data['username'] = $user->username;
                $data['first_name'] = $user->first_name;
                $data['last_name'] = $user->last_name;
                $data['password'] = $request->input('password');
                $user->notify(new FirstAdminNotification($data));
            }

            return redirect()->route('setup.done');
        }
    }

    /**
     * Return the admin user creation form in Setup.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v3.0]
     */
    public function getSetupUser() : View
    {
        return view('setup/user')
            ->with('step', 3)
            ->with('section', 'Create a User');
    }

    /**
     * Return the view that tells the user that the Setup is done.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v3.0]
     */
    public function getSetupDone() : View
    {
        return view('setup/done')
            ->with('step', 4)
            ->with('section', 'Done!');
    }

    /**
     * Migrate the database tables, and return the output
     * to a view for Setup.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v3.0]
     */
    public function getSetupMigrate() : View
    {
        Artisan::call('migrate', ['--force' => true]);
        if ((! file_exists(storage_path().'/oauth-private.key')) || (! file_exists(storage_path().'/oauth-public.key'))) {
            Artisan::call('migrate', ['--path' => 'vendor/laravel/passport/database/migrations', '--force' => true]);
            Artisan::call('passport:install', ['--no-interaction' => true]);
        }

        return view('setup/migrate')
            ->with('output', 'Databases installed!')
            ->with('step', 2)
            ->with('section', 'Create Database Tables');
    }

    /**
     * Return a view that shows some of the key settings.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     */
    public function index() : View
    {
        $settings = Setting::getSettings();

        return view('settings/index', compact('settings'));
    }


    /**
     * Return a form to allow a super admin to update settings.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     */
    public function getSettings() : View
    {
        $setting = Setting::getSettings();
        return view('settings/general', compact('setting'));
    }

    /**
     * Return a form to allow a super admin to update settings.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     */
    public function postSettings(Request $request) : RedirectResponse

    {
        if (is_null($setting = Setting::getSettings())) {
            return redirect()->to('admin')->with('error', trans('admin/settings/message.update.error'));
        }

        $setting->modellist_displays = '';

        if (($request->filled('show_in_model_list')) && (count($request->input('show_in_model_list')) > 0)) {
            $setting->modellist_displays = implode(',', $request->input('show_in_model_list'));
        }

        $old_locations_fmcs = $setting->scope_locations_fmcs;
        $setting->full_multiple_companies_support = $request->input('full_multiple_companies_support', '0');
        $setting->scope_locations_fmcs = $request->input('scope_locations_fmcs', '0');

        // Backward compatibility for locations makes no sense without FullMultipleCompanySupport
        if (!$setting->full_multiple_companies_support) {
            $setting->scope_locations_fmcs = '0';
        }

        // check for inconsistencies when activating scoped locations
        if ($old_locations_fmcs == '0' && $setting->scope_locations_fmcs == '1') {
            $mismatched = Helper::test_locations_fmcs(false);
            if (count($mismatched) != 0) {
                return redirect()->back()->withInput()->with('error', trans_choice('admin/settings/message.location_scoping.mismatch', count($mismatched)).' '.trans('admin/settings/message.location_scoping.not_saved'));
            }
        }

        $setting->unique_serial = $request->input('unique_serial', '0');
        $setting->shortcuts_enabled = $request->input('shortcuts_enabled', '0');
        $setting->show_images_in_email = $request->input('show_images_in_email', '0');
        $setting->show_archived_in_list = $request->input('show_archived_in_list', '0');
        $setting->dashboard_message = $request->input('dashboard_message');
        $setting->email_domain = $request->input('email_domain');
        $setting->email_format = $request->input('email_format');
        $setting->username_format = $request->input('username_format');
        $setting->require_accept_signature = $request->input('require_accept_signature');
        $setting->show_assigned_assets = $request->input('show_assigned_assets', '0');
        if (! config('app.lock_passwords')) {
            $setting->login_note = $request->input('login_note');
        }

        $setting->default_eula_text = $request->input('default_eula_text');
        $setting->thumbnail_max_h = $request->input('thumbnail_max_h');
        $setting->privacy_policy_link = $request->input('privacy_policy_link');
        $setting->depreciation_method = $request->input('depreciation_method');
        $setting->dash_chart_type = $request->input('dash_chart_type');
        $setting->profile_edit = $request->input('profile_edit', 0);
        $setting->require_checkinout_notes = $request->input('require_checkinout_notes', 0);
        $setting->manager_view_enabled = $request->input('manager_view_enabled', 0);


        if ($request->input('per_page') != '') {
            $setting->per_page = $request->input('per_page');
        } else {
            $setting->per_page = 200;
        }

        if ($setting->save()) {
            return redirect()->route('settings.index')
                ->with('success', trans('admin/settings/message.update.success'));
        }

        return redirect()->back()->withInput()->withErrors($setting->getErrors());
    }


    /**
     * Return a form to allow a super admin to update settings.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     */
    public function getBranding() : View
    {
        $setting = Setting::getSettings();

        return view('settings.branding', compact('setting'));
    }

    /**
     * Return a form to allow a super admin to update settings.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     */
    public function postBranding(ImageUploadRequest $request) : RedirectResponse
    {
        // Something has gone horribly wrong - no settings record exists!
        if (is_null($setting = Setting::getSettings())) {
            return redirect()->to('admin')->with('error', trans('admin/settings/message.update.error'));
        }

        $setting->brand = $request->input('brand', '1');
        $setting->header_color = $request->input('header_color');
        $setting->support_footer = $request->input('support_footer');
        $setting->version_footer = $request->input('version_footer');
        $setting->footer_text = $request->input('footer_text');
        $setting->skin = $request->input('skin');
        $setting->allow_user_skin = $request->input('allow_user_skin', '0');
        $setting->show_url_in_emails = $request->input('show_url_in_emails', '0');
        $setting->logo_print_assets = $request->input('logo_print_assets', '0');
        $setting->load_remote = $request->input('load_remote', 0);

        // Only allow the site name, images, and CSS to be changed if lock_passwords is false
        // Because public demos make people act like dicks

        if (!config('app.lock_passwords')) {

            if ($request->has('site_name')) {
                $request->validate(['site_name' => 'required']);
            }

            $setting->site_name = $request->input('site_name', 'Snipe-IT');
            $setting->custom_css = $request->input('custom_css');

            // Logo upload
            $setting = $request->handleImages($setting, 600, 'logo', '', 'logo');

            if ($request->input('clear_logo') == '1') {
                $setting = $request->deleteExistingImage($setting, '', 'logo');
                $setting->logo = null;
                $setting->brand = 1;
            }

            // Email logo upload
            $setting = $request->handleImages($setting, 600, 'email_logo', '', 'email_logo');
            if ($request->input('clear_email_logo') == '1') {
                $setting = $request->deleteExistingImage($setting, '', 'email_logo');
                $setting->email_logo = null;
            }

             // Label logo upload
            $setting = $request->handleImages($setting, 600, 'label_logo', '', 'label_logo');

            if ($request->input('clear_label_logo') == '1') {
                $setting = $request->deleteExistingImage($setting, '', 'label_logo');
                $setting->label_logo = null;
            }

            // Acceptance PDF upload
            $setting = $request->handleImages($setting, 600, 'acceptance_pdf_logo', '', 'acceptance_pdf_logo');
            if ('1' == $request->input('clear_acceptance_pdf_logo')) {
                $setting = $request->deleteExistingImage($setting, '', 'acceptance_pdf_logo');
                $setting->acceptance_pdf_logo = null;
            }

            // Favicon upload
            $setting = $request->handleImages($setting, 100, 'favicon', '', 'favicon');
            if ('1' == $request->input('clear_favicon')) {
                $setting = $request->deleteExistingImage($setting, '', 'favicon');
                $setting->favicon = null;
            }


            // Default avatar upload
            $setting = $request->handleImages($setting, 500, 'default_avatar', 'avatars', 'default_avatar');
            if ($request->input('clear_default_avatar') == '1')  {
                // Don't delete the file, just update the field if this is the default
                if ($setting->default_avatar!='default.png') {
                    $setting = $request->deleteExistingImage($setting, 'avatars', 'default_avatar');
                }
                $setting->default_avatar = null;
            }

            if ($request->input('restore_default_avatar') == '1')  {
                $setting->default_avatar = 'default.png';
            }
        }

        if ($setting->save()) {
            return redirect()->route('settings.index')
                ->with('success', trans('admin/settings/message.update.success'));
        }

        return redirect()->back()->withInput()->withErrors($setting->getErrors());
    }


    /**
     * Return a form to allow a super admin to update settings.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     */
    public function getSecurity() : View
    {
        $setting = Setting::getSettings();

        return view('settings.security', compact('setting'));
    }

    /**
     * Return a form to allow a super admin to update settings.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     */
    public function postSecurity(StoreSecuritySettings $request) : RedirectResponse
    {
        $this->validate($request, [
            'pwd_secure_complexity' => 'array',
            'pwd_secure_complexity.*' => [
                Rule::in([
                    'disallow_same_pwd_as_user_fields',
                    'letters',
                    'numbers',
                    'symbols',
                    'case_diff',
                ])
            ]
        ]);

        if (is_null($setting = Setting::getSettings())) {
            return redirect()->to('admin')->with('error', trans('admin/settings/message.update.error'));
        }
        if (! config('app.lock_passwords')) {
            if ('' == $request->input('two_factor_enabled')) {
                $setting->two_factor_enabled = null;
            } else {
                $setting->two_factor_enabled = $request->input('two_factor_enabled');
            }

            // remote user login
            $setting->login_remote_user_enabled = (int) $request->input('login_remote_user_enabled');
            $setting->login_common_disabled = (int) $request->input('login_common_disabled');
            $setting->login_remote_user_custom_logout_url = $request->input('login_remote_user_custom_logout_url');
            $setting->login_remote_user_header_name = $request->input('login_remote_user_header_name');
        }

        $setting->pwd_secure_uncommon = (int) $request->input('pwd_secure_uncommon');
        $setting->pwd_secure_min = (int) $request->input('pwd_secure_min');
        $setting->pwd_secure_complexity = '';


        if ($request->filled('pwd_secure_complexity')) {
            $setting->pwd_secure_complexity = implode('|', $request->input('pwd_secure_complexity'));
        }

        if ($setting->save()) {
            return redirect()->route('settings.index')
                ->with('success', trans('admin/settings/message.update.success'));
        }

        return redirect()->back()->withInput()->withErrors($setting->getErrors());
    }

    /**
     * Return a form to allow a super admin to update settings.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     */
    public function getLocalization() : View
    {
        $setting = Setting::getSettings();

        return view('settings.localization', compact('setting'));
    }

    /**
     * Return a form to allow a super admin to update settings.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     */
    public function postLocalization(StoreLocalizationSettings $request) : RedirectResponse
    {
        if (is_null($setting = Setting::getSettings())) {
            return redirect()->to('admin')->with('error', trans('admin/settings/message.update.error'));
        }

        if (! config('app.lock_passwords')) {
            $setting->locale = $request->input('locale', 'en-US');
        }
        $setting->default_currency = $request->input('default_currency', '$');
        $setting->date_display_format = $request->input('date_display_format');
        $setting->time_display_format = $request->input('time_display_format');
        $setting->digit_separator = $request->input('digit_separator');
        $setting->name_display_format = $request->input('name_display_format');

        if ($setting->save()) {
            return redirect()->route('settings.index')
                ->with('success', trans('admin/settings/message.update.success'));
        }

        return redirect()->back()->withInput()->withErrors($setting->getErrors());
    }

    /**
     * Return a form to allow a super admin to update settings.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     */
    public function getAlerts() : View
    {
        $setting = Setting::getSettings();

        return view('settings.alerts', compact('setting'));
    }

    /**
     * Return a form to allow a super admin to update settings.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     */
    public function postAlerts(StoreNotificationSettings $request) : RedirectResponse
    {
        if (is_null($setting = Setting::getSettings())) {
            return redirect()->to('admin')->with('error', trans('admin/settings/message.update.error'));
        }

        // Check if the audit interval has changed - if it has, we want to update ALL of the assets audit dates
        if ($request->input('audit_interval') != $setting->audit_interval) {

            // This could be a negative number if the user is trying to set the audit interval to a lower number than it was before
            $audit_diff_months = ((int)$request->input('audit_interval') - (int)($setting->audit_interval));

            // Batch update the dates. We have to use this method to avoid time limit exceeded errors on very large datasets,
            // but it DOES mean this change doesn't get logged in the action logs, since it skips the observer.
            // @see https://stackoverflow.com/questions/54879160/laravel-observer-not-working-on-bulk-insert
            $affected = Asset::whereNotNull('next_audit_date')
                ->whereNull('deleted_at')
                ->update(
                    ['next_audit_date' => DB::raw('DATE_ADD(next_audit_date, INTERVAL '.$audit_diff_months.' MONTH)')]
            );

            Log::debug($affected .' assets affected by audit interval update');


        }

        $alert_email = rtrim($request->input('alert_email'), ',');
        $alert_email = trim($alert_email);
        $admin_cc_email = rtrim($request->input('admin_cc_email'), ',');
        $admin_cc_email = trim($admin_cc_email);

        $setting->alert_email = $alert_email;
        $setting->admin_cc_email = $admin_cc_email;
        $setting->admin_cc_always = $request->validated('admin_cc_always');
        $setting->alerts_enabled = $request->input('alerts_enabled', '0');
        $setting->alert_interval = $request->input('alert_interval');
        $setting->alert_threshold = $request->input('alert_threshold');
        $setting->audit_interval = $request->input('audit_interval');
        $setting->audit_warning_days = $request->input('audit_warning_days');
        $setting->due_checkin_days = $request->input('due_checkin_days');
        $setting->show_alerts_in_menu = $request->input('show_alerts_in_menu', '0');

        if ($setting->save()) {
            return redirect()->route('settings.index')
                ->with('success', trans('admin/settings/message.update.success'));
        }

        return redirect()->back()->withInput()->withErrors($setting->getErrors());
    }

    /**
     * Return a form to allow a super admin to update settings.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     */
    public function getSlack() : View
    {
        $setting = Setting::getSettings();

        return view('settings.slack', compact('setting'));
    }

    /**
     * Return a form to allow a super admin to update settings.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     */
    public function getAssetTags() : View
    {
        $setting = Setting::getSettings();

        return view('settings.asset_tags', compact('setting'));
    }

    /**
     * Saves settings from form.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     */
    public function postAssetTags(Request $request) : RedirectResponse
    {
        if (is_null($setting = Setting::getSettings())) {
            return redirect()->to('admin')->with('error', trans('admin/settings/message.update.error'));
        }

        $setting->auto_increment_prefix = $request->input('auto_increment_prefix');
        $setting->auto_increment_assets = $request->input('auto_increment_assets', '0');
        $setting->zerofill_count = $request->input('zerofill_count');
        $setting->next_auto_tag_base = $request->input('next_auto_tag_base');

        if ($setting->save()) {
            return redirect()->route('settings.index')
                ->with('success', trans('admin/settings/message.update.success'));
        }

        return redirect()->back()->withInput()->withErrors($setting->getErrors());
    }

    /**
     * Return a form to allow a super admin to update settings.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     */
    public function getPhpInfo() : View | RedirectResponse
    {
        if (config('app.debug') === true) {
            return view('settings.phpinfo');
        }

        return redirect()->route('settings.index')
            ->with('error', 'PHP syetem debugging information is only available when debug is enabled in your .env file.');
    }

    /**
     * Return a form to allow a super admin to update settings.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     */
    public function getLabels() : View
    {
        $is_gd_installed = extension_loaded('gd');

        return view('settings.labels')
            ->with('setting', Setting::getSettings())
            ->with('is_gd_installed', $is_gd_installed)
            ->with('customFields', CustomField::where('field_encrypted', '=', 0)->get());
    }

    /**
     * Saves settings from form.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     */
    public function postLabels(StoreLabelSettings $request) : RedirectResponse
    {
        if (is_null($setting = Setting::getSettings())) {
            return redirect()->to('admin')->with('error', trans('admin/settings/message.update.error'));
        }
        $setting->label2_enable = $request->input('label2_enable');
        $setting->label2_template = $request->input('label2_template');
        $setting->label2_title = $request->input('label2_title');
        $setting->label2_asset_logo = $request->input('label2_asset_logo');
        $setting->label2_1d_type = $request->input('label2_1d_type');
        $setting->label2_2d_type = $request->input('label2_2d_type');
        $setting->label2_2d_target = $request->input('label2_2d_target');
        $setting->label2_fields = $request->input('label2_fields');
        $setting->label2_empty_row_count = $request->input('label2_empty_row_count');
        $setting->labels_per_page = $request->input('labels_per_page');
        $setting->labels_width = $request->input('labels_width');
        $setting->labels_height = $request->input('labels_height');
        $setting->labels_pmargin_left = $request->input('labels_pmargin_left');
        $setting->labels_pmargin_right = $request->input('labels_pmargin_right');
        $setting->labels_pmargin_top = $request->input('labels_pmargin_top');
        $setting->labels_pmargin_bottom = $request->input('labels_pmargin_bottom');
        $setting->labels_display_bgutter = $request->input('labels_display_bgutter');
        $setting->labels_display_sgutter = $request->input('labels_display_sgutter');
        $setting->labels_fontsize = $request->input('labels_fontsize');
        $setting->labels_pagewidth = $request->input('labels_pagewidth');
        $setting->labels_pageheight = $request->input('labels_pageheight');
        $setting->labels_display_company_name = $request->input('labels_display_company_name', '0');

        //Barcodes
        $setting->qr_code = $request->input('qr_code', '0');
        //1D-Barcode
        $setting->alt_barcode_enabled = $request->input('alt_barcode_enabled', '0');
        //QR-Code
        $setting->qr_text = $request->input('qr_text');

        if ($request->filled('labels_display_name')) {
            $setting->labels_display_name = 1;
        } else {
            $setting->labels_display_name = 0;
        }

        if ($request->filled('labels_display_serial')) {
            $setting->labels_display_serial = 1;
        } else {
            $setting->labels_display_serial = 0;
        }

        if ($request->filled('labels_display_tag')) {
            $setting->labels_display_tag = 1;
        } else {
            $setting->labels_display_tag = 0;
        }

        if ($request->filled('labels_display_tag')) {
            $setting->labels_display_tag = 1;
        } else {
            $setting->labels_display_tag = 0;
        }

        if ($request->filled('labels_display_model')) {
            $setting->labels_display_model = 1;
        } else {
            $setting->labels_display_model = 0;
        }

        if ($setting->save()) {

            return redirect()->route('settings.labels.index')
                ->with('success', trans('admin/settings/message.update.success'));
        }

        return redirect()->back()->withInput()->withErrors($setting->getErrors());
    }

    /**
     * Return a form to allow a super admin to update settings.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     */
    public function getLdapSettings() : View
    {
        $setting = Setting::getSettings();
        $groups = Group::pluck('name', 'id');
        return view('settings.ldap', compact('setting', 'groups'));
    }

    /**
     * Saves settings from form.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     */
    public function postLdapSettings(StoreLdapSettings $request) : RedirectResponse
    {
        if (is_null($setting = Setting::getSettings())) {
            return redirect()->to('admin')->with('error', trans('admin/settings/message.update.error'));
        }

        if (! config('app.lock_passwords') === true) {
            $setting->ldap_enabled = $request->input('ldap_enabled', '0');
            $setting->ldap_server = $request->input('ldap_server');
            $setting->ldap_server_cert_ignore = $request->input('ldap_server_cert_ignore', false);
            $setting->ldap_uname = $request->input('ldap_uname');
            if ($request->filled('ldap_pword')) {
                $setting->ldap_pword = Crypt::encrypt($request->input('ldap_pword'));
            }
            $setting->ldap_basedn = $request->input('ldap_basedn');
            $setting->ldap_default_group = $request->input('ldap_default_group');
            $setting->ldap_filter = $request->input('ldap_filter');
            $setting->ldap_username_field = $request->input('ldap_username_field');
            $setting->ldap_display_name = $request->input('ldap_display_name');
            $setting->ldap_lname_field = $request->input('ldap_lname_field');
            $setting->ldap_fname_field = $request->input('ldap_fname_field');
            $setting->ldap_auth_filter_query = $request->input('ldap_auth_filter_query');
            $setting->ldap_version = $request->input('ldap_version', 3);
            $setting->ldap_active_flag = $request->input('ldap_active_flag', 0);
            $setting->ldap_invert_active_flag = $request->input('ldap_invert_active_flag', 0);
            $setting->ldap_emp_num = $request->input('ldap_emp_num');
            $setting->ldap_email = $request->input('ldap_email');
            $setting->ldap_manager = $request->input('ldap_manager');
            $setting->ad_domain = $request->input('ad_domain');
            $setting->is_ad = $request->input('is_ad', '0');
            $setting->ad_append_domain = $request->input('ad_append_domain', '0');
            $setting->ldap_tls = $request->input('ldap_tls', '0');
            $setting->ldap_pw_sync = $request->input('ldap_pw_sync', '0');
            $setting->custom_forgot_pass_url = $request->input('custom_forgot_pass_url');
            $setting->ldap_phone_field = $request->input('ldap_phone');
            $setting->ldap_mobile = $request->input('ldap_mobile');
            $setting->ldap_jobtitle = $request->input('ldap_jobtitle');
            $setting->ldap_address = $request->input('ldap_address');
            $setting->ldap_city = $request->input('ldap_city');
            $setting->ldap_state = $request->input('ldap_state');
            $setting->ldap_zip = $request->input('ldap_zip');
            $setting->ldap_country = $request->input('ldap_country');
            $setting->ldap_location = $request->input('ldap_location');
            $setting->ldap_dept = $request->input('ldap_dept');
            $setting->ldap_client_tls_cert   = $request->input('ldap_client_tls_cert');
            $setting->ldap_client_tls_key    = $request->input('ldap_client_tls_key');
        }

        if ($setting->save()) {
            return redirect()->route('settings.ldap.index')
                ->with('success', trans('admin/settings/message.update.success'));
        }

        return redirect()->back()->withInput()->withErrors($setting->getErrors());
    }

    /**
     * Return a form to allow a super admin to update settings.
     *
     * @author Johnson Yi <jyi.dev@outlook.com>
     * @since v5.0.0
     */
    public function getSamlSettings() : View
    {
        $setting = Setting::getSettings();
        return view('settings.saml', compact('setting'));
    }

    /**
     * Saves settings from form.
     *
     * @author Johnson Yi <jyi.dev@outlook.com>
     * @since v5.0.0
     */
    public function postSamlSettings(SettingsSamlRequest $request) : RedirectResponse
    {       
        if (is_null($setting = Setting::getSettings())) {
            return redirect()->to('admin')->with('error', trans('admin/settings/message.update.error'));
        }

        $setting->saml_enabled = $request->input('saml_enabled', '0');
        $setting->saml_idp_metadata = $request->input('saml_idp_metadata');
        $setting->saml_attr_mapping_username = $request->input('saml_attr_mapping_username');
        $setting->saml_forcelogin = $request->input('saml_forcelogin', '0');
        $setting->saml_slo = $request->input('saml_slo', '0');
        if (! empty($request->input('saml_sp_privatekey'))) {
            $setting->saml_sp_x509cert = $request->input('saml_sp_x509cert');
            $setting->saml_sp_privatekey = $request->input('saml_sp_privatekey');
        }
        if (! empty($request->input('saml_sp_x509certNew'))) {
            $setting->saml_sp_x509certNew = $request->input('saml_sp_x509certNew');
        } else {
            $setting->saml_sp_x509certNew = '';
        }
        $setting->saml_custom_settings = $request->input('saml_custom_settings');

        if ($setting->save()) {
            return redirect()->route('settings.saml.index')
                ->with('success', trans('admin/settings/message.update.success'));
        }

        return redirect()->back()->withInput()->withErrors($setting->getErrors());
    }

    /**
     * Do we need this? Can we not just call getSettings() directly?
     */
    public static function getPDFBranding() : Setting
    {
        $pdf_branding = Setting::getSettings();
        return $pdf_branding;
    }


    /**
     * Show Google login settings form
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v6.1.1]
     */
    public function getGoogleLoginSettings() : View
    {
        $setting = Setting::getSettings();
        return view('settings.google', compact('setting'));
    }

    /**
     * ShSaveow Google login settings form
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v6.1.1]
     */
    public function postGoogleLoginSettings(Request $request) : RedirectResponse
    {
        if (!config('app.lock_passwords')) {
            $setting = Setting::getSettings();

            $setting->google_login = $request->input('google_login', 0);
            $setting->google_client_id = $request->input('google_client_id');
            $setting->google_client_secret = $request->input('google_client_secret');

            if ($setting->save()) {
                return redirect()->route('settings.index')
                    ->with('success', trans('admin/settings/message.update.success'));
            }

            return redirect()->back()->withInput()->withErrors($setting->getErrors());
        }

        return redirect()->back()->with('error', trans('general.feature_disabled'));
    }


    /**
     * Show the listing of backups.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.8]
     */
    public function getBackups() : View
    {
        $settings = Setting::getSettings();
        $path = 'app/backups';
        $backup_files = Storage::files($path);
        $files_raw = [];

        if (count($backup_files) > 0) {
            for ($f = 0; $f < count($backup_files); $f++) {

                // Skip dotfiles like .gitignore and .DS_STORE
                if ((substr(basename($backup_files[$f]), 0, 1) != '.')) {
                    //$lastmodified = Carbon::parse(Storage::lastModified($backup_files[$f]))->toDatetimeString();
                    $file_timestamp = Storage::lastModified($backup_files[$f]);

                    $files_raw[] = [
                        'filename' => basename($backup_files[$f]),
                        'filesize' => Setting::fileSizeConvert(Storage::size($backup_files[$f])),
                        'modified_value' => $file_timestamp,
                        'modified_display' => date($settings->date_display_format.' '.$settings->time_display_format, $file_timestamp),

                    ];
                }
            }
        }

        // Reverse the array so it lists oldest first
        $files = array_reverse($files_raw);

        return view('settings/backups', compact('path', 'files'));
    }

    /**
     * Process the backup.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.8]
     */
    public function postBackups() : RedirectResponse
    {
        if (! config('app.lock_passwords')) {
            Artisan::call('snipeit:backup', ['--filename' => 'manual-backup-'.date('Y-m-d-H-i-s')]);
            $output = Artisan::output();

            // Backup completed
            if (! preg_match('/failed/', $output)) {
                return redirect()->route('settings.backups.index')
                    ->with('success', trans('admin/settings/message.backup.generated'));
            }

            $formatted_output = str_replace('Backup completed!', '', $output);
            $output_split = explode('...', $formatted_output);

            if (array_key_exists(2, $output_split)) {
                return redirect()->route('settings.backups.index')->with('error', $output_split[2]);
            }

            return redirect()->route('settings.backups.index')->with('error', $formatted_output);
        }

        return redirect()->route('settings.backups.index')->with('error', trans('general.feature_disabled'));
    }

    /**
     * Download the backup file.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.8]
     */
    public function downloadFile($filename = null) : RedirectResponse | BinaryFileResponse
    {
        $path = 'app/backups';

        if (! config('app.lock_passwords')) {
            if (Storage::exists($path.'/'.$filename)) {
                Log::warning('User '.auth()->user()->username.' is attempting to download backup file: '.$filename);
                return StorageHelper::downloader($path.'/'.$filename);
            } else {
                // Redirect to the backup page
                return redirect()->route('settings.backups.index')->with('error', trans('admin/settings/message.backup.file_not_found'));
            }
        } else {
            // Redirect to the backup page
            return redirect()->route('settings.backups.index')->with('error', trans('general.feature_disabled'));
        }
    }

    /**
     * Delete the backup file.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.8]
     */
    public function deleteFile($filename = null) : RedirectResponse
    {
        if (config('app.allow_backup_delete')=='true') {

            if (!config('app.lock_passwords')) {
                $path = 'app/backups';

                if (Storage::exists($path . '/' . $filename)) {

                    try {
                        Log::warning('User '.auth()->user()->username.' is attempting to delete backup file: '.$filename);
                        Storage::delete($path . '/' . $filename);
                        return redirect()->route('settings.backups.index')->with('success', trans('admin/settings/message.backup.file_deleted'));
                    } catch (\Exception $e) {
                        Log::debug($e);
                    }
                } else {
                    return redirect()->route('settings.backups.index')->with('error', trans('admin/settings/message.backup.file_not_found'));
                }
            }

            return redirect()->route('settings.backups.index')->with('error', trans('general.feature_disabled'));
        }

        // Hell to the no
        Log::warning('User ID '.auth()->id().' is attempting to delete backup file '.$filename.' and is not authorized to.');
        return redirect()->route('settings.backups.index')->with('error', trans('general.backup_delete_not_allowed'));
    }


    /**
     * Uploads a backup file
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v6.0]
     */

    public function postUploadBackup(Request $request) : RedirectResponse
    {

        if (! config('app.lock_passwords')) {
            if (!$request->hasFile('file')) {
                return redirect()->route('settings.backups.index')->with('error', 'No file uploaded');
            } else {

                $max_file_size = Helper::file_upload_max_size();
                $validator = Validator::make($request->all(), [
                    'file' => 'required|mimes:zip|max:'.$max_file_size,
                ]);

                if ($validator->passes()) {

                        $upload_filename = 'uploaded-'.date('U').'-'.Str::slug(pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_FILENAME)).'.zip';

                        Storage::putFileAs('app/backups', $request->file('file'), $upload_filename);

                        return redirect()->route('settings.backups.index')->with('success', 'File uploaded');
                }

                return redirect()->route('settings.backups.index')->withErrors($validator);
            }
        } else {
            return redirect()->route('settings.backups.index')->with('error', trans('general.feature_disabled'));
        }
    }

    /**
     * Restore the backup file.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v6.0]
     */
    public function postRestore(Request $request, $filename = null): RedirectResponse
    {

        if (! config('app.lock_passwords')) {
            $path = 'app/backups';

            if (Storage::exists($path.'/'.$filename)) {

                // grab the user's info so we can make sure they exist in the system
                $user = User::find(auth()->id());

                // TODO: run a backup


                Artisan::call('db:wipe', [
                    '--force' => true,
                ]);

                Log::warning('User '.auth()->user()->username.' is attempting to restore from: '. storage_path($path).'/'.$filename);

                $restore_params = [
                    '--force' => true,
                    '--no-progress' => true,
                    'filename' => storage_path($path) . '/' . $filename
                ];

                if ($request->input('clean')) {
                    Log::debug("Attempting 'clean' - first, guessing prefix...");
                    Artisan::call('snipeit:restore', [
                        '--sanitize-guess-prefix' => true,
                        'filename' => storage_path($path) . '/' . $filename
                    ]);
                    $guess_prefix_output = Artisan::output();
                    Log::debug("Sanitize output is: $guess_prefix_output");
                    list($prefix, $_output) = explode("\n", $guess_prefix_output);
                    Log::debug("prefix is: '$prefix'");
                    $restore_params['--sanitize-with-prefix'] = $prefix;
                }

                // run the restore command
                Artisan::call('snipeit:restore',
                    $restore_params
                );

                // If it's greater than 300, it probably worked
                $output = Artisan::output();

                /* Run migrations */
                Log::debug('Migrating database...');
                Artisan::call('migrate', ['--force' => true]);
                $migrate_output = Artisan::output();
                Log::debug($migrate_output);

                $find_user = DB::table('users')->where('username', $user->username)->exists();

                if (!$find_user){
                    Log::warning('Attempting to restore user: ' . $user->username);
                    $new_user = $user->replicate();
                    $new_user->push();
                } else {
                    Log::debug('User: ' . $user->username .' already exists.');
                }

                Log::debug('Logging all users out..');
                Artisan::call('snipeit:global-logout', ['--force' => true]);

                DB::table('users')->update(['remember_token' => null]);
                Auth::logout();

                return redirect()->route('login')->with('success', trans('admin/settings/message.restore.success'));
            } else {
                return redirect()->route('settings.backups.index')->with('error', trans('admin/settings/message.backup.file_not_found'));
            }
        } else {
            return redirect()->route('settings.backups.index')->with('error', trans('general.feature_disabled'));
        }
    }

    /**
     * Return a form to allow a super admin to update settings.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     */
    public function getPurge() : View | RedirectResponse
    {

        Log::warning('User '.auth()->user()->username.' (ID: '.auth()->id().') is attempting a PURGE');

        if (config('app.allow_purge')=='true') {
            return view('settings.purge-form');
        }

        return redirect()->route('settings.index')->with('error', trans('general.purge_not_allowed'));
    }

    /**
     * Purges soft-deletes.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     */
    public function postPurge(Request $request) : RedirectResponse
    {
        Log::warning('User '.auth()->user()->username.' (ID'.auth()->id().') is attempting a PURGE');

        if (config('app.allow_purge')=='true') {
            Log::debug('Purging is not allowed via the .env');

            if (!config('app.lock_passwords')) {

                if ($request->input('confirm_purge')=='DELETE') {

                    Log::warning('User ID ' . auth()->id() . ' initiated a PURGE!');
                    // Run a backup immediately before processing
                    Artisan::call('backup:run');
                    Artisan::call('snipeit:purge', ['--force' => 'true', '--no-interaction' => true]);
                    $output = Artisan::output();

                    return redirect()->route('settings.index')
                        ->with('output', $output)->with('success', trans('admin/settings/message.purge.success'));
                } else {
                    return redirect()->route('settings.purge.index')
                        ->with('error', trans('admin/settings/message.purge.validation_failed'));
                }
            } else {
                return redirect()->route('settings.index')
                    ->with('error', trans('general.feature_disabled'));
            }
        }

        Log::error('User '.auth()->user()->username.' (ID'.auth()->id().') is attempting to purge deleted data and is not authorized to.');


        // Nope.
        return redirect()->route('settings.index')
            ->with('error', trans('general.purge_not_allowed'));
    }

    /**
     * Returns a page with the API token generation interface.
     *
     * We created a controller method for this because closures aren't allowed
     * in the routes file if you want to be able to cache the routes.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     */
    public function api() : View
    {
        return view('settings.api');
    }

    /**
     * Test the email configuration.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     */
    public function ajaxTestEmail() : JsonResponse
    {
        try {
            (new User())->forceFill([
                'name'  => config('mail.from.name'),
                'email' => config('mail.from.address'),
            ])->notify(new MailTest());
            Log::debug('Attempting to send mail to '.config('mail.from.address'));
            return response()->json(Helper::formatStandardApiResponse('success', null, trans('mail_sent.mail_sent')));
        } catch (\Exception $e) {
            Log::error('Mail sent from '.config('mail.from.address') .' with errors '. $e->getMessage());
            Log::debug($e);
            return response()->json(Helper::formatStandardApiResponse('success', null, $e->getMessage()));
        }
    }



    /**
     * Get login attempts view
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     */
    public function getLoginAttempts() : View
    {
        return view('settings.logins');
    }
}
