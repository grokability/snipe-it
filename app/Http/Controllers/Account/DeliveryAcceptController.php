<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Statuslabel;
use Illuminate\Http\Request;

class DeliveryAcceptController extends Controller
{
    public function show($assetId)
    {
        $asset = Asset::findOrFail($assetId);
        return view('account.accept.accept-delivery', compact('asset'));
    }

    public function store(Request $request, $assetId)
    {
        $request->validate([
            'receiver_name' => 'required|string|max:255',
        ]);

        $asset = Asset::findOrFail($assetId);

        // Append delivery note to asset notes
        $existingNotes = $asset->notes ?? '';
        $asset->notes = trim($existingNotes . "\n\nDelivery accepted by: " . $request->receiver_name . " on " . now());

        // Set status to "With Department"
        $withDeptId = Statuslabel::where('name', 'With Department')->value('id');
        if ($withDeptId) {
            $asset->status_id = $withDeptId;
        }

        $asset->save();

        return redirect()->route('hardware.show', $assetId)
            ->with('success', 'Delivery accepted successfully.');
    }
}

