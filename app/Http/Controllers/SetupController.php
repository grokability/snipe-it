<?php

namespace App\Http\Controllers;

use App\Http\Requests\SetupUserRequest;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\FirstAdminNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use \Illuminate\Contracts\View\View;


/**
 * This controller handles all actions related to Settings for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class SetupController extends Controller
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
            ->with('section', trans('general.setup_config_check'))
            ->with('icon', 'fa-regular fa-rectangle-list');
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
        $settings->link_light_color = $request->input('link_light_color', '#296282');
        $settings->link_dark_color = $request->input('link_dark_color', '#296282');
        $settings->nav_link_color = $request->input('nav_link_color', '#FFFFFF');
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

            return redirect()
                ->route('setup.done')
                ->with('section', trans('general.setup_create_admin'))
                ->with('icon', 'fa-solid fa-champagne-glasses')
                ->with('success', trans('admin/settings/general.create_admin_success'));
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
            ->with('section', trans('general.setup_create_admin'))
            ->with('icon', 'fa-solid fa-user-plus');
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
            ->with('success', trans('general.create_admin_success'))
            ->with('step', 4)
            ->with('icon', 'fa-solid fa-champagne-glasses fa-shake')
            ->with('section', trans('general.setup_done'));
    }



    /**
     * Migrate the database tables, and return the output
     * to a view for Setup.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v3.0]
     */
    public function setupMigrate()
    {
        Artisan::call('migrate', ['--force' => true]);
        $output = Artisan::output();
        if ((! file_exists(storage_path().'/oauth-private.key')) || (! file_exists(storage_path().'/oauth-public.key'))) {
            Artisan::call('migrate', ['--path' => 'vendor/laravel/passport/database/migrations', '--force' => true]);
            Artisan::call('passport:install', ['--no-interaction' => true]);
        }

        return view('setup/migrate')
            ->with('success', trans('general.create_admin_success'))
            ->with('output', trim($output))
            ->with('step', 2)
            ->with('section', trans('general.setup_create_database'))
            ->with('icon', 'fa-solid fa-database');
    }







}
