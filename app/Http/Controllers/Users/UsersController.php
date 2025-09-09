<?php

namespace App\Http\Controllers\Users;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteUserRequest;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\SaveUserRequest;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Group;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Notifications\CurrentInventory;

/**
 * This controller handles all actions related to Users for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class UsersController extends Controller
{
    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the users listing, which is generated in getDatatable().
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see UsersController::getDatatable() method that generates the JSON response
     * @since [v1.0]
     * @return \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', User::class);

        return view('users/index');
    }

    /**
     * Returns a view that displays the user creation form.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @return \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create(Request $request)
    {
        $this->authorize('create', User::class);
        $groups = Group::pluck('name', 'id');

        $userGroups = collect();

        if ($request->old('groups')) {
            $userGroups = Group::whereIn('id', $request->old('groups'))->pluck('name', 'id');
        }

        $permissions = config('permissions');
        $userPermissions = Helper::selectedPermissionsArray($permissions, $request->old('permissions', []));
        $permissions = $this->filterDisplayable($permissions);

        $user = new User;

        return view('users/edit', compact('groups', 'userGroups', 'permissions', 'userPermissions'))
            ->with('user', $user);
    }

    /**
     * Validate and store the new user data, or return an error.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param SaveUserRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(SaveUserRequest $request)
    {
        $this->authorize('create', User::class);
        $user = new User;
        //Username, email, and password need to be handled specially because the need to respect config values on an edit.
        $user->email = trim($request->input('email'));
        $user->username = trim($request->input('username'));
        $user->display_name = $request->input('display_name');
        if ($request->filled('password')) {
            $user->password = bcrypt($request->input('password'));
        }
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->locale = $request->input('locale');
        $user->employee_num = $request->input('employee_num');
        $user->activated = $request->input('activated', 0);
        $user->jobtitle = $request->input('jobtitle');
        $user->phone = $request->input('phone');
        $user->mobile = $request->input('mobile');
        $user->location_id = $request->input('location_id', null);
        $user->department_id = $request->input('department_id', null);
        $user->company_id = Company::getIdForUser($request->input('company_id', null));
        $user->manager_id = $request->input('manager_id', null);
        $user->notes = $request->input('notes');
        $user->address = $request->input('address', null);
        $user->city = $request->input('city', null);
        $user->state = $request->input('state', null);
        $user->country = $request->input('country', null);
        $user->zip = $request->input('zip', null);
        $user->remote = $request->input('remote', 0);
        $user->website = $request->input('website', null);
        $user->created_by = auth()->id();
        $user->start_date = $request->input('start_date', null);
        $user->end_date = $request->input('end_date', null);
        $user->autoassign_licenses = $request->input('autoassign_licenses', 0);

        // Strip out the superuser permission if the user isn't a superadmin
        $permissions_array = $request->input('permission');

        if (! auth()->user()->isSuperUser()) {
            unset($permissions_array['superuser']);
        }
        $user->permissions = json_encode($permissions_array);

        // we have to invoke the form request here to handle image uploads
        app(ImageUploadRequest::class)->handleImages($user, 600, 'avatar', 'avatars', 'avatar');

        if ($request->get('redirect_option') === 'back'){
            session()->put(['redirect_option' => 'index']);
        } else {
            session()->put(['redirect_option' => $request->get('redirect_option')]);
        }


        if ($user->save()) {

            if (($user->activated == '1') && ($user->email != '') && ($request->input('send_welcome') == '1')) {

                try {
                    $user->notify(new WelcomeNotification($user));
                } catch (\Exception $e) {
                    Log::warning('Could not send welcome notification for user: ' . $e->getMessage());
                }


            }

            if ($request->filled('groups')) {
                $user->groups()->sync($request->input('groups'));
            } else {
                $user->groups()->sync([]);
            }

            return Helper::getRedirectOption($request, $user->id, 'Users')
                ->with('success', trans('admin/users/message.success.create'));
        }

        return redirect()->back()->withInput()->withErrors($user->getErrors());
    }

    private function filterDisplayable($permissions)
    {
        $output = null;
        foreach ($permissions as $key => $permission) {
            $output[$key] = array_filter($permission, function ($p) {
                return $p['display'] === true;
            });
        }

        return $output;
    }

    /**
     * Returns a view that displays the edit user form
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param $permissions
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     * @internal param int $id
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(User $user)
    {

        $this->authorize('update', User::class);
        session()->put('back_url', url()->previous());
        $user = User::with(['assets', 'assets.model', 'consumables', 'accessories', 'licenses', 'userloc'])->withTrashed()->find($user->id);

        if ($user) {

            if ($user->trashed()) {
                return redirect()->route('users.show', $user->id);
            }

            $permissions = config('permissions');
            $groups = Group::pluck('name', 'id');

            $userGroups = $user->groups()->pluck('name', 'id');
            $user->permissions = $user->decodePermissions();
            $userPermissions = Helper::selectedPermissionsArray($permissions, $user->permissions);
            $permissions = $this->filterDisplayable($permissions);

            return view('users/edit', compact('user', 'groups', 'userGroups', 'permissions', 'userPermissions'))->with('item', $user);
        }

    }

    /**
     * Validate and save edited user data from edit form.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param SaveUserRequest $request
     * @param  int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(SaveUserRequest $request, User $user)
    {
        $this->authorize('update', User::class);

        // This is a janky hack to prevent people from changing admin demo user data on the public demo.
        // The $ids 1 and 2 are special since they are seeded as superadmins in the demo seeder.
        // Thanks, jerks. You are why we can't have nice things. - snipe
        if ((($user->id == 1) || ($user->id == 2)) && (config('app.lock_passwords'))) {
            return redirect()->route('users.index')->with('error', trans('general.permission_denied_superuser_demo'));
        }

        // We need to reverse the UI specific logic for our
        // permissions here before we update the user.
        $permissions = $request->input('permissions', []);
        app('request')->request->set('permissions', $permissions);

        $user->load(['assets', 'assets.model', 'consumables', 'accessories', 'licenses', 'userloc'])->withTrashed();

        $this->authorize('update', $user);

        // Figure out of this user was an admin before this edit
        $orig_permissions_array = $user->decodePermissions();
        $orig_superuser = '0';
        if (is_array($orig_permissions_array)) {
            if (array_key_exists('superuser', $orig_permissions_array)) {
                $orig_superuser = $orig_permissions_array['superuser'];
            }
        }


        // Update the user fields

        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->display_name = $request->input('display_name');
        $user->two_factor_optin = $request->input('two_factor_optin') ?: 0;
        $user->locale = $request->input('locale');
        $user->employee_num = $request->input('employee_num');
        $user->jobtitle = $request->input('jobtitle', null);
        $user->phone = $request->input('phone');
        $user->mobile = $request->input('mobile');
        $user->location_id = $request->input('location_id', null);
        $user->company_id = Company::getIdForUser($request->input('company_id', null));
        $user->manager_id = $request->input('manager_id', null);
        $user->notes = $request->input('notes');
        $user->department_id = $request->input('department_id', null);
        $user->address = $request->input('address', null);
        $user->city = $request->input('city', null);
        $user->state = $request->input('state', null);
        $user->country = $request->input('country', null);
        $user->zip = $request->input('zip', null);
        $user->remote = $request->input('remote', 0);
        $user->vip = $request->input('vip', 0);
        $user->website = $request->input('website', null);
        $user->start_date = $request->input('start_date', null);
        $user->end_date = $request->input('end_date', null);
        $user->autoassign_licenses = $request->input('autoassign_licenses', 0);

        // Set this here so that we can overwrite it later if the user is an admin or superadmin
        $user->activated = $request->input('activated', auth()->user()->is($user) ? 1 : $user->activated);


        // Update the location of any assets checked out to this user
        Asset::where('assigned_type', User::class)
            ->where('assigned_to', $user->id)
            ->update(['location_id' => $request->input('location_id', null)]);

        // check for permissions related fields and only set them if the user has permission to edit them
        if (auth()->user()->can('canEditAuthFields', $user) && auth()->user()->can('editableOnDemo')) {

            $user->username = trim($request->input('username'));
            $user->email = trim($request->input('email'));
            $user->activated = $request->input('activated', $request->user()->is($user) ? 1 : 0);

            // Do we want to update the user password?
            if ($request->filled('password')) {
                $user->password = bcrypt($request->input('password'));
            }

            $permissions_array = $request->input('permission');

            // Strip out the superuser permission if the user isn't a superadmin
            if (! auth()->user()->isSuperUser()) {
                unset($permissions_array['superuser']);
                $permissions_array['superuser'] = $orig_superuser;
            }

            $user->permissions = json_encode($permissions_array);

            // Only save groups if the user is a superuser
            if (auth()->user()->isSuperUser()) {
                $user->groups()->sync($request->input('groups'));
            }
        }


        // Update the location of any assets checked out to this user
        Asset::where('assigned_type', User::class)
            ->where('assigned_to', $user->id)
            ->update(['location_id' => $user->location_id]);


        // Handle uploaded avatar
        app(ImageUploadRequest::class)->handleImages($user, 600, 'avatar', 'avatars', 'avatar');
        session()->put(['redirect_option' => $request->get('redirect_option')]);

        if ($user->save()) {
            // Redirect to the user page
            return Helper::getRedirectOption($request, $user->id, 'Users')
                ->with('success', trans('admin/users/message.success.update'));
        }
        return redirect()->back()->withInput()->withErrors($user->getErrors());
    }

    /**
     * Delete a user
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param  int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(DeleteUserRequest $request, $id)
    {
        $this->authorize('delete', User::class);

        if ($user = User::find($id)) {

            $this->authorize('delete', $user);

            if ($user->delete()) {
                return redirect()->route('users.index')->with('success', trans('admin/users/message.success.delete'));
            }
        }
        return redirect()->route('users.index')->with('error', trans('admin/users/message.user_not_found'));

    }

    /**
     * Restore a deleted user
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param  int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getRestore($id = null)
    {
        if ($user = User::withTrashed()->find($id)) {
            $this->authorize('delete', $user);

            if ($user->deleted_at == '') {
                return redirect()->back()->with('error', trans('general.not_deleted', ['item_type' => trans('general.user')]));
            }

            if ($user->restore()) {
                $logaction = new Actionlog();
                $logaction->item_type = User::class;
                $logaction->item_id = $user->id;
                $logaction->created_at = date('Y-m-d H:i:s');
                $logaction->created_by = auth()->id();
                $logaction->logaction('restore');

                // Redirect them to the deleted page if there are more, otherwise the section index
                $deleted_users = User::onlyTrashed()->count();
                if ($deleted_users > 0) {
                    return redirect()->back()->with('success', trans('admin/users/message.success.restored'));
                }
                return redirect()->route('users.index')->with('success', trans('admin/users/message.success.restored'));

            }

            // Check validation to make sure we're not restoring a user with the same username as an existing user
            return redirect()->back()->with('error', trans('general.could_not_restore', ['item_type' => trans('general.user'), 'error' => $user->getErrors()->first()]));
        }

        return redirect()->route('users.index')->with('error', trans('admin/users/message.does_not_exist'));
    }

    /**
     * Return a view with user detail
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param  int $userId
     * @return \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(User $user)
    {
        // Make sure the user can view users at all
        $this->authorize('view', User::class);

        $user = User::with([
            'consumables',
            'accessories',
            'licenses',
            'userloc',
        ])
            ->withTrashed()
            ->find($user->id);

        // Make sure they can view this particular user
        $this->authorize('view', $user);

        return view('users/view', [
            'user' => $user,
            'settings' => Setting::getSettings(),
        ]);
    }


    /**
     * Return a view containing a pre-populated new user form,
     * populated with some fields from an existing user.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param  int $id
     * @return \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getClone(Request $request, User $user)
    {
        $this->authorize('create', User::class);

        // We need to reverse the UI specific logic for our
        // permissions here before we update the user.
        $permissions = $request->input('permissions', []);
        app('request')->request->set('permissions', $permissions);


        $user_to_clone = User::with('userloc')->withTrashed()->find($user->id);
        // Make sure they can view this particular user
        $this->authorize('view', $user_to_clone);


        if ($user_to_clone) {


            $user = clone $user_to_clone;

            // Blank out some fields
            $user->first_name = '';
            $user->last_name = '';
            $user->email = substr($user->email, ($pos = strpos($user->email, '@')) !== false ? $pos : 0);
            $user->id = null;
            $user->username = null;
            $user->avatar = null;

            // Get this user's groups
            $userGroups = $user_to_clone->groups()->pluck('name', 'id');

            // Get all the available permissions
            $permissions = config('permissions');
            $clonedPermissions = $user_to_clone->decodePermissions();

            $userPermissions = Helper::selectedPermissionsArray($permissions, $clonedPermissions);

            // Show the page
            return view('users/edit', compact('permissions', 'userPermissions'))
                ->with('user', $user)
                ->with('groups', Group::pluck('name', 'id'))
                ->with('userGroups', $userGroups)
                ->with('cloned_model', $user_to_clone)
                ->with('item', $user);
        }


    }

    /**
     * Exports users to CSV
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.5]
     * @return StreamedResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getExportUserCsv()
    {
        $this->authorize('view', User::class);
        \Debugbar::disable();

        $response = new StreamedResponse(function () {
            // Open output stream
            $handle = fopen('php://output', 'w');

            $users = User::with(
                'assets',
                'accessories',
                'consumables',
                'department',
                'licenses',
                'manager',
                'groups',
                'userloc',
                'company'
            )->orderBy('created_at', 'DESC')
                ->chunk(500, function ($users) use ($handle) {
                    $headers = [
                        // strtolower to prevent Excel from trying to open it as a SYLK file
                        strtolower(trans('general.id')),
                        trans('admin/companies/table.title'),
                        trans('admin/users/table.title'),
                        trans('general.employee_number'),
                        trans('admin/users/table.first_name'),
                        trans('admin/users/table.last_name'),
                        trans('admin/users/table.name'),
                        trans('admin/users/table.username'),
                        trans('admin/users/table.email'),
                        trans('admin/users/table.manager'),
                        trans('admin/users/table.location'),
                        trans('general.department'),
                        trans('general.assets'),
                        trans('general.licenses'),
                        trans('general.accessories'),
                        trans('general.consumables'),
                        trans('general.groups'),
                        trans('general.permissions'),
                        trans('general.notes'),
                        trans('admin/users/table.activated'),
                        trans('general.created_at'),
                    ];

                    fputcsv($handle, $headers);

                    foreach ($users as $user) {
                        $user_groups = '';

                        foreach ($user->groups as $user_group) {
                            $user_groups .= $user_group->name.', ';
                        }


                        $permissionstring = "";
                        
                        if($user->isSuperUser()) {
                            $permissionstring = trans('general.superuser');
                        }
                        elseif($user->hasAccess('admin')) {
                            $permissionstring = trans('general.admin');
                        }
                        else {
                            $permissionstring = trans('general.user');
                        }

                        // Add a new row with data
                        $values = [
                            $user->id,
                            ($user->company) ? $user->company->name : '',
                            $user->jobtitle,
                            $user->employee_num,
                            $user->first_name,
                            $user->last_name,
                            $user->display_name,
                            $user->username,
                            $user->email,
                            ($user->manager) ? $user->manager->display_name : '',
                            ($user->userloc) ? $user->userloc->name : '',
                            ($user->department) ? $user->department->name : '',
                            $user->assets->count(),
                            $user->licenses->count(),
                            $user->accessories->count(),
                            $user->consumables->count(),
                            $user_groups,
                            $permissionstring,
                            $user->notes,
                            ($user->activated == '1') ? trans('general.yes') : trans('general.no'),
                            $user->created_at,
                        ];

                        fputcsv($handle, $values);
                    }
                });

            // Close the output stream
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="users-'.date('Y-m-d-his').'.csv"',
        ]);

        return $response;
    }

    /**
     * Print inventory
     *
     * @since [v1.8]
     * @author Aladin Alaily
     */
    public function printInventory($id)
    {
        $this->authorize('view', User::class);

        $user = User::where('id', $id)
            ->with([
                'assets.log' => fn($query) => $query->withTrashed()->where('target_type', User::class)->where('target_id', $id)->where('action_type', 'accepted'),
                'assets.assignedAssets.log' => fn($query) => $query->withTrashed()->where('target_type', User::class)->where('target_id', $id)->where('action_type', 'accepted'),
                'assets.assignedAssets.defaultLoc',
                'assets.assignedAssets.location',
                'assets.assignedAssets.model.category',
                'assets.defaultLoc',
                'assets.location',
                'assets.model.category',
                'accessories.log' => fn($query) => $query->withTrashed()->where('target_type', User::class)->where('target_id', $id)->where('action_type', 'accepted'),
                'accessories.category',
                'accessories.manufacturer',
                'consumables.log' => fn($query) => $query->withTrashed()->where('target_type', User::class)->where('target_id', $id)->where('action_type', 'accepted'),
                'consumables.category',
                'consumables.manufacturer',
                'licenses.category',
            ])
            ->withTrashed()
            ->first();

        if ($user) {
            $this->authorize('view', $user);

            return view('users.print')
                ->with('users', [$user])
                ->with('settings', Setting::getSettings());
        }

        return redirect()->route('users.index')->with('error', trans('admin/users/message.user_not_found', compact('id')));
    }

    /**
     * Emails user a list of assigned assets
     *
     * @author [G. Martinez] [<godmartinz@gmail.com>]
     * @since [v6.0.5]
     * @param  \App\Http\Controllers\Users\UsersController  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function emailAssetList($id)
    {
        $this->authorize('view', User::class);

        $user = User::find($id);

        // Make sure they can view this particular user
        $this->authorize('view', $user);

        if ($user) {

            if (empty($user->email)) {
                return redirect()->back()->with('error', trans('admin/users/message.user_has_no_email'));
            }

            $user->notify((new CurrentInventory($user)));
            return redirect()->back()->with('success', trans('admin/users/general.user_notified'));
        }

        return redirect()->back()->with('error', trans('admin/users/message.user_not_found', ['id' => $id]));

    }

    /**
     * Send individual password reset email
     *
     * @author A. Gianotto
     * @since [v5.0.15]
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendPasswordReset($id)
    {
        $this->authorize('view', User::class);

        if (($user = User::find($id)) && ($user->activated == '1') && ($user->email != '') && ($user->ldap_import == '0')) {
            $credentials = ['email' => trim($user->email)];

            try {

                Password::sendResetLink($credentials);
                return redirect()->back()->with('success', trans('admin/users/message.password_reset_sent', ['email' => $user->email]));

            } catch (\Exception $e) {
                return redirect()->back()->with('error', trans('general.error_sending_email'));
            }
        }

        return redirect()->back()->with('error', trans('general.pwd_reset_not_sent'));
    }
}
