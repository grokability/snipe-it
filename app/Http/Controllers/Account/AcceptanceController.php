<?php

namespace App\Http\Controllers\Account;

use App\Events\CheckoutAccepted;
use App\Events\CheckoutDeclined;
use App\Events\ItemAccepted;
use App\Events\ItemDeclined;
use App\Http\Controllers\Controller;
use App\Mail\CheckoutAcceptanceResponseMail;
use App\Models\CheckoutAcceptance;
use App\Models\Company;
use App\Models\Contracts\Acceptable;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\AcceptanceAssetAcceptedNotification;
use App\Notifications\AcceptanceAssetAcceptedToUserNotification;
use App\Notifications\AcceptanceAssetDeclinedNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use \Illuminate\Contracts\View\View;
use \Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use App\Helpers\Helper;

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
        $assigned_user = User::find($acceptance->assigned_to_id);
        $settings = Setting::getSettings();
        $sig_filename='';


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
         * Check for the signature directory
         */
        if (! Storage::exists('private_uploads/signatures')) {
            Storage::makeDirectory('private_uploads/signatures', 775);
        }

        /**
         * Check for the eula-pdfs directory
         */
        if (! Storage::exists('private_uploads/eula-pdfs')) {
            Storage::makeDirectory('private_uploads/eula-pdfs', 775);
        }

        $item = $acceptance->checkoutable_type::find($acceptance->checkoutable_id);



        // If signatures are required, make sure we have one
        if (Setting::getSettings()->require_accept_signature == '1') {

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


        // Convert PDF logo to base64 for TCPDF
        // This is needed for TCPDF to properly embed the image if it's a png and the cache isn't writable
        $encoded_logo = null;
        if ($settings->acceptance_pdf_logo) {
            $encoded_logo = base64_encode(file_get_contents(public_path() . '/uploads/' . $settings->acceptance_pdf_logo));
        }

        // Get the data array ready for the notifications and PDF generation
        $data = [
            'item_tag' => $item->asset_tag,
            'item_name' => $item->name, // this handles licenses seats, which don't have a 'name' field
            'item_model' => $item->model?->name,
            'item_serial' => $item->serial,
            'item_status' => $item->assetstatus?->name,
            'eula' => $item->getEula(),
            'note' => $request->input('note'),
            'check_out_date' => Helper::getFormattedDateObject($acceptance->created_at, 'datetime', false),
            'accepted_date' => Helper::getFormattedDateObject(now()->format('Y-m-d H:i:s'), 'datetime', false),
            'declined_date' => Helper::getFormattedDateObject(now()->format('Y-m-d H:i:s'), 'datetime', false),
            'assigned_to' => $assigned_user->display_name,
            'email' => $assigned_user->email,
            'employee_num' => $assigned_user->employee_num,
            'site_name' => $settings->site_name,
            'company_name' => $item->company?->name?? $settings->site_name,
            'signature' => (($sig_filename && array_key_exists('1', $encoded_image))) ? $encoded_image[1] : null,
            'logo' => ($encoded_logo) ?? null,
            'date_settings' => $settings->date_display_format,
            'admin' => auth()->user()->present()?->fullName,
            'qty' => $acceptance->qty ?? 1,
        ];


        if ($request->input('asset_acceptance') == 'accepted') {


            $pdf_filename = 'accepted-'.$acceptance->checkoutable_id.'-'.$acceptance->display_checkoutable_type.'-eula-'.date('Y-m-d-h-i-s').'.pdf';

            // Generate the PDF content
            $pdf_content = $acceptance->generateAcceptancePdf($data, $acceptance);
            Storage::put('private_uploads/eula-pdfs/' .$pdf_filename, $pdf_content);

            // Log the acceptance
            $acceptance->accept($sig_filename, $item->getEula(), $pdf_filename, $request->input('note'));

            // Send the PDF to the signing user
            if (($request->input('send_copy') == '1') && ($assigned_user->email !='')) {

                // Add the attachment for the signing user into the $data array
                $data['file'] = $pdf_filename;
                try {
                    $assigned_user->notify((new AcceptanceAssetAcceptedToUserNotification($data))->locale($assigned_user->locale));
                } catch (\Exception $e) {
                    Log::warning($e);
                }
            }
            try {
                $acceptance->notify((new AcceptanceAssetAcceptedNotification($data))->locale(Setting::getSettings()->locale));
            } catch (\Exception $e) {
                Log::warning($e);
            }
            event(new CheckoutAccepted($acceptance));

            $return_msg = trans('admin/users/message.accepted');

        // Item was declined
        } else {

            for ($i = 0; $i < ($acceptance->qty ?? 1); $i++) {
                $acceptance->decline($sig_filename, $request->input('note'));
            }

            $acceptance->notify(new AcceptanceAssetDeclinedNotification($data));
            Log::debug('New event acceptance.');
            event(new CheckoutDeclined($acceptance));
            $return_msg = trans('admin/users/message.declined');
        }


        // Send an email notification if one is requested
        if ($acceptance->alert_on_response_id) {
            try {
                $recipient = User::find($acceptance->alert_on_response_id);

                if ($recipient) {
                    Log::debug('Attempting to send email acceptance.');
                    Mail::to($recipient)->send(new CheckoutAcceptanceResponseMail(
                        $acceptance,
                        $recipient,
                        $request->input('asset_acceptance') === 'accepted',
                    ));
                    Log::debug('Send email notification sucess on checkout acceptance response.');
                }
            } catch (Exception $e) {
                Log::error($e->getMessage());
                Log::warning($e);
            }
        }
        return redirect()->to('account/accept')->with('success', $return_msg);

    }



}
