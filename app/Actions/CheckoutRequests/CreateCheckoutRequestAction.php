<?php

namespace App\Actions\CheckoutRequests;

use App\Exceptions\AssetNotRequestable;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\RequestAssetNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Log;
use Illuminate\Support\Facades\Notification;

class CreateCheckoutRequestAction
{
    /**
     * @throws AssetNotRequestable
     * @throws AuthorizationException
     */
    public static function run(Asset $asset, User $user): string
    {
        if (is_null(Asset::RequestableAssets()->find($asset->id))) {
            throw new AssetNotRequestable($asset);
        }
        if (!Company::isCurrentUserHasAccess($asset)) {
            throw new AuthorizationException();
        }
	$requester = auth()->user();
        $data['item'] = $asset;
        $data['target'] = $requester;
	$data['requester'] = $requester;
	$data['requested_for'] = $user;
        $data['item_quantity'] = 1;
        $settings = Setting::getSettings();

        $logaction = new Actionlog();
        $logaction->item_id = $data['asset_id'] = $asset->id;
        $logaction->item_type = $data['item_type'] = Asset::class;
        $logaction->created_at = $data['requested_date'] = date('Y-m-d H:i:s');
        $logaction->target_id = $data['user_id'] = $requester->id;
        $logaction->target_type = User::class;
        $logaction->location_id = $user->location_id ?? null;
        $logaction->logaction('requested');

        $asset->request();
        $asset->increment('requests_counter', 1);
        
        try {

	    $asset->loadMissing('location');
	    $locationId = $asset->location->id ?? null;

	    if ($locationId) {

		$recipients = User::where('activated', 1)
		    ->where('location_id', $locationId)
		    ->where('id', '!=', $requester->id)
		    ->get();

		Notification::send($recipients, new RequestAssetNotification($data));

	    } else {
		Log::warning("No location found for asset {$asset->id}");
	    }

	} catch (\Exception $e) {
	    Log::warning($e);
	}

        return true;
    }
}
