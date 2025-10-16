<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveUserRequest;
use App\Http\Transformers\AccessoriesTransformer;
use App\Http\Transformers\ActionlogsTransformer;
use App\Http\Transformers\AssetsTransformer;
use App\Http\Transformers\ConsumablesTransformer;
use App\Http\Transformers\LicensesTransformer;
use App\Http\Transformers\SelectlistTransformer;
use App\Http\Transformers\UsersTransformer;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Accessory;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\License;
use App\Models\User;
use App\Notifications\CurrentInventory;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\DeleteUserRequest;
use Illuminate\Http\JsonResponse;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     *
     * @return array
     */
    public function index(Request $request) : array
    {
        $this->authorize('view', User::class);

        $users = User::select([
            'users.activated',
            'users.address',
            'users.avatar',
            'users.city',
            'users.company_id',
            'users.country',
            'users.created_by',
            'users.created_at',
            'users.updated_at',
            'users.deleted_at',
            'users.department_id',
            'users.email',
            'users.employee_num',
            'users.first_name',
            'users.id',
            'users.jobtitle',
            'users.last_login',
            'users.last_name',
            'users.display_name',
            'users.locale',
            'users.location_id',
            'users.manager_id',
            'users.notes',
            'users.permissions',
            'users.phone',
            'users.mobile',
            'users.state',
            'users.two_factor_enrolled',
            'users.two_factor_optin',
            'users.username',
            'users.zip',
            'users.remote',
            'users.ldap_import',
            'users.start_date',
            'users.end_date',
            'users.vip',
            'users.autoassign_licenses',
            'users.website',

        ])->with('manager')
            ->with('groups')
            ->with('userloc')
            ->with('company')
            ->with('department')
            ->with('createdBy')
            ->withCount([
                'assets as assets_count' => function(Builder $query) {
                    $query->withoutTrashed();
                },
                'licenses as licenses_count',
                'accessories as accessories_count',
                'consumables as consumables_count',
                'managesUsers as manages_users_count',
                'managedLocations as manages_locations_count'
            ]);

        $allowed_columns =
            [
                'last_name',
                'first_name',
                'display_name',
                'email',
                'jobtitle',
                'username',
                'employee_num',
                'groups',
                'activated',
                'created_at',
                'updated_at',
                'two_factor_enrolled',
                'two_factor_optin',
                'last_login',
                'assets_count',
                'licenses_count',
                'consumables_count',
                'accessories_count',
                'manages_users_count',
                'manages_locations_count',
                'phone',
                'mobile',
                'address',
                'city',
                'state',
                'country',
                'zip',
                'id',
                'ldap_import',
                'two_factor_optin',
                'two_factor_enrolled',
                'remote',
                'vip',
                'start_date',
                'end_date',
                'autoassign_licenses',
                'website',
                'locale',
                'notes',
                'employee_num',

                // These are *relationships* so we wouldn't normally include them in this array,
                // since they would normally create a `column not found` error,
                // BUT we account for them in the ordering switch down at the end of this method
                // DO NOT ADD ANYTHING TO THIS LIST WITHOUT CHECKING THE ORDERING SWITCH BELOW!
                'company',
                'location',
                'department',
                'manager',
                'created_by',

            ];

        $filter = [];

        if ($request->filled('filter')) {
            $filter = json_decode($request->input('filter'), true);
            $filter = array_filter($filter, function ($key) use ($allowed_columns) {
                return in_array($key, $allowed_columns);
            }, ARRAY_FILTER_USE_KEY);

        }

        if ((! is_null($filter)) && (count($filter)) > 0) {
            $users->ByFilter($filter);
        } elseif ($request->filled('search')) {
            $users->TextSearch($request->input('search'));
        }

        if ($request->filled('activated')) {
            $users = $users->where('users.activated', '=', $request->input('activated'));
        }

        if ($request->input('admins') == 'true') {
            $users = $users->OnlyAdminsAndSuperAdmins();
        }

        if ($request->input('superadmins') == 'true') {
            $users = $users->OnlySuperAdmins();
        }

        if ($request->filled('company_id')) {
            $users = $users->where('users.company_id', '=', $request->input('company_id'));
        }

        if ($request->filled('phone')) {
            $users = $users->where('users.phone', '=', $request->input('phone'));
        }

        if ($request->filled('mobile')) {
            $users = $users->where('users.mobile', '=', $request->input('mobile'));
        }

        if ($request->filled('location_id')) {
            $users = $users->where('users.location_id', '=', $request->input('location_id'));
        }

        if ($request->filled('created_by')) {
            $users = $users->where('users.created_by', '=', $request->input('created_by'));
        }

        if ($request->filled('email')) {
            $users = $users->where('users.email', '=', $request->input('email'));
        }

        if ($request->filled('username')) {
            $users = $users->where('users.username', '=', $request->input('username'));
        }

        if ($request->filled('first_name')) {
            $users = $users->where('users.first_name', '=', $request->input('first_name'));
        }

        if ($request->filled('last_name')) {
            $users = $users->where('users.last_name', '=', $request->input('last_name'));
        }

        if ($request->filled('display_name')) {
            $users = $users->where('users.display_name', '=', $request->input('display_name'));
        }

        if ($request->filled('employee_num')) {
            $users = $users->where('users.employee_num', '=', $request->input('employee_num'));
        }

        if ($request->filled('state')) {
            $users = $users->where('users.state', '=', $request->input('state'));
        }

        if ($request->filled('country')) {
            $users = $users->where('users.country', '=', $request->input('country'));
        }

        if ($request->filled('website')) {
            $users = $users->where('users.website', '=', $request->input('website'));
        }

        if ($request->filled('zip')) {
            $users = $users->where('users.zip', '=', $request->input('zip'));
        }

        if ($request->filled('group_id')) {
            $users = $users->ByGroup($request->get('group_id'));
        }

        if ($request->filled('department_id')) {
            $users = $users->where('users.department_id', '=', $request->input('department_id'));
        }

        if ($request->filled('manager_id')) {
            $users = $users->where('users.manager_id','=',$request->input('manager_id'));
        }

        if ($request->filled('ldap_import')) {
            $users = $users->where('ldap_import', '=', $request->input('ldap_import'));
        }

        if ($request->filled('remote')) {
            $users = $users->where('remote', '=', $request->input('remote'));
        }

        if ($request->filled('vip')) {
            $users = $users->where('vip', '=', $request->input('vip'));
        }

        if ($request->filled('two_factor_enrolled')) {
            $users = $users->where('two_factor_enrolled', '=', $request->input('two_factor_enrolled'));
        }

        if ($request->filled('two_factor_optin')) {
            $users = $users->where('two_factor_optin', '=', $request->input('two_factor_optin'));
        }

        if ($request->filled('start_date')) {
            $users = $users->where('users.start_date', '=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $users = $users->where('users.end_date', '=', $request->input('end_date'));
        }

        if ($request->filled('assets_count')) {
           $users->has('assets', '=', $request->input('assets_count'));
        }

        if ($request->filled('consumables_count')) {
            $users->has('consumables', '=', $request->input('consumables_count'));
        }

        if ($request->filled('licenses_count')) {
            $users->has('licenses', '=', $request->input('licenses_count'));
        }

        if ($request->filled('accessories_count')) {
            $users->has('accessories', '=', $request->input('accessories_count'));
        }

        if ($request->filled('manages_users_count')) {
            $users->has('managesUsers', '=', $request->input('manages_users_count'));
        }

        if ($request->filled('manages_locations_count')) {
            $users->has('managedLocations', '=', $request->input('manages_locations_count'));
        }

        if ($request->filled('autoassign_licenses')) {
            $users->where('autoassign_licenses', '=', $request->input('autoassign_licenses'));
        }

        if ($request->filled('locale')) {
            $users = $users->where('users.locale', '=', $request->input('locale'));
        }


        if (($request->filled('deleted')) && ($request->input('deleted') == 'true')) {
            $users = $users->onlyTrashed();
        } elseif (($request->filled('all')) && ($request->input('all') == 'true')) {
            $users = $users->withTrashed();
        }

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';

        switch ($request->input('sort')) {
            case 'manager':
                $users = $users->OrderManager($order);
                break;
            case 'location':
                $users = $users->OrderLocation($order);
                break;
            case 'department':
                $users = $users->OrderDepartment($order);
                break;
            case 'created_by':
                $users = $users->OrderByCreatedBy($order);
                break;
            case 'company':
                $users = $users->OrderCompany($order);
                break;
            case 'first_name':
                $users->orderBy('first_name', $order);
                $users->orderBy('last_name', $order);
                break;
            case 'last_name':
                $users->orderBy('last_name', $order);
                $users->orderBy('first_name', $order);
                break;
            default:
                $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'first_name';
                $users = $users->orderBy($sort, $order);
                break;
        }



        // Make sure the offset and limit are actually integers and do not exceed system limits
        $offset = ($request->input('offset') > $users->count()) ? $users->count() : app('api_offset_value');
        $limit = app('api_limit_value');

        $total = $users->count();
        $users = $users->skip($offset)->take($limit)->get();

        return (new UsersTransformer)->transformUsers($users, $total);
    }

    /**
     * Gets a paginated collection for the select2 menus
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0.16]
     * @see \App\Http\Transformers\SelectlistTransformer
     */
    public function selectlist(Request $request) : array
    {
        $users = User::select(
            [
                'users.id',
                'users.username',
                'users.employee_num',
                'users.first_name',
                'users.last_name',
                'users.display_name',
                'users.gravatar',
                'users.avatar',
                'users.email',
            ]
            )->where('show_in_list', '=', '1');

        if ($request->filled('search')) {
            $users = $users->where(function ($query) use ($request) {
                $query->SimpleNameSearch($request->get('search'))
                    ->orWhere('username', 'LIKE', '%'.$request->get('search').'%')
                    ->orWhere('display_name', 'LIKE', '%'.$request->get('search').'%')
                    ->orWhere('email', 'LIKE', '%'.$request->get('search').'%')
                    ->orWhere('employee_num', 'LIKE', '%'.$request->get('search').'%');
            });
        }

        $users = $users->orderBy('display_name', 'asc')->orderBy('last_name', 'asc')->orderBy('first_name', 'asc');
        $users = $users->paginate(50);

        foreach ($users as $user) {
            $name_str = $user->display_name;

            if ($user->username != '') {
                $name_str .= ' ('.$user->username.')';
            }

            if ($user->employee_num != '') {
                $name_str .= ' - #'.$user->employee_num;
            }

            $user->use_text = $name_str;
            $user->use_image = ($user->present()->gravatar) ? $user->present()->gravatar : null;
        }

        return (new SelectlistTransformer)->transformSelectlist($users);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(SaveUserRequest $request) : JsonResponse
    {
        $this->authorize('create', User::class);

        $user = new User;
        $user->fill($request->all());
        $user->company_id = Company::getIdForCurrentUser($request->input('company_id'));
        $user->created_by = auth()->id();

        if ($request->has('permissions')) {
            $permissions_array = $request->input('permissions');

            // Strip out the superuser permission if the API user isn't a superadmin
            if (! auth()->user()->isSuperUser()) {
                unset($permissions_array['superuser']);
            }
            $user->permissions = $permissions_array;
        }

        // 
        if ($request->filled('password')) {
            $user->password = bcrypt($request->get('password'));
        } else {
            $user->password = $user->noPassword();
        }

        app('App\Http\Requests\ImageUploadRequest')->handleImages($user, 600, 'avatar', 'avatars', 'avatar');
        
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

            return response()->json(Helper::formatStandardApiResponse('success', (new UsersTransformer)->transformUser($user), trans('admin/users/message.success.create')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $user->getErrors()));
    }

    /**
     * Display the specified resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param  int  $id
     */
    public function show($id) : JsonResponse | array
    {
        $this->authorize('view', User::class);

        if ($user = User::withCount('assets as assets_count', 'licenses as licenses_count', 'accessories as accessories_count', 'consumables as consumables_count', 'managesUsers as manages_users_count', 'managedLocations as manages_locations_count')->find($id)) {
            $this->authorize('view', $user);
            return (new UsersTransformer)->transformUser($user);
        }
        
        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/users/message.user_not_found', compact('id'))));

    }


    /**
     * Update the specified resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function update(SaveUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', User::class);

            $this->authorize('update', $user);

            /**
             * This is a janky hack to prevent people from changing admin demo user data on the public demo.
             * The $ids 1 and 2 are special since they are seeded as superadmins in the demo seeder.
             *  Thanks, jerks. You are why we can't have nice things. - snipe
             *
             */

        if ((($user->id == 1) || ($user->id == 2)) && (config('app.lock_passwords'))) {
                return response()->json(Helper::formatStandardApiResponse('error', null, 'Permission denied. You cannot update user information via API on the demo.'));
            }

            $user->fill($request->all());

            if ($request->filled('company_id')) {
                $user->company_id = Company::getIdForCurrentUser($request->input('company_id'));
            }

            if ($user->id == $request->input('manager_id')) {
                return response()->json(Helper::formatStandardApiResponse('error', null, 'You cannot be your own manager'));
            }

            // check for permissions related fields and pull them out if the current user cannot edit them
            if (auth()->user()->can('canEditAuthFields', $user) && auth()->user()->can('editableOnDemo')) {

                if ($request->filled('password')) {
                    $user->password = bcrypt($request->input('password'));
                }

                if ($request->filled('username')) {
                    $user->username = $request->input('username');
                }

                if ($request->filled('display_name')) {
                    $user->display_name = $request->input('display_name');
                }

                if ($request->filled('email')) {
                    $user->email = $request->input('email');
                }

                if ($request->filled('activated')) {
                    $user->activated = $request->input('activated');
                }

            }

            // We need to use has()  instead of filled()
            // here because we need to overwrite permissions
            // if someone needs to null them out
            if ($request->has('permissions')) {
                $permissions_array = $request->input('permissions');

                // Strip out the individual superuser permission if the API user isn't a superadmin
                if (!auth()->user()->isSuperUser()) {
                    unset($permissions_array['superuser']);
                }

                $user->permissions = $permissions_array;
            }

            if($request->has('location_id')) {
                // Update the location of any assets checked out to this user
                Asset::where('assigned_type', User::class)
                    ->where('assigned_to', $user->id)->update(['location_id' => $request->input('location_id', null)]);
            }
            app('App\Http\Requests\ImageUploadRequest')->handleImages($user, 600, 'avatar', 'avatars', 'avatar');

            if ($user->save()) {
                // Check if the request has groups passed and has a value, AND that the user us a superuser
                if (($request->has('groups')) && (auth()->user()->isSuperUser())) {

                    $validator = Validator::make($request->only('groups'), [
                        'groups.*' => 'integer|exists:permission_groups,id',
                    ]);

                    if ($validator->fails()) {
                        return response()->json(Helper::formatStandardApiResponse('error', null, $validator->errors()));
                    }

                    // Sync the groups since the user is a superuser and the groups pass validation
                    $user->groups()->sync($request->input('groups'));
                }
                return response()->json(Helper::formatStandardApiResponse('success', (new UsersTransformer)->transformUser($user), trans('admin/users/message.success.update')));
            }
            return response()->json(Helper::formatStandardApiResponse('error', null, $user->getErrors()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  int  $id
     */
    public function destroy(DeleteUserRequest $request, $id) : JsonResponse
    {
        $this->authorize('delete', User::class);

        if ($user = User::withTrashed()->find($id)) {

            $this->authorize('delete', $user);

            if ($user->delete()) {

                // Remove the user's avatar if they have one
                if (Storage::disk('public')->exists('avatars/' . $user->avatar)) {
                    try {
                        Storage::disk('public')->delete('avatars/' . $user->avatar);
                    } catch (\Exception $e) {
                        Log::debug($e);
                    }
                }

                return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/users/message.success.delete')));
            }

            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/users/message.error.delete')));

        }

        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/users/message.user_not_found')));

    }

    /**
     * Return JSON containing a list of assets assigned to a user.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @param $userId
     */
    public function assets(Request $request, $id) : JsonResponse | array
    {
        $this->authorize('view', User::class);
        $this->authorize('view', Asset::class);

        if ($user = User::with('assets', 'assets.model', 'consumables', 'accessories', 'licenses', 'userloc')->withTrashed()->find($id)) {
            $this->authorize('view', $user);

            $assets = Asset::where('assigned_to', '=', $id)->where('assigned_type', '=', User::class)->with('model');


            // Filter on category ID
            if ($request->filled('category_id')) {
                $assets = $assets->InCategory($request->input('category_id'));
            }


            // Filter on model ID
            if ($request->filled('model_id')) {

                $model_ids = $request->input('model_id');
                if (!is_array($model_ids)) {
                    $model_ids = array($model_ids);
                }
                $assets = $assets->InModelList($model_ids);
            }

            $assets = $assets->get();

            return (new AssetsTransformer)->transformAssets($assets, $assets->count(), $request);
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/users/message.user_not_found', compact('id'))));

    }

    /**
     * Notify a specific user via email with all of their assigned assets.
     *
     * @author [Lukas Fehling] [<lukas.fehling@adabay.rocks>]
     * @since [v6.0.13]
     * @param Request $request
     * @param $id
     */
    public function emailAssetList(Request $request, $id) : JsonResponse

    {
        $this->authorize('update', User::class);

        if ($user = User::find($id)) {
            $this->authorize('update', $user);

            if (empty($user->email)) {
                return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/users/message.inventorynotification.error')));
            }

            $user->notify((new CurrentInventory($user)));
            return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/users/message.inventorynotification.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/users/message.user_not_found', compact('id'))));
 

    }

    /**
     * Return JSON containing a list of consumables assigned to a user.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @param $userId
     */
    public function consumables(Request $request, $id) : array
    {
        $this->authorize('view', User::class);
        $this->authorize('view', Consumable::class);
        $user = User::findOrFail($id);
        $this->authorize('view', $user);
        $consumables = $user->consumables;
        return (new ConsumablesTransformer)->transformConsumables($consumables, $consumables->count(), $request);
    }

    /**
     * Return JSON containing a list of accessories assigned to a user.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.6.14]
     * @param $userId
     */
    public function accessories($id) : array
    {
        $this->authorize('view', User::class);
        $user = User::findOrFail($id);
        $this->authorize('view', $user);
        $this->authorize('view', Accessory::class);
        $accessories = $user->accessories;

        return (new AccessoriesTransformer)->transformAccessories($accessories, $accessories->count());
    }

    /**
     * Return JSON containing a list of licenses assigned to a user.
     *
     * @author [N. Mathar] [<snipe@snipe.net>]
     * @since [v5.0]
     * @param $userId
     */
    public function licenses($id) : JsonResponse | array
    {
        $this->authorize('view', User::class);
        $this->authorize('view', License::class);
        
        if ($user = User::where('id', $id)->withTrashed()->first()) {
            $licenses = $user->licenses()->get();
            return (new LicensesTransformer())->transformLicenses($licenses, $licenses->count());
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/users/message.user_not_found', compact('id'))));

    }

    /**
     * Reset the user's two-factor status
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @param $userId
     */
    public function postTwoFactorReset(Request $request) : JsonResponse
    {
        $this->authorize('update', User::class);

        if ($request->filled('id')) {
            try {
                $user = User::find($request->get('id'));
                $this->authorize('update', $user);
                $user->two_factor_secret = null;
                $user->two_factor_enrolled = 0;
                $user->saveQuietly();

                // Log the reset
                $logaction = new Actionlog();
                $logaction->target_type = User::class;
                $logaction->target_id = $user->id;
                $logaction->item_type = User::class;
                $logaction->item_id = $user->id;
                $logaction->created_at = date('Y-m-d H:i:s');
                $logaction->created_by = auth()->id();
                $logaction->logaction('2FA reset');

                return response()->json(['message' => trans('admin/settings/general.two_factor_reset_success')], 200);
            } catch (\Exception $e) {
                return response()->json(['message' => trans('admin/settings/general.two_factor_reset_error')], 500);
            }
        }
        return response()->json(['message' => 'No ID provided'], 500);


    }

    /**
     * Get info on the current user.
     *
     * @author [Juan Font] [<juanfontalonso@gmail.com>]
     * @since [v4.4.2]
     * @param  \Illuminate\Http\Request  $request
     */
    public function getCurrentUserInfo(Request $request) : array
    {
        return (new UsersTransformer)->transformUser($request->user());
    }

    /**
     * Display the EULAs accepted by the user.
     *
     *  @param \App\Models\User $user
     *  @param \App\Http\Transformers\ActionlogsTransformer $transformer
     *  @return \Illuminate\Http\JsonResponse
     *@since [v8.1.16]
     * @author [Godfrey Martinez] [<gmartinez@grokability.com>]
     */
    public function eulas(User $user, ActionlogsTransformer $transformer)
    {
        $this->authorize('view', User::class);

        $eulas = $user->eulas;
        return response()->json(
            $transformer->transformActionlogs($eulas, $eulas->count())
        );
    }

    /**
     * Restore a soft-deleted user.
     *
     * @author [E. Taylor] [<dev@evantaylor.name>]
     * @param int $userId
     * @since [v6.0.0]
     */
    public function restore($userId) : JsonResponse
    {
        $this->authorize('delete', User::class);

        if ($user = User::withTrashed()->find($userId)) {

            $this->authorize('delete', $user);

            if ($user->deleted_at == '') {
                return response()->json(Helper::formatStandardApiResponse('error', trans('general.not_deleted', ['item_type' => trans('general.user')])), 200);
            }

            if ($user->restore()) {

                $logaction = new Actionlog();
                $logaction->item_type = User::class;
                $logaction->item_id = $user->id;
                $logaction->created_at = date('Y-m-d H:i:s');
                $logaction->created_by = auth()->id();
                $logaction->logaction('restore');

                return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/users/message.success.restored')), 200);
            }

        }

        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/users/message.user_not_found')), 200);

    }


    /**
     * Run the LDAP sync command to import users from LDAP via API.
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since 8.2.2
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncLdapUsers(Request $request)
    {
        $this->authorize('update', User::class);
        // Call Artisan LDAP import command.

        Artisan::call('snipeit:ldap-sync', ['--location_id' => $request->input('location_id'), '--json_summary' => true]);

        // Collect and parse JSON summary.
        $ldap_results_json = Artisan::output();
        $ldap_results = json_decode($ldap_results_json, true);

        if (!$ldap_results) {
            return response()->json(Helper::formatStandardApiResponse('error', null,trans('general.no_results')), 200);
        }

        // Direct user to appropriate status page.
        if ($ldap_results['error']) {
            return response()->json(Helper::formatStandardApiResponse('error', null, $ldap_results['error_message']), 200);
        }

        return response()->json(Helper::formatStandardApiResponse('success', null, $ldap_results['summary']), 200);

    }
}
