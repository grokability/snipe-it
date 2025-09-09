<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\PredefinedFilter;



class PredefinedFilterController extends Controller
{
    public function index(Request $request)
    {
        $filters = PredefinedFilter:://where('created_by', $request->user()->id)
            orderBy('name')
            ->get(['id','name']);

        return response()->json($filters);
    }

    public function show(Request $request, string $id)
    {
        $filter = PredefinedFilter::find($id)
            //->where('created_by', $request->user()->id)
            ;

        if (!$filter) {
            return response()->json(['error' => 'Filter not found'], 404);
        }
        return response()->json($filter->toArray());
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', PredefinedFilter::class);  // TODO: needed? or should everyone be able to create

        $rules = (new PredefinedFilter)->getRules();
        $validated = $request->validate($rules);

        $predefined_filter = PredefinedFilter::create([
            'name'=> $validated['name'],
            'filter_data'=> $validated['filter_data'],
            'created_by'=> $request->user()->id
        ]);

        // Weiterleitung zur Detailseite (oder Übersicht)
        return response()->json([
            'message' => __('admin/reports/message.create.success'),
            'filter_data' => $predefined_filter
        ], 201);
    }

    /* 
    public function update(Request $request, int $id)
    {
        throw new \Exception('Not Implemented');
    }

    public function destroy(Request $request, int $id){
        throw new \Exception('Not Implemented');
    }
    */
}