<?php

namespace App\Http\Controllers\api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\SelectlistTransformer;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AssignedToDropdownController extends Controller
{
    public function selectlist(Request $request): array
    {
        // Authorization
        $request->headers->get('referer') === route('profile')
            ? $this->authorize('self.edit_location')
            : $this->authorize('view.selectlists');

        $page = $request->input('page', 1);
        $perPage = 50000;
        $search = $request->input('search');

        $locationQuery = Location::select(['id', 'name', 'image']);

        if (Setting::getSettings()->scope_locations_fmcs) {
            $locationQuery = Company::scopeCompanyables($locationQuery);
        }

        if ($search) {
            $locationQuery->where('name', 'LIKE', "%$search%");
        }

        $locations = $locationQuery->get()->map(function ($location) {
            return (object) [
                'id' => $location->id,
                'name' => $location->name,
                'use_text' => $location->name,
                'use_image' => $location->image,
                'use_item_key' => $location->id,
                'type' => 'location',
            ];
        });

        $assetQuery = Asset::select(['id', 'name', 'asset_tag', 'image']);

        if ($search) {
            $assetQuery->where('name', 'LIKE', "%$search%");
        }

        $assets = $assetQuery->get()->map(function ($asset) {
            if (!empty($asset->name)) {
                $name = $asset->name . " (#" . $asset->asset_tag . ")";
            } else {
                $name = "#" . $asset->asset_tag;
            }
            return (object) [
                'id' => $asset->id,
                'name' => $name,
                'use_text' => $name,
                'use_image' => $asset->image,
                'use_item_key' => $asset->id,
                'type' => 'asset',
            ];
        });

        $userQuery = User::select(['id', 'first_name', 'last_name']);

        if ($search) {
            $userQuery->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%$search%");
        }

        $users = $userQuery->get()->map(function ($user) {
            return (object) [
                'id' => $user->id,
                'name' => trim("{$user->first_name} {$user->last_name}"),
                'use_text' => trim("{$user->first_name} {$user->last_name}"),
                'use_image' => null,
                'use_item_key' => $user->id,
                'type' => 'user',
            ];
        });


        $combined = $locations->merge($assets)->merge($users)->sortBy('name')->values();

        // === PAGINATE ===
        $paginated = new LengthAwarePaginator(
            $combined->forPage($page, $perPage),
            $combined->count(),
            $perPage,
            $page,
            []
        );

        /*dump($locations);
        dump($assets);
        dump($users);*/
        //return [$paginated];
        return (new SelectlistTransformer)->transformSelectlist($paginated);
    }

}
