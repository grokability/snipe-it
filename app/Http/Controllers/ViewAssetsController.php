<?php

namespace App\Http\Controllers;

use App\Actions\CheckoutRequests\CancelCheckoutRequestAction;
use App\Actions\CheckoutRequests\CreateCheckoutRequestAction;
use App\Exceptions\AssetNotRequestable;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\RequestAssetCancelation;
use App\Notifications\RequestAssetNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use \Illuminate\Contracts\View\View;
use Exception;

/**
 * This controller handles all actions related to the ability for users
 * to view their own assets in the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class ViewAssetsController extends Controller
{
    /**
     * Redirect to the profile page.
     *
     */
    public function getIndex() : View | RedirectResponse
    {
        $user = User::with(
            'assets',
            'assets.model',
            'assets.model.fieldset.fields',
            'consumables',
            'accessories',
            'licenses',
        )->find(auth()->id());

        if (Setting::getSettings()->show_assigned_assets) {
            $user->load([
                'assets.assignedAssets',
                'assets.assignedAssets.model',
                'assets.assignedAssets.model.fieldset.fields',
            ]);
        }

        $field_array = array();

        // Loop through all the custom fields that are applied to any model the user has assigned
        foreach ($user->assets as $asset) {

            // Make sure the model has a custom fieldset before trying to loop through the associated fields
            if ($asset->model->fieldset) {

                foreach ($asset->model->fieldset->fields as $field) {
                    // check and make sure they're allowed to see the value of the custom field
                    if ($field->display_in_user_view == '1') {
                        $field_array[$field->db_column] = $field->name;
                    }
                    
                }
            }

            foreach ($asset->assignedAssets as $assignedAsset) {
                // Make sure the model has a custom fieldset before trying to loop through the associated fields
                if ($assignedAsset->model->fieldset) {

                    foreach ($assignedAsset->model->fieldset->fields as $field) {
                        // check and make sure they're allowed to see the value of the custom field
                        if ($field->display_in_user_view == '1') {
                            $field_array[$field->db_column] = $field->name;
                        }

                    }
                }
            }

        }

        // Since some models may re-use the same fieldsets/fields, let's make the array unique so we don't repeat columns
        array_unique($field_array);

        if (isset($user->id)) {
            return view('account/view-assets', compact('user', 'field_array' ))
                ->with('settings', Setting::getSettings());
        }

        // Redirect to the user management page
        return redirect()->route('users.index')
            ->with('error', trans('admin/users/message.user_not_found', $user->id));
    }

    /**
     * Returns view of requestable items for a user.
     */
    public function getRequestableIndex() : View
    {
        $assets = Asset::with('model', 'defaultLoc', 'location', 'assignedTo', 'requests')->Hardware()->RequestableAssets();
        $models = AssetModel::with('category', 'requests', 'assets')->RequestableModels()->get();

        return view('account/requestable-assets', compact('assets', 'models'));
    }

    public function getRequestItem(Request $request, $itemType, $itemId = null, $cancel_by_admin = false, $requestingUser = null): RedirectResponse
    {
        $item = null;
        $fullItemType = 'App\\Models\\'.studly_case($itemType);

        if ($itemType == 'asset_model') {
            $itemType = 'model';
        }
        $item = call_user_func([$fullItemType, 'find'], $itemId);

        $user = auth()->user();

        $logaction = new Actionlog();
        $logaction->item_id = $data['asset_id'] = $item->id;
        $logaction->item_type = $fullItemType;
        $logaction->created_at = $data['requested_date'] = date('Y-m-d H:i:s');

        if ($user->location_id) {
            $logaction->location_id = $user->location_id;
        }

        $logaction->target_id = $data['user_id'] = auth()->id();
        $logaction->target_type = User::class;

        $data['item_quantity'] = $request->has('request-quantity') ? e($request->input('request-quantity')) : 1;
        $data['requested_by'] = $user->present()->fullName();
        $data['item'] = $item;
        $data['item_type'] = $itemType;
        $data['target'] = auth()->user();

        if ($fullItemType == Asset::class) {
            $data['item_url'] = route('hardware.show', $item->id);
        } else {
            $data['item_url'] = route("view/${itemType}", $item->id);
        }

        $settings = Setting::getSettings();

        if (($item_request = $item->isRequestedBy($user)) || $cancel_by_admin) {
            $item->cancelRequest($requestingUser);
            $data['item_quantity'] = ($item_request) ? $item_request->qty : 1;
            $logaction->logaction('request_canceled');

            if (($settings->alert_email != '') && ($settings->alerts_enabled == '1') && (! config('app.lock_passwords'))) {
                $settings->notify(new RequestAssetCancelation($data));
            }

            return redirect()->back()->with('success')->with('success', trans('admin/hardware/message.requests.canceled'));
        } else {
            $item->request();
            if (($settings->alert_email != '') && ($settings->alerts_enabled == '1') && (! config('app.lock_passwords'))) {
                $logaction->logaction('requested');
                $settings->notify(new RequestAssetNotification($data));
            }

            return redirect()->route('requestable-assets')->with('success')->with('success', trans('admin/hardware/message.requests.success'));
        }
    }

    /**
     * Process a specific requested asset
     * @param null $assetId
     */
    public function store(Asset $asset): RedirectResponse
    {
        try {
            CreateCheckoutRequestAction::run($asset, auth()->user());
            return redirect()->route('requestable-assets')->with('success')->with('success', trans('admin/hardware/message.requests.success'));
        } catch (AssetNotRequestable $e) {
            return redirect()->back()->with('error', 'Asset is not requestable');
        } catch (AuthorizationException $e) {
            return redirect()->back()->with('error', trans('admin/hardware/message.requests.error'));
        } catch (Exception $e) {
            report($e);
            return redirect()->back()->with('error', trans('general.something_went_wrong'));
        }
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        try {
            CancelCheckoutRequestAction::run($asset, auth()->user());
            return redirect()->route('requestable-assets')->with('success')->with('success', trans('admin/hardware/message.requests.canceled'));
        } catch (Exception $e) {
            report($e);
            return redirect()->back()->with('error', trans('general.something_went_wrong'));
        }
    }


    public function getRequestedAssets() : View
    {
        return view('account/requested');
    }
}
