<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\GroupsTransformer;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class GroupsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     */
    public function index(Request $request) : JsonResponse | array
    {
        $this->authorize('superadmin');

        $this->authorize('view', Group::class);

        $groups = Group::select('id', 'name', 'permissions', 'notes', 'created_at', 'updated_at', 'created_by')->with('adminuser')->withCount('users as users_count');

        if ($request->filled('search')) {
            $groups = $groups->TextSearch($request->input('search'));
        }

        if ($request->filled('name')) {
            $groups->where('name', '=', $request->input('name'));
        }


        $offset = ($request->input('offset') > $groups->count()) ? $groups->count() : app('api_offset_value');
        $limit = app('api_limit_value');
        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';

        switch ($request->input('sort')) {
            case 'created_by':
                $groups = $groups->OrderByCreatedBy($order);
                break;
            default:
                // This array is what determines which fields should be allowed to be sorted on ON the table itself.
                // These must match a column on the consumables table directly.
                $allowed_columns = [
                    'id',
                    'name',
                    'created_at',
                    'users_count',
                ];

                $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';
                $groups = $groups->orderBy($sort, $order);
                break;
        }

        $total = $groups->count();
        $groups = $groups->skip($offset)->take($limit)->get();

        return (new GroupsTransformer)->transformGroups($groups, $total);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request) : JsonResponse
    {
        $this->authorize('superadmin');
        $group = new Group;
        // Get all the available permissions
        $permissions = json_encode(config('permissions'));
        $groupPermissions = Helper::selectedPermissionsArray($permissions, $permissions);

        $group->name = $request->input('name');
        $group->created_by = auth()->id();
        $group->notes = $request->input('notes');
        $group->permissions = json_encode($request->input('permissions', $groupPermissions));

        if ($group->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', (new GroupsTransformer)->transformGroup($group), trans('admin/groups/message.success.create')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $group->getErrors()));
    }

    /**
     * Display the specified resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  int  $id
     */
    public function show($id) : array
    {
        $this->authorize('superadmin');
        $group = Group::findOrFail($id);
        return (new GroupsTransformer)->transformGroup($group);
    }

    /**
     * Update the specified resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function update(Request $request, $id) : JsonResponse
    {
        $this->authorize('superadmin');
        $group = Group::findOrFail($id);

        $group->name = $request->input('name');
        $group->notes = $request->input('notes');
        $group->permissions = $request->input('permissions'); // Todo - some JSON validation stuff here

        if ($group->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', (new GroupsTransformer)->transformGroup($group), trans('admin/groups/message.success.update')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $group->getErrors()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  int  $id
     */
    public function destroy($id) : JsonResponse
    {
        $this->authorize('superadmin');
        $group = Group::findOrFail($id);
        $group->delete();

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/groups/message.delete.success')));
    }
}
