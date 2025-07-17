<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use App\Http\Transformers\ProfileTransformer;
use App\Models\Actionlog;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\CurrentInventory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use \Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * This controller handles all actions related to User Profiles for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class ProfileController extends Controller
{
    /**
     * Returns a view with the user's profile form for editing
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     */
    public function getIndex() : View
    {
        $this->authorize('self.profile');
        $user = auth()->user();
        return view('account/profile', compact('user'));
    }

    /**
     * Validates and stores the user's update data.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     */
    public function postIndex(ImageUploadRequest $request) : RedirectResponse
    {
        $this->authorize('self.profile');
        $user = auth()->user();
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->website = $request->input('website');
        $user->gravatar = $request->input('gravatar');
        $user->skin = $request->input('skin');
        $user->phone = $request->input('phone');
        $user->enable_sounds = $request->input('enable_sounds', false);
        $user->enable_confetti = $request->input('enable_confetti', false);

        if (! config('app.lock_passwords')) {
            $user->locale = $request->input('locale');
        }

        if ((Gate::allows('self.two_factor')) && ((Setting::getSettings()->two_factor_enabled == '1') && (! config('app.lock_passwords')))) {
            $user->two_factor_optin = $request->input('two_factor_optin', '0');
        }

        if (Gate::allows('self.edit_location') && (! config('app.lock_passwords'))) {
            $user->location_id = $request->input('location_id');
        }

        // Handle the avatar upload and/or delete if necessary
        app('\App\Http\Requests\ImageUploadRequest')->handleImages($user, 600, 'avatar', 'avatars', 'avatar');


        if ($user->save()) {
            return redirect()->route('profile')->with('success', trans('account/general.profile_updated'));
        }

        return redirect()->back()->withInput()->withErrors($user->getErrors());
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
    public function api(): View
    {
        // Make sure the self.api permission has been granted
        if (!Gate::allows('self.api')) {
            abort(403);
        }

        return view('account/api');
    }

    /**
     * User change email page.
     *
     */
    public function password() : View | RedirectResponse
    {

        $user = auth()->user();
        if ($user->ldap_import=='1') {
            return redirect()->route('account')->with('error', trans('admin/users/message.error.password_ldap'));
        }
        return view('account/change-password', compact('user'));
    }

    /**
     * Users change password form processing page.
     */
    public function passwordSave(Request $request) : RedirectResponse
    {
        if (config('app.lock_passwords')) {
            return redirect()->route('account.password.index')->with('error', trans('admin/users/table.lock_passwords'));
        }

        $user = auth()->user();
        if ($user->ldap_import == '1') {
            return redirect()->route('account')->with('error', trans('admin/users/message.error.password_ldap'));
        }

        $rules = [
            'current_password'     => 'required',
            'password'         => Setting::passwordComplexityRulesSaving('store').'|confirmed',
        ];

        $validator = \Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request, $user) {
            if (! Hash::check($request->input('current_password'), $user->password)) {
                $validator->errors()->add('current_password', trans('validation.custom.hashed_pass'));
            }

            // This checks to make sure that the user's password isn't the same as their username,
            // email address, first name or last name (see https://github.com/grokability/snipe-it/issues/8661)
            // While this is handled via SaveUserRequest form request in other places, we have to do this manually
            // here because we don't have the username, etc form fields available in the profile password change
            // form.

            // There may be a more elegant way to do this in the future.

            // First let's see if that option is enabled in the settings
            if (strpos(Setting::passwordComplexityRulesSaving('store'), 'disallow_same_pwd_as_user_fields') !== false) {
                if (($request->input('password') == $user->username) ||
                    ($request->input('password') == $user->email) ||
                    ($request->input('password') == $user->first_name) ||
                    ($request->input('password') == $user->last_name)) {
                    $validator->errors()->add('password', trans('validation.disallow_same_pwd_as_user_fields'));
                }
            }
        });

        if (! $validator->fails()) {

            $user->password = Hash::make($request->input('password'));
            // We have to use saveQuietly here because for some reason this method was calling the User Oserver twice :(
            $user->saveQuietly();
            
            // Log the user out of other devices
            Auth::logoutOtherDevices($request->input('password'));
            return redirect()->route('account')->with('success', trans('passwords.password_change'));

        }
        return redirect()->back()->withInput()->withErrors($validator);


    }

    /**
     * Save the menu state of open/closed when the user clicks on the hamburger
     * menu.
     *
     * This URL is triggered via jquery in
     * resources/views/layouts/default.blade.php
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     */
    public function getMenuState(Request $request) : void
    {
        if ($request->input('state') == 'open') {
            $request->session()->put('menu_state', 'open');
        } else {
            $request->session()->put('menu_state', 'closed');
        }
    }


    /**
     * Print inventory
     *
     * @author A. Gianotto
     * @since [v6.0.12]
     */
    public function printInventory() : View
    {
        $show_users = User::where('id',auth()->user()->id)->get();

        return view('users/print')
            ->with('assets', auth()->user()->assets())
            ->with('licenses', auth()->user()->licenses()->get())
            ->with('accessories', auth()->user()->accessories()->get())
            ->with('consumables', auth()->user()->consumables()->get())
            ->with('users', $show_users)
            ->with('settings', Setting::getSettings());
    }

    /**
     * Emails user a list of assigned assets
     *
     * @author A. Gianotto
     * @since [v6.0.12]
     */
    public function emailAssetList() : RedirectResponse
    {

        if (!$user = User::find(auth()->id())) {
            return redirect()->back()
                ->with('error', trans('admin/users/message.user_not_found', ['id' => auth()->id()]));
        }
        if (empty($user->email)) {
            return redirect()->back()->with('error', trans('admin/users/message.user_has_no_email'));
        }

        try {
            $user->notify((new CurrentInventory($user)));
        } catch (\Exception $e) {
            \Log::error($e);
        }

        return redirect()->back()->with('success', trans('admin/users/general.user_notified'));
    }



    public function getStoredEula($filename) : Response | BinaryFileResponse | RedirectResponse
    {

        $logentry = Actionlog::where('filename', $filename)->first();

        // Make sure the user has permission to view this file
        if (auth()->id() != $logentry->target_id) {
            return redirect()->route('account')->with('error', trans('general.generic_model_not_found', ['model' => 'file']));
        }

        if (config('filesystems.default') == 's3_private') {
            return redirect()->away(Storage::disk('s3_private')->temporaryUrl('private_uploads/eula-pdfs/'.$filename, now()->addMinutes(5)));
        }

        if (Storage::exists('private_uploads/eula-pdfs/'.$filename)) {
            return response()->download(config('app.private_uploads').'/eula-pdfs/'.$filename);
        }

        return redirect()->back()->with('error',  trans('general.file_does_not_exist'));

    }
}
