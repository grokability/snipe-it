<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\PredefinedFilter;
use App\Http\Transformers\SelectlistTransformer;


class PredefinedFilterController extends Controller
{
    public function index(Request $request)
    {
        //$this->authorize('view', PredefinedFilter::class);

        $filters = PredefinedFilter::
            orderBy('name')
            ->get(['id', 'name']);

        $viewableFilters = [];

        foreach ($filters as $filter) {
            //if ($filter->userHasPermission(auth()->user(), 'view') || $filter->created_by === auth()->user()->id) {
            $viewableFilters[] = $filter;
            //}
        }

        return response()->json($viewableFilters);
    }

    public function show(Request $request, string $id)
    {
        // $this->authorize('view', PredefinedFilter::class);

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
        // $this->authorize('create', PredefinedFilter::class);  // TODO: needed? or should everyone be able to create

        $rules = (new PredefinedFilter)->getRules();
        $validated = $request->validate($rules);

        $predefined_filter = PredefinedFilter::create([
            'name' => $validated['name'],
            'filter_data' => $validated['filter_data'],
            'created_by' => $request->user()->id
        ]);

        // Weiterleitung zur Detailseite (oder Übersicht)?
        return response()->json([
            'message' => __('admin/reports/message.create.success'),
            'filter_data' => $predefined_filter
        ], 201); // check for status code
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $filter = PredefinedFilter::find($id);

        if (!$filter) {
            return response()->json(['error' => 'Filter not found'], 404);
        }

        // $this->authorize('edit', PredefinedFilter::class);

        $rules = (new PredefinedFilter)->getRules();
        $validated = $request->validate($rules);

        $filter->update([
            'name' => $validated['name'],
            'filter_data' => $validated['filter_data'],
        ]);

        return response()->json([
            'message' => __('admin/reports/message.update.success'),
            'filter_data' => $filter
        ], 200); // check for status code
    }

    public function destroy(Request $request, int $id)
    {
        $filter = PredefinedFilter::find($id);

        // $this->authorize('delete', PredefinedFilter::class);

        if (!$filter) {
            return response()->json(['error' => 'Filter not found'], 404);
        }

        $filter->delete();

        return response()->json([
            'message' => __('admin/reports/message.delete.success'),
        ], 200); //check for status code
    }

    public function selectlist(Request $request)
    {
        $this->authorize('view.selectlists');
        $predefinedFilters = PredefinedFilter::select([
            'id',
            'name',
        ]);

        if ($request->filled('search')) {
            $predefinedFilters = $predefinedFilters->where('name', 'LIKE', '%'.$request->get('search').'%');
        }

        $predefinedFilters = $predefinedFilters->orderBy('name', 'ASC')->paginate(50);

        // Loop through and set some custom properties for the transformer to use.
        // This lets us have more flexibility in special cases like assets, where
        // they may not have a ->name value but we want to display something anyway
        foreach ($predefinedFilters as $predefinedFiler) {
            $predefinedFiler->use_text = $predefinedFiler->name;
            //$manufacturer->use_image = ($manufacturer->image) ? Storage::disk('public')->url('manufacturers/'.$manufacturer->image, $manufacturer->image) : null;
        }

        return (new SelectlistTransformer)->transformSelectlist($predefinedFilters);
    }


}