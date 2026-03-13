<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\ReturnRequest;
use App\Models\Statuslabel;
use App\Notifications\ReturnStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;

class ReturnsController extends Controller
{

	private function warehouseRecipients()
	{
	    $allowedGroups = ['Warehouse','Manager Archive','Archivist','Archivists'];

	    return \App\Models\User::query()
		->whereHas('groups', function ($g) use ($allowedGroups) {
		    $g->whereIn('name', $allowedGroups);
		})
		->get();
	}

    public function index()
    {
        $user = auth()->user();

        $isAdmin = $user && (
            Gate::allows('admin') ||
            (method_exists($user,'isSuperUser') && $user->isSuperUser())
        );

        $allowedGroups = ['Warehouse','Manager Archive','Archivist','Archivists'];
        $inAllowedGroup = $user && $user->groups()->whereIn('name', $allowedGroups)->exists();
        if (!($isAdmin || $inAllowedGroup)) abort(403);

        ReturnRequest::query()
            ->whereNull('canceled_at')
            ->whereNull('closed_at')
            ->whereNotNull('received_at')
            ->whereHas('asset', function ($q) {
                $q->whereNull('assigned_to'); // checked-in
            })
            ->update([
                'checked_in_at' => now(),
                'closed_at'     => now(),
            ]);

        $returns = ReturnRequest::query()
            ->whereNull('canceled_at')
            ->whereNull('closed_at')
            ->whereNull('checked_in_at')
            ->latest('requested_at')
            ->with('asset')
            ->get();

        return view('returns.index', compact('returns'));
    }

    // Return to Archive (δημιουργία request)
    public function store(Request $request, Asset $asset)
    {
        $user = auth()->user();

        $exists = ReturnRequest::where('asset_id', $asset->id)
            ->whereNull('canceled_at')
            ->whereNull('received_at')
            ->exists();

        if ($exists) {
            return back()->with('success', 'Return request already exists.');
        }

        $return = ReturnRequest::create([
            'asset_id'         => $asset->id,
            'requested_by'     => $user?->id,
            'from_location_id' => $asset->location_id,
            'requested_at'     => now(),
            'note'             => $request->input('note'),
        ]);

        // ✅ Notify Warehouse/Archivists to open /returns
        $recipients = $this->warehouseRecipients();
        Notification::send($recipients, new ReturnStatusNotification('requested', $return, $asset, $user));

        return back()->with('success', 'Return request sent to Warehouse.');
    }

    // Secretary: Mark In Transit
    public function markInTransit(ReturnRequest $return)
    {
        $user = auth()->user();
        $isSecretary = $user && $user->groups()->where('name', 'Secretary')->exists();
        if (!$isSecretary) abort(403);

        if ($return->canceled_at || $return->received_at) {
            return back()->with('error', 'Return is closed.');
        }

        if (is_null($return->in_transit_at)) {
            $return->in_transit_at = now();
            $return->save();

            $inTransitId = Statuslabel::where('name', 'In Transit')->value('id');
            if ($inTransitId && $return->asset) {
                $return->asset->status_id = $inTransitId;
                $return->asset->save();
            }

            // ✅ Notify Warehouse: it was sent (open /returns)
            $recipients = $this->warehouseRecipients();
            Notification::send($recipients, new ReturnStatusNotification('in_transit', $return, $return->asset, $user));
        }

        return back()->with('success', 'Marked as In Transit.');
    }

    // Warehouse: Mark Received
    public function markReceived(ReturnRequest $return)
    {
        $user = auth()->user();

        $canReceive = $user && (
            Gate::allows('superadmin') ||
            Gate::allows('admin') ||
            $user->groups()->whereIn('name', [
                'Warehouse','Manager Archive','Archivist','Archivists','Admin',
            ])->exists()
        );
        if (!$canReceive) abort(403);

        if ($return->canceled_at || $return->received_at) {
            return back()->with('error', 'Return is closed.');
        }

        $return->received_at = now();
        $return->save();

        // ✅ Notify the person who requested it: warehouse received it
        $requester = $return->requester; // relation
        if ($requester) {
            $requester->notify(new ReturnStatusNotification('received', $return, $return->asset, $user));
        }

        return back()->with('success', 'Marked as received.');
    }

    public function close(ReturnRequest $return)
    {
        $user = auth()->user();

        $canClose = $user && (
            Gate::allows('admin') ||
            (method_exists($user,'isSuperUser') && $user->isSuperUser()) ||
            $user->groups()->whereIn('name', ['Warehouse','Manager Archive','Archivist','Archivists'])->exists()
        );
        if (!$canClose) abort(403);

        if (!$return->received_at) {
            return back()->with('error', 'Cannot close before Mark Received.');
        }

        $return->checked_in_at = $return->checked_in_at ?? now();
        $return->closed_at = now();
        $return->save();

        return back()->with('success', 'Return closed.');
    }

    public function rows()
    {
        $me = auth()->user();

        $returns = ReturnRequest::with('asset')
            ->whereNull('closed_at')
            ->orderByDesc('requested_at')
            ->get();

        $isSecretary = $me && $me->groups()->where('name','Secretary')->exists();
        $allowedGroups = ['Warehouse','Manager Archive','Archivist','Archivists'];
        $canWarehouse = $me && (
            Gate::allows('admin') ||
            Gate::allows('superadmin') ||
            (method_exists($me,'isSuperUser') && $me->isSuperUser()) ||
            $me->groups()->whereIn('name', $allowedGroups)->exists()
        );

        return view('returns._rows', compact('returns','isSecretary','canWarehouse'));
    }
}

