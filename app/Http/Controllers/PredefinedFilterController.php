<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\PredefinedFilter;
use Illuminate\Contracts\View\View;

class PredefinedFilterController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $filters = PredefinedFilter::with('permissionGroups')
            ->orderBy('name')
            ->get()
            ->filter(function ($filter) use ($user) {
                return $filter->created_by === $user->id
                    || ($filter->is_public && $filter->userHasPermission($user, 'view'));
            });

        return view('predefined-filters.index', compact('filters'));
    }


    /**
    * Show the given Predefined Filter.
    *
    * @param PredefinedFilter
    */
    public function view(PredefinedFilter $filter) : View|RedirectResponse
    {
        $user = auth()->user();

        $filter = PredefinedFilter::find($filter->id);

        if (!$filter) {
            return redirect()->back()->withErrors([
                'message' => trans('admin/predefinedFilters/message.does_not_exist'),
            ]);
        }

        if ($filter->created_by === $user->id || ($filter->is_public && $filter->userHasPermission($user, 'view'))) {

            return view('predefined-filters.view', compact('filter'));
        }

        return redirect()->route('predefined-filters.index')
            ->with('error', trans('admin/predefinedFilters/message.show.not_allowed'));
    }

    /**
    * Delete the given Predefined Filter.
    *
    * @param  int $Id
    */
    public function destroy($Id) : RedirectResponse
    {
        $user = auth()->user();

        $filter = PredefinedFilter::find($Id);

        if (!$filter) {
            return redirect()->route('predefined-filters.index')
                ->with('error', trans('admin/predefinedFilters/message.does_not_exist'));
        }

        if ($filter->created_by === $user->id) {
            $filter->delete();
            return redirect()->route('predefined-filters.index')
                ->with('success', trans('admin/predefinedFilters/message.delete.success'));
        }

            // It's public, so check permission logic
        if ($filter->is_public) {
            if (!$filter->userHasPermission($user, 'destroy')) {
                return redirect()->route('predefined-filters.index')
                    ->with('error', trans('admin/predefinedFilters/message.not_allowed_to_delete'));
            }

            $filter->delete();
                return redirect()->route('predefined-filters.index')
                    ->with('success', trans('admin/predefinedFilters/message.delete.success'));
        }

        return redirect()->route('predefined-filters.index')
            ->with('error', trans('admin/predefinedFilters/message.delete.not_allowed_to_delete'));
    }
}