<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SavedSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Private-only CRUD for v1. Every endpoint scopes to the caller's own
 * records. The policy and the ownedByCurrent() builder method
 * provide DiD so a crafted URL or a missing policy
 * registration cannot leak another user's presets. (Which
 * wouldn't really matter in most cases, since the query_string
 * is just a URL query and the individual item permissions would block
 * the presentation/request, but still.)
 */
class SavedSearchesController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SavedSearch::class);

        $query = SavedSearch::ownedByCurrent()->orderBy('name', 'asc');

        if ($request->filled('table_name')) {
            $query->forTable((string) $request->input('table_name'));
        }

        return response()->json([
            'total' => $query->count(),
            'rows' => $query->get(['id', 'table_name', 'model', 'name', 'query_string', 'created_at', 'updated_at']),
        ]);
    }

    /**
     * Create a new saved search
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', SavedSearch::class);

        $savedSearch = new SavedSearch;
        $savedSearch->fill($request->only(['table_name', 'model', 'name', 'query_string']));
        $savedSearch->created_by = $request->user()->id;

        if (!$savedSearch->save()) {
            return response()->json([
                'status' => 'error',
                'messages' => $savedSearch->getErrors(),
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'payload' => $savedSearch,
        ], 201);
    }

    /**
     * Rename an existing saved search. Policy guards per-record ownership.
     */
    public function update(Request $request, SavedSearch $savedSearch): JsonResponse
    {
        $this->authorize('update', $savedSearch);

        $savedSearch->fill($request->only(['name']));

        if (!$savedSearch->save()) {
            return response()->json([
                'status' => 'error',
                'messages' => $savedSearch->getErrors(),
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'payload' => $savedSearch,
        ]);
    }

    /**
     * Delete a saved search. Policy guards per-record ownership.
     */
    public function destroy(SavedSearch $savedSearch): JsonResponse
    {
        $this->authorize('delete', $savedSearch);

        $savedSearch->delete();

        return response()->json(['status' => 'success']);
    }
}
