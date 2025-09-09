<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Actionlog;
use App\Models\Asset;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * This class controls all API actions related to notes for
 * the Snipe-IT Asset Management application.
 */
class NotesController extends Controller
{
    /**
     * Retrieve a list of manual notes (action logs) for a given asset.
     *
     * Checks authorization to view assets, attempts to find the asset by ID,
     * and fetches related action log entries of type 'note added', including
     * user information for each note. Returns a JSON response with the notes or errors.
     *
     * @param  \Illuminate\Http\Request  $request  The incoming HTTP request.
     * @param  Asset  $asset  The ID of the asset whose notes to retrieve.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Asset $asset): JsonResponse
    {
        $this->authorize('view', $asset);

        // Get the manual notes for the asset
        $notes = ActionLog::with('user:id,username')
            ->where('item_type', Asset::class)
            ->where('item_id', $asset->id)
            ->where('action_type', 'note added')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'created_at', 'note', 'created_by', 'item_id', 'item_type', 'action_type', 'target_id', 'target_type']);

        $notesArray = $notes->map(function ($note) {
            return [
                'id' => $note->id,
                'created_at' => $note->created_at,
                'note' => $note->note,
                'created_by' => $note->created_by,
                'username' => $note->user?->username, // adding the username
                'item_id' => $note->item_id,
                'item_type' => $note->item_type,
                'action_type' => $note->action_type,
            ];
        });

        // Return a success response
        return response()->json(Helper::formatStandardApiResponse('success', ['notes' => $notesArray, 'asset_id' => $asset->id]));
    }
    
    /**
     * Store a manual note on a specified asset and log the action.
     *
     * Checks authorization for updating assets, validates the presence of the 'note',
     * attempts to find the asset by ID, and creates a new ActionLog entry if successful.
     * Returns JSON responses indicating success or failure with appropriate HTTP status codes.
     *
     * @param  \Illuminate\Http\Request  $request  The incoming HTTP request containing the 'note'.
     * @param  Asset  $asset  The ID of the asset to attach the note to.
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Asset $asset): JsonResponse
    {
        $this->authorize('update', $asset);

        if ($request->input('note', '') == '') {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('validation.required', ['attribute' => 'note'])), 422);
        }

        // Create the note
        $logaction = new ActionLog();
        $logaction->item_type = get_class($asset);
        $logaction->created_by = Auth::id();
        $logaction->item_id = $asset->id;
        $logaction->note = $request->input('note', '');

        if ($logaction->logaction('note added')) {
            // Return a success response
            return response()->json(Helper::formatStandardApiResponse('success', ['note' => $logaction->note, 'item_id' => $asset->id], trans('general.note_added')));
        }

        // Return an error response if something went wrong
        return response()->json(Helper::formatStandardApiResponse('error', null, 'Something went wrong'), 500);
    }
}
