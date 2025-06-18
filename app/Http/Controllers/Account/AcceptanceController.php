<?php

namespace App\Http\Controllers\Account;

use App\Events\CheckoutAccepted;
use App\Events\CheckoutDeclined;
use App\Events\ItemAccepted;
use App\Events\ItemDeclined;
use App\Http\Controllers\Controller;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Company;
use App\Models\Contracts\Acceptable;
use App\Models\Setting;
use App\Models\User;
use App\Models\AssetModel;
use App\Models\Accessory;
use App\Models\License;
use App\Models\Component;
use App\Models\Consumable;
use App\Notifications\AcceptanceAssetAcceptedNotification;
use App\Notifications\AcceptanceAssetDeclinedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\SettingsController;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use \Illuminate\Contracts\View\View;
use \Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class AcceptanceController extends Controller
{
    /**
     * Show a listing of pending checkout acceptances for the current user
     */
    public function index() : View
    {
        $acceptances = CheckoutAcceptance::forUser(auth()->user())->pending()->get();
        return view('account/accept.index', compact('acceptances'));
    }

    /**
     * Shows a form to either accept or decline the checkout acceptance
     *
     * @param  int  $id
     */
    public function create($id) : View | RedirectResponse
    {
        $acceptance = CheckoutAcceptance::find($id);


        if (is_null($acceptance)) {
            return redirect()->route('account.accept')->with('error', trans('admin/hardware/message.does_not_exist'));
        }

        if (! $acceptance->isPending()) {
            return redirect()->route('account.accept')->with('error', trans('admin/users/message.error.asset_already_accepted'));
        }

        if (! $acceptance->isCheckedOutTo(auth()->user())) {
            return redirect()->route('account.accept')->with('error', trans('admin/users/message.error.incorrect_user_accepted'));
        }

        if (! Company::isCurrentUserHasAccess($acceptance->checkoutable)) {
            return redirect()->route('account.accept')->with('error', trans('general.error_user_company'));
        }

        return view('account/accept.create', compact('acceptance'));
    }

    /**
     * Stores the accept/decline of the checkout acceptance
     *
     * @param  Request $request
     * @param  int  $id
     */
    public function store(Request $request, $id) : RedirectResponse
    {
        $acceptance = CheckoutAcceptance::find($id);

        if (is_null($acceptance)) {
            return redirect()->route('account.accept')->with('error', trans('admin/hardware/message.does_not_exist'));
        }

        if (! $acceptance->isPending()) {
            return redirect()->route('account.accept')->with('error', trans('admin/users/message.error.asset_already_accepted'));
        }

        if (! $acceptance->isCheckedOutTo(auth()->user())) {
            return redirect()->route('account.accept')->with('error', trans('admin/users/message.error.incorrect_user_accepted'));
        }

        if (! Company::isCurrentUserHasAccess($acceptance->checkoutable)) {
            return redirect()->route('account.accept')->with('error', trans('general.insufficient_permissions'));
        }

        if (! $request->filled('asset_acceptance')) {
            return redirect()->back()->with('error', trans('admin/users/message.error.accept_or_decline'));
        }

        /**
         * Get the signature and save it
         */
        if (! Storage::exists('private_uploads/signatures')) {
            Storage::makeDirectory('private_uploads/signatures', 775);
        }



        $item = $acceptance->checkoutable_type::find($acceptance->checkoutable_id);
        $display_model = '';
        $pdf_view_route = '';
        $pdf_filename = 'accepted-eula-'.date('Y-m-d-h-i-s').'.pdf';
        $sig_filename='';

        if ($request->input('asset_acceptance') == 'accepted') {

            /**
             * Check for the eula-pdfs directory
             */
            if (! Storage::exists('private_uploads/eula-pdfs')) {
                Storage::makeDirectory('private_uploads/eula-pdfs', 775);
            }

            if (Setting::getSettings()->require_accept_signature == '1') {
                
                // Check if the signature directory exists, if not create it
                if (!Storage::exists('private_uploads/signatures')) {
                    Storage::makeDirectory('private_uploads/signatures', 775);
                }

                // The item was accepted, check for a signature
                if ($request->filled('signature_output')) {
                    $sig_filename = 'siglog-' . Str::uuid() . '-' . date('Y-m-d-his') . '.png';
                    $data_uri = $request->input('signature_output');
                    $encoded_image = explode(',', $data_uri);
                    $decoded_image = base64_decode($encoded_image[1]);
                    Storage::put('private_uploads/signatures/' . $sig_filename, (string)$decoded_image);

                    // No image data is present, kick them back.
                    // This mostly only applies to users on super-duper crapola browsers *cough* IE *cough*
                } else {
                    return redirect()->back()->with('error', trans('general.shitty_browser'));
                }
            }

            // this is horrible
            switch($acceptance->checkoutable_type){
                case 'App\Models\Asset':
                        $pdf_view_route ='account.accept.accept-asset-eula';
                        $asset_model = AssetModel::find($item->model_id);
                        if (!$asset_model) {
                            return redirect()->back()->with('error', trans('admin/models/message.does_not_exist'));
                        }
                        $display_model = $asset_model->name;
                        $assigned_to = User::find($acceptance->assigned_to_id)->present()->fullName;
                break;

                case 'App\Models\Accessory':
                        $pdf_view_route ='account.accept.accept-accessory-eula';
                        $accessory = Accessory::find($item->id);
                        $display_model = $accessory->name;
                        $assigned_to = User::find($acceptance->assigned_to_id)->present()->fullName;
                break;

                case 'App\Models\LicenseSeat':
                        $pdf_view_route ='account.accept.accept-license-eula';
                        $license = License::find($item->license_id);
                        $display_model = $license->name;
                        $assigned_to = User::find($acceptance->assigned_to_id)->present()->fullName;
                break;

                case 'App\Models\Component':
                        $pdf_view_route ='account.accept.accept-component-eula';
                        $component = Component::find($item->id);
                        $display_model = $component->name;
                        $assigned_to = User::find($acceptance->assigned_to_id)->present()->fullName;
                break;

                case 'App\Models\Consumable':
                        $pdf_view_route ='account.accept.accept-consumable-eula';
                        $consumable = Consumable::find($item->id);
                        $display_model = $consumable->name;
                        $assigned_to = User::find($acceptance->assigned_to_id)->present()->fullName;
                break;
            }
//            if ($acceptance->checkoutable_type == 'App\Models\Asset') {
//                $pdf_view_route ='account.accept.accept-asset-eula';
//                $asset_model = AssetModel::find($item->model_id);
//                $display_model = $asset_model->name;
//                $assigned_to = User::find($item->assigned_to)->present()->fullName;
//
//            } elseif ($acceptance->checkoutable_type== 'App\Models\Accessory') {
//                $pdf_view_route ='account.accept.accept-accessory-eula';
//                $accessory = Accessory::find($item->id);
//                $display_model = $accessory->name;
//                $assigned_to = User::find($item->assignedTo);
//
//            }

            /**
             * Gather the data for the PDF. We fire this whether there is a signature required or not,
             * since we want the moment-in-time proof of what the EULA was when they accepted it.
             */
            $branding_settings = SettingsController::getPDFBranding();

            $path_logo = "";

            // Check for the PDF logo path and use that, otherwise use the regular logo path
            if (!is_null($branding_settings->acceptance_pdf_logo)) {
                $path_logo = public_path() . '/uploads/' . $branding_settings->acceptance_pdf_logo;
            } elseif (!is_null($branding_settings->logo)) {
                $path_logo = public_path() . '/uploads/' . $branding_settings->logo;
            }
            
            $data = [
                'item_tag' => $item->asset_tag,
                'item_model' => $display_model,
                'item_serial' => $item->serial,
                'item_status' => $item->assetstatus?->name,
                'eula' => $item->getEula(),
                'note' => $request->input('note'),
                'check_out_date' => Carbon::parse($acceptance->created_at)->format('Y-m-d'),
                'accepted_date' => Carbon::parse($acceptance->accepted_at)->format('Y-m-d'),
                'assigned_to' => $assigned_to,
                'company_name' => $branding_settings->site_name,
                'signature' => ($sig_filename) ? storage_path() . '/private_uploads/signatures/' . $sig_filename : null,
                'logo' => $path_logo,
                'date_settings' => $branding_settings->date_display_format,
            ];

            if ($pdf_view_route!='') {
                Log::debug($pdf_filename.' is the filename, and the route was specified.');
                $pdf = Pdf::loadView($pdf_view_route, $data);
                Storage::put('private_uploads/eula-pdfs/' .$pdf_filename, $pdf->output());
            }

            $acceptance->accept($sig_filename, $item->getEula(), $pdf_filename, $request->input('note'));
            try {
                $acceptance->notify(new AcceptanceAssetAcceptedNotification($data));
            } catch (\Exception $e) {
                Log::warning($e);
            }
            event(new CheckoutAccepted($acceptance));

            $return_msg = trans('admin/users/message.accepted');

        } else {

            /**
             * Check for the eula-pdfs directory
             */
            if (! Storage::exists('private_uploads/eula-pdfs')) {
                Storage::makeDirectory('private_uploads/eula-pdfs', 775);
            }

            if (Setting::getSettings()->require_accept_signature == '1') {
                
                // Check if the signature directory exists, if not create it
                if (!Storage::exists('private_uploads/signatures')) {
                    Storage::makeDirectory('private_uploads/signatures', 775);
                }

                // The item was accepted, check for a signature
                if ($request->filled('signature_output')) {
                    $sig_filename = 'siglog-' . Str::uuid() . '-' . date('Y-m-d-his') . '.png';
                    $data_uri = $request->input('signature_output');
                    $encoded_image = explode(',', $data_uri);
                    $decoded_image = base64_decode($encoded_image[1]);
                    Storage::put('private_uploads/signatures/' . $sig_filename, (string)$decoded_image);

                    // No image data is present, kick them back.
                    // This mostly only applies to users on super-duper crapola browsers *cough* IE *cough*
                } else {
                    return redirect()->back()->with('error', trans('general.shitty_browser'));
                }
            }
            
            // Format the data to send the declined notification
            $branding_settings = SettingsController::getPDFBranding();

            // This is the most horriblest
            switch($acceptance->checkoutable_type){
                case 'App\Models\Asset':
                    $asset_model = AssetModel::find($item->model_id);
                    $display_model = $asset_model->name;
                    $assigned_to = User::find($acceptance->assigned_to_id)->present()->fullName;
                    break;

                case 'App\Models\Accessory':
                    $accessory = Accessory::find($item->id);
                    $display_model = $accessory->name;
                    $assigned_to = User::find($acceptance->assigned_to_id)->present()->fullName;
                    break;

                case 'App\Models\LicenseSeat':
                    $assigned_to = User::find($acceptance->assigned_to_id)->present()->fullName;
                    break;

                case 'App\Models\Component':
                    $assigned_to = User::find($acceptance->assigned_to_id)->present()->fullName;
                    break;

                case 'App\Models\Consumable':
                    $consumable = Consumable::find($item->id);
                    $display_model = $consumable->name;
                    $assigned_to = User::find($acceptance->assigned_to_id)->present()->fullName;
                    break;
            }

            $data = [
                'item_tag' => $item->asset_tag,
                'item_model' => $display_model,
                'item_serial' => $item->serial,
                'item_status' => $item->assetstatus?->name,
                'note' => $request->input('note'),
                'declined_date' => Carbon::parse($acceptance->declined_at)->format('Y-m-d'),
                'signature' => ($sig_filename) ? storage_path() . '/private_uploads/signatures/' . $sig_filename : null,
                'assigned_to' => $assigned_to,
                'company_name' => $branding_settings->site_name,
                'date_settings' => $branding_settings->date_display_format,
            ];

            if ($pdf_view_route!='') {
                Log::debug($pdf_filename.' is the filename, and the route was specified.');
                $pdf = Pdf::loadView($pdf_view_route, $data);
                Storage::put('private_uploads/eula-pdfs/' .$pdf_filename, $pdf->output());
            }

            $acceptance->decline($sig_filename, $request->input('note'));
            $acceptance->notify(new AcceptanceAssetDeclinedNotification($data));
            event(new CheckoutDeclined($acceptance));
            $return_msg = trans('admin/users/message.declined');
        }


        return redirect()->to('account/accept')->with('success', $return_msg);

    }

}
