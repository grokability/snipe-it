<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PredefinedFilter;

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

        return view('predefined_filters.index', compact('filters'));
    }
}