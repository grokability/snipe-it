<?php

namespace App\Http\Controllers\Users;

use App\Events\UserMerged;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Accessory;
use App\Models\License;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Group;
use App\Models\LicenseSeat;
use App\Models\ConsumableAssignment;
use App\Models\Consumable;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\CurrentInventory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;

class BulkUsersController extends Controller
{
    /**
     * Returns a view that confirms the user's a bulk action will be applied to.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.7]
     * @param Request $request
     * @return \Illuminate\Contracts\View\View | \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Request $request)
    {
        $this->authorize('view', User::class);

        // Make sure there were users selected
        if (($request->filled('ids')) && (count($request->input('ids')) > 0)) {

            // Get the list of affected users
            $user_raw_array = request('ids');
            $users = User::whereIn('id', $user_raw_array)
                ->with('assets', 'manager', 'userlog', 'licenses', 'consumables', 'accessories', 'managedLocations','uploads', 'acceptances')->get();

            // bulk edit, display the bulk edit form
            if ($request->input('bulk_actions') == 'edit') {
                $this->authorize('update', User::class);
                return view('users/bulk-edit', compact('users'))
                    ->with('groups', Group::pluck('name', 'id'));

            // bulk send assigned inventory
            } elseif ($request->input('bulk_actions') == 'send_assigned') {
                    $this->authorize('update', User::class);

                $users_without_email = 0;
                foreach ($users as $user) {
                    if (empty($user->email)) {
                        $users_without_email++;
                    } else {
                        $user->notify((new CurrentInventory($user)));
                    }
                }

                if ($users_without_email == 0) {
                    return redirect()->back()->with('success', trans_choice('admin/users/general.users_notified', $users->count()));
                } else {
                    return redirect()->back()->with('warning', trans_choice('admin/users/general.users_notified_warning', $users->count(), ['no_email' => $users_without_email]));
                }




            // bulk delete, display the bulk delete confirmation form
            } elseif ($request->input('bulk_actions') == 'delete') {
                $this->authorize('delete', User::class);
                return view('users/confirm-bulk-delete')->with('users', $users)->with('statuslabel_list', Helper::statusLabelList());

            // merge, confirm they have at least 2 users selected and display the merge screen
            } elseif ($request->input('bulk_actions') == 'merge') {
                $this->authorize('delete', User::class);
                if (($request->filled('ids')) && (count($request->input('ids')) > 1)) {
                    return view('users/confirm-merge')->with('users', $users);
                // Not enough users selected, send them back
                } else {
                    return redirect()->back()->with('error', trans('general.not_enough_users_selected', ['count' => 2]));
                }

            // bulk password reset, just do the thing
            } elseif ($request->input('bulk_actions') == 'bulkpasswordreset') {
                foreach ($users as $user) {
                    if (($user->activated == '1') && ($user->email != '') && ($user->ldap_import != '1')) {
                        $credentials = ['email' => $user->email];
                        Password::sendResetLink($credentials/* , function (Message $message) {
                        $message->subject($this->getEmailSubject()); // TODO - I'm not sure if we still need this, but this second parameter is no longer accepted in later Laravel versions.
                        } */ );                                      // TODO - so hopefully this doesn't give us generic password reset messages? But it at least _works_
                    }
                }
                return redirect()->back()->with('success', trans('admin/users/message.password_resets_sent'));

            } elseif ($request->input('bulk_actions') == 'print') {
                $users = User::query()
                    ->with([
                        'assets.assetlog',
                        'assets.assignedAssets.assetlog',
                        'assets.assignedAssets.defaultLoc',
                        'assets.assignedAssets.location',
                        'assets.assignedAssets.model.category',
                        'assets.defaultLoc',
                        'assets.location',
                        'assets.model.category',
                        'accessories.assetlog',
                        'accessories.category',
                        'accessories.manufacturer',
                        'consumables.assetlog',
                        'consumables.category',
                        'consumables.manufacturer',
                        'licenses.category',
                    ])
                    ->withTrashed()
                    ->findMany($request->input('ids'));

                $users->each(fn($user) => $this->authorize('view', $user));

                return view('users.print')
                    ->with('users', $users)
                    ->with('settings', Setting::getSettings());
            }
        }

        return redirect()->back()->with('error', trans('general.no_users_selected'));
    }

    /**
     * Save bulk-edited users
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request)
    {
        $this->authorize('update', User::class);

        if ((! $request->filled('ids')) || $request->input('ids') <= 0) {
            return redirect()->back()->with('error', trans('general.no_users_selected'));
        }
        $user_raw_array = $request->input('ids');

        // Remove the user from any updates.
        $user_raw_array = array_diff($user_raw_array, [auth()->id()]);
        $manager_conflict = false;
        $users = User::whereIn('id', $user_raw_array)->where('id', '!=', auth()->id())->get();

        $return_array = [
            'success' => trans('admin/users/message.success.update_bulk'),
        ];

        $this->conditionallyAddItem('location_id')
            ->conditionallyAddItem('department_id')
            ->conditionallyAddItem('company_id')
            ->conditionallyAddItem('locale')
            ->conditionallyAddItem('remote')
            ->conditionallyAddItem('ldap_import')
            ->conditionallyAddItem('activated')
            ->conditionallyAddItem('start_date')
            ->conditionallyAddItem('end_date')
            ->conditionallyAddItem('city')
            ->conditionallyAddItem('autoassign_licenses');


        // If the manager_id is one of the users being updated, generate a warning.
        if (array_search($request->input('manager_id'), $user_raw_array)) {
            $manager_conflict = true;
            $return_array = [
                'warning' => trans('admin/users/message.bulk_manager_warn'),
            ];
        }

        /**
         * Check to see if the user wants to actually blank out the values vs skip them
         */
        if ($request->input('null_location_id')=='1') {
            $this->update_array['location_id'] = null;
        }

        if ($request->input('null_department_id')=='1') {
            $this->update_array['department_id'] = null;
        }

        if ($request->input('null_manager_id')=='1') {
            $this->update_array['manager_id'] = null;
        }

        if ($request->input('null_company_id')=='1') {
            $this->update_array['company_id'] = null;
        }

        if ($request->input('null_start_date')=='1') {
            $this->update_array['start_date'] = null;
        }

        if ($request->input('null_end_date')=='1') {
            $this->update_array['end_date'] = null;
        }

        if ($request->input('null_locale')=='1') {
            $this->update_array['locale'] = null;
        }

        if (! $manager_conflict) {
            $this->conditionallyAddItem('manager_id');
        }
        // Save the updated info
        User::whereIn('id', $user_raw_array)
            ->where('id', '!=', auth()->id())->update($this->update_array);

        if (array_key_exists('location_id', $this->update_array)){
            Asset::where('assigned_type', User::class)
                ->whereIn('assigned_to', $user_raw_array)
                ->update(['location_id' => $this->update_array['location_id']]);
        }

        // Only sync groups if groups were selected
        if ($request->filled('groups')) {
            foreach ($users as $user) {
                $user->groups()->sync($request->input('groups'));
            }
        }

        return redirect()->route('users.index')
            ->with($return_array);
    }

    /**
     * Array to store update data per item
     * @var array
     */
    private $update_array = [];

    /**
     * Adds parameter to update array for an item if it exists in request
     * @param  string $field field name
     * @return BulkUsersController Model for Chaining
     */
    protected function conditionallyAddItem($field)
    {
        if (request()->filled($field)) {
            $this->update_array[$field] = request()->input($field);
        }

        return $this;
    }

    /**
     * Soft-delete bulk users
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Request $request)
    {
        $this->authorize('update', User::class);

        if ((! $request->filled('ids')) || (count($request->input('ids')) == 0)) {
            return redirect()->back()->with('error', trans('general.no_users_selected'));
        }

        if (config('app.lock_passwords')) {
            return redirect()->route('users.index')->with('error', trans('general.feature_disabled'));
        }

        $user_raw_array = request('ids');

        if (($key = array_search(auth()->id(), $user_raw_array)) !== false) {
            unset($user_raw_array[$key]);
        }

        $users = User::whereIn('id', $user_raw_array)->get();
        $assets = Asset::whereIn('assigned_to', $user_raw_array)->where('assigned_type', User::class)->get();
        $accessoryUserRows = DB::table('accessories_checkout')->where('assigned_type', User::class)->whereIn('assigned_to', $user_raw_array)->get();
        $licenses = DB::table('license_seats')->whereIn('assigned_to', $user_raw_array)->get();
        $consumableUserRows = DB::table('consumables_users')->whereIn('assigned_to', $user_raw_array)->get();

        if ((($assets->count() > 0) && ((!$request->filled('status_id')) || ($request->input('status_id') == '')))) {
            return redirect()->route('users.index')->with('error', 'No status selected');
        }

        $this->logItemCheckinAndDelete($assets, Asset::class);
        $this->logAccessoriesCheckin($accessoryUserRows);
        $this->logItemCheckinAndDelete($licenses, License::class);

        Asset::whereIn('id', $assets->pluck('id'))->update([
            'status_id'     => e(request('status_id')),
            'assigned_to'   => null,
            'assigned_type' => null,
            'expected_checkin' => null,
        ]);

        LicenseSeat::whereIn('id', $licenses->pluck('id'))->update(['assigned_to' => null]);
        ConsumableAssignment::whereIn('id', $consumableUserRows->pluck('id'))->delete();

        foreach ($users as $user) {
            $user->accessories()->sync([]);
            if ($request->input('delete_user')=='1') {
                $user->delete();
            }
        }

        $msg = trans('general.bulk_checkin_success');
        if ($request->input('delete_user')=='1') {
            $msg = trans('general.bulk_checkin_delete_success');
        }


        return redirect()->route('users.index')->with('success', $msg);
    }

    /**
     * Generate an action log entry for each of a group of items.
     * @param $items
     * @param $itemType string name of items being passed.
     */
    protected function logItemCheckinAndDelete($items, $itemType)
    {
        foreach ($items as $item) {
            $item_id = $item->id;
            $logAction = new Actionlog();

            if ($itemType == License::class){
                $item_id = $item->license_id;
            }

            $logAction->item_id = $item_id;
            // We can't rely on get_class here because the licenses/accessories fetched above are not eloquent models, but simply arrays.
            $logAction->item_type = $itemType;
            $logAction->target_id = $item->assigned_to;
            $logAction->target_type = User::class;
            $logAction->created_by = auth()->id();
            $logAction->note = 'Bulk checkin items';
            $logAction->logaction('checkin from');
        }
    }

    private function logAccessoriesCheckin(Collection $accessoryUserRows): void
    {
        foreach ($accessoryUserRows as $accessoryUserRow) {
            $logAction = new Actionlog();
            $logAction->item_id = $accessoryUserRow->accessory_id;
            $logAction->item_type = Accessory::class;
            $logAction->target_id = $accessoryUserRow->assigned_to;
            $logAction->target_type = User::class;
            $logAction->created_by = auth()->id();
            $logAction->note = 'Bulk checkin items';
            $logAction->logaction('checkin from');
        }
    }

    /**
     * Save bulk-edited users
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function merge(Request $request)
    {
        $this->authorize('update', User::class);

        if (config('app.lock_passwords')) {
            return redirect()->route('users.index')->with('error', trans('general.feature_disabled'));
        }

        $user_ids_to_merge = $request->input('ids_to_merge');
        $user_ids_to_merge = array_diff($user_ids_to_merge, array($request->input('merge_into_id')));

        if ((!$request->filled('merge_into_id')) || (count($user_ids_to_merge) < 1)) {
            return redirect()->back()->with('error', trans('general.no_users_selected'));
        }

        // Get the users
        $merge_into_user = User::find($request->input('merge_into_id'));
        $users_to_merge = User::whereIn('id', $user_ids_to_merge)->with('assets', 'manager', 'userlog', 'licenses', 'consumables', 'accessories', 'managedLocations','uploads', 'acceptances')->get();
        $admin = User::find(auth()->id());

        // Walk users
        foreach ($users_to_merge as $user_to_merge) {

            foreach ($user_to_merge->assets as $asset) {
                Log::debug('Updating asset: '.$asset->asset_tag . ' to '.$merge_into_user->id);
                $asset->assigned_to = $request->input('merge_into_id');
                $asset->save();
            }

            foreach ($user_to_merge->licenses as $license) {
                Log::debug('Updating license pivot: '.$license->id . ' to '.$merge_into_user->id);
                $user_to_merge->licenses()->updateExistingPivot($license->id, ['assigned_to' => $merge_into_user->id]);
            }

            foreach ($user_to_merge->consumables as $consumable) {
                Log::debug('Updating consumable pivot: '.$consumable->id . ' to '.$merge_into_user->id);
                $user_to_merge->consumables()->updateExistingPivot($consumable->id, ['assigned_to' => $merge_into_user->id]);
            }

            foreach ($user_to_merge->accessories as $accessory) {
                $user_to_merge->accessories()->updateExistingPivot($accessory->id, ['assigned_to' => $merge_into_user->id]);
            }

            foreach ($user_to_merge->userlog as $log) {
                $log->target_id = $merge_into_user->id;
                $log->save();
            }

            foreach ($user_to_merge->uploads as $upload) {
                $upload->item_id = $merge_into_user->id;
                $upload->save();
            }

            foreach ($user_to_merge->acceptances as $acceptance) {
                $acceptance->item_id = $merge_into_user->id;
                $acceptance->save();
            }

            User::where('manager_id', '=', $user_to_merge->id)->update(['manager_id' => $merge_into_user->id]);

            foreach ($user_to_merge->managedLocations as $managedLocation) {
                $managedLocation->manager_id = $merge_into_user->id;
                $managedLocation->save();
            }

            $user_to_merge->delete();

            event(new UserMerged($user_to_merge, $merge_into_user, $admin));

        }

        return redirect()->route('users.index')->with('success', trans('general.merge_success', ['count' => $users_to_merge->count(), 'into_username' => $merge_into_user->username]));


    }
}
