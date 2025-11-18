<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use \Illuminate\Contracts\View\View;
use \App\Models\User;

/**
 * This controller handles all actions related to User Groups for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class GroupsController extends Controller
{
    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the user group listing, which is generated in getDatatable.
     *
     * @author [A. Gianotto] [<snipe@snipe.net]
     * @see GroupsController::getDatatable() method that generates the JSON response
     * @since [v1.0]
     */
    public function index(): View
    {
        return view('groups/index');
    }

    /**
     * Returns a view that displays a form to create a new User Group.
     *
     * @author [A. Gianotto] [<snipe@snipe.net]
     * @see GroupsController::postCreate()
     * @since [v1.0]
     */
    public function create(Request $request) : View
    {
        $group = new Group;
        // Get all the available permissions
        $permissions = config('permissions');
        $groupPermissions = Helper::selectedPermissionsArray($permissions, $permissions);
        $selectedPermissions = $request->old('permissions', $groupPermissions);
        $users_query = User::where('show_in_list', 1)->whereNull('deleted_at');
        $users_count = $users_query->count();

        $users = collect();
        if ($users_count <= config('app.max_unpaginated_records')) {
            $users = $users_query->orderBy('first_name', 'asc')->orderBy('last_name', 'asc')->get();
        }

        // Show the page
        return view('groups/edit', compact('permissions', 'selectedPermissions', 'groupPermissions'))
            ->with('group', $group)
            ->with('associated_users', [])
            ->with('unselected_users', $users)
            ->with('all_users_count', $users_count);
    }

    /**
     * Validates and stores the new User Group data.
     *
     * @author [A. Gianotto] [<snipe@snipe.net]
     * @see GroupsController::getCreate()
     * @since [v1.0]
     */
    public function store(Request $request) : RedirectResponse
    {
        // create a new group instance
        $group = new Group();
        $group->name = $request->input('name');

        if ($request->filled('permission')) {
            $group->permissions = json_encode($request->array('permission'));
        } else {
            $group->permissions = null;
        }

        $group->permissions = json_encode($request->input('permission'));
        $group->created_by = auth()->id();
        $group->notes = $request->input('notes');

        if ($group->save()) {

            if ($request->filled('users_to_sync')) {
                $associated_users = explode(',',$request->input('users_to_sync'));
                $group->users()->sync($associated_users);
            }
            return redirect()->route('groups.index')->with('success', trans('admin/groups/message.success.create'));
        }

        return redirect()->back()->withInput()->withErrors($group->getErrors());
    }

    /**
     * Returns a view that presents a form to edit a User Group.
     *
     * @author [A. Gianotto] [<snipe@snipe.net]
     * @see GroupsController::postEdit()
     * @param int $id
     * @since [v1.0]
     */
    public function edit(Group $group) : View | RedirectResponse
    {
        $permissions = config('permissions');
        $groupPermissions = $group->decodePermissions();

        if ((!is_array($groupPermissions)) || (!$groupPermissions)) {
            $groupPermissions = [];
        }

        $selected_array = Helper::selectedPermissionsArray($permissions, $groupPermissions);


        $users_query = User::where('show_in_list', 1)->whereNull('deleted_at');
        $users_count = $users_query->count();

        $associated_users = collect();
        $unselected_users = collect();

        if ($users_count <= config('app.max_unpaginated_records')) {
            $associated_users = $group->users()->where('show_in_list', 1)->orderBy('first_name', 'asc')->orderBy('last_name', 'asc')->get();
            // Get the unselected users
            $unselected_users = User::where('show_in_list', 1)->whereNotIn('id', $associated_users->pluck('id')->toArray())->orderBy('first_name', 'asc')->orderBy('last_name', 'asc')->get();
        }

        return view('groups.edit', compact('group', 'permissions', 'selected_array', 'groupPermissions'))
            ->with('associated_users', $associated_users)
            ->with('unselected_users', $unselected_users)
            ->with('all_users_count', $users_count);
    }

    /**
     * Validates and stores the updated User Group data.
     *
     * @author [A. Gianotto] [<snipe@snipe.net]
     * @see GroupsController::getEdit()
     * @param int $id
     * @since [v1.0]
     */
    public function update(Request $request, Group $group) : RedirectResponse
    {
        $group->name = $request->input('name');

        if ($request->filled('permission')) {
            $group->permissions = json_encode($request->array('permission'));
        } else {
            $group->permissions = null;
        }

        $group->notes = $request->input('notes');


        if (! config('app.lock_passwords')) {
            if ($group->save()) {

                if ($request->has('users_to_sync')) {
                    $associated_users = explode(',',$request->input('users_to_sync'));
                    $group->users()->sync($associated_users);
                }

                return redirect()->route('groups.index')->with('success', trans('admin/groups/message.success.update'));
            }

            return redirect()->back()->withInput()->withErrors($group->getErrors());
        }

        return redirect()->route('groups.index')->with('error', trans('general.feature_disabled'));
    }

    /**
     * Validates and deletes the User Group.
     *
     * @author [A. Gianotto] [<snipe@snipe.net]
     * @see GroupsController::getEdit()
     * @param int $id
     * @since [v1.0]
     */
    public function destroy($id) : RedirectResponse
    {
        if (! config('app.lock_passwords')) {
            if (! $group = Group::find($id)) {
                return redirect()->route('groups.index')->with('error', trans('admin/groups/message.group_not_found', ['id' => $id]));
            }
            $group->delete();
            return redirect()->route('groups.index')->with('success', trans('admin/groups/message.success.delete'));
        }

        return redirect()->route('groups.index')->with('error', trans('general.feature_disabled'));
    }

    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the group detail page.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param $id
     * @since [v4.0.11]
     */
    public function show(Group $group) : View | RedirectResponse
    {
      return view('groups/view', compact('group'));
    }
}
