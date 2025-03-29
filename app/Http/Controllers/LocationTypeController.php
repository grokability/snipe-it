<?php

namespace App\Http\Controllers;

use App\Models\LocationType;
use Illuminate\Http\Request;

class LocationTypeController extends Controller
{
    public function index()
    {
        $types = LocationType::all();
        return view('location_types.index', compact('types'));
    }

    public function create()
    {
        return view('location_types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        LocationType::create($request->only('name'));

        return redirect()->route('location-types.index');
    }
}