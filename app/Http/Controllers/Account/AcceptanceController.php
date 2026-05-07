<?php

namespace App\Http\Controllers\Account;

use App\Events\CheckoutAccepted;
use App\Events\CheckoutDeclined;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Mail\CheckoutAcceptanceResponseMail;
use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\AcceptanceItemAcceptedNotification;
use App\Notifications\AcceptanceItemAcceptedToUserNotification;
use App\Notifications\AcceptanceItemDeclinedNotification;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AcceptanceController extends Controller
{
    /**
     * Show a listing of pending checkout acceptances for the current user
     */
    public function index(): View
    {
        $acceptances = CheckoutAcceptance::forUser(auth()->user())->pending()->get();

        return view('account/accept.index', compact('acceptances'));
    }

    /**
     * Shows a form to either accept or decline the checkout acceptance
     *
     * @param  int  $id
     */
    public function create(Request $request, $id): View|RedirectResponse
    {
        $currentUser = auth()->user();

        if (! $currentUser instanceof User) {
            abort(403, trans('general.insufficient_permissions'));
        }

        $acceptance = CheckoutAcceptance::find($id);

        if (! $acceptance) {
            return redirect()->route('account.accept')->with('error', trans('admin/hardware/message.does_not_exist'));
        }

        if (! $acceptance->isPending()) {
            if ($this->isStaleSignInPlaceAdminAttempt($acceptance, $currentUser)) {
                return $this->redirectToIntendedSignInPlaceDestination($request, $acceptance)
                    ->with('warning', trans('admin/users/message.error.asset_already_accepted'));
            }

            return redirect()->route('account.accept')->with('error', trans('admin/users/message.error.asset_already_accepted'));
        }

        $isSignInPlaceAdminFlow = $this->isSignInPlaceAdminFlow($acceptance);

        if (! $acceptance->isCheckedOutTo($currentUser) && (! $isSignInPlaceAdminFlow)) {
            return redirect()->route('account.accept')->with('error', trans('admin/users/message.error.incorrect_user_accepted'));
        }

        if (! Company::isCurrentUserHasAccess($acceptance->checkoutable)) {
            return redirect()->route('account.accept')->with('error', trans('general.error_user_company'));
        }

        $checkedOutAt = Helper::getFormattedDateObject($acceptance->created_at, 'datetime', false);
        $checkedOutBy = $this->resolveCheckoutActorName($acceptance);

        return view('account/accept.create', compact('acceptance', 'isSignInPlaceAdminFlow', 'checkedOutAt', 'checkedOutBy'));
    }

    /**
     * Stores the accept/decline of the checkout acceptance
     *
     * @param  int  $id
     */
    public function store(Request $request, $id): RedirectResponse
    {
        $currentUser = auth()->user();

        if (! $currentUser instanceof User) {
            abort(403, trans('general.insufficient_permissions'));
        }

        $acceptance = CheckoutAcceptance::find($id);

        if (! $acceptance) {
            return redirect()->route('account.accept')->with('error', trans('admin/hardware/message.does_not_exist'));
        }

        $assignedUser = User::find($acceptance->assigned_to_id);
        $settings = Setting::getSettings();
        $requiresSignature = (string) $settings->require_accept_signature === '1';
        $sig_filename = '';
        $encodedSignatureImage = null;

        if (! $acceptance->isPending()) {
            if ($this->isStaleSignInPlaceAdminAttempt($acceptance, $currentUser)) {
                return $this->redirectToIntendedSignInPlaceDestination($request, $acceptance)
                    ->with('warning', trans('admin/users/message.error.asset_already_accepted'));
            }

            return redirect()->route('account.accept')->with('error', trans('admin/users/message.error.asset_already_accepted'));
        }

        $isSignInPlaceAdminFlow = $this->isSignInPlaceAdminFlow($acceptance);

        if (! $acceptance->isCheckedOutTo($currentUser) && (! $isSignInPlaceAdminFlow)) {
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
        if ($requiresSignature) {

            // The item was accepted, check for a signature
            if ($request->filled('signature_output')) {
                $sig_filename = 'siglog-'.Str::uuid().'-'.date('Y-m-d-his').'.png';
                $dataUri = (string) $request->input('signature_output');
                $encodedSignatureImage = Str::contains($dataUri, ',')
                    ? Str::after($dataUri, ',')
                    : $dataUri;

                $decoded_image = base64_decode($encodedSignatureImage, true);

                if ($decoded_image === false) {
                    return redirect()->back()->with('error', trans('general.shitty_browser'));
                }

                $decoded_image = $this->flattenSignatureBackgroundToWhite($decoded_image);
                $encodedSignatureImage = base64_encode($decoded_image);

                Storage::put('private_uploads/signatures/'.$sig_filename, (string) $decoded_image);

                // No image data is present, kick them back.
                // This mostly only applies to users on super-duper crapola browsers *cough* IE *cough*
            } else {
                return redirect()->back()->with('error', trans('general.shitty_browser'));
            }
        }

        // Convert PDF logo to base64 for TCPDF
        // This is needed for TCPDF to properly embed the image if it's a png and the cache isn't writable
        $encoded_logo = null;
        if (($settings->acceptance_pdf_logo) && (Storage::disk('public')->exists($settings->acceptance_pdf_logo))) {
            $encoded_logo = base64_encode(file_get_contents(public_path().'/uploads/'.basename($settings->acceptance_pdf_logo)));
        }

        // Get the data array ready for the notifications and PDF generation
        $data = [
            'item_tag' => $item->asset_tag,
            'item_name' => $item->display_name, // this handles licenses seats, which don't have a 'name' field
            'item_model' => $item->model?->name,
            'item_serial' => $item->serial,
            'item_status' => $item->status?->name,
            'eula' => $item->getEula(),
            'note' => $request->input('note'),
            'check_out_date' => Helper::getFormattedDateObject($acceptance->created_at, 'datetime', false),
            'accepted_date' => Helper::getFormattedDateObject(now()->format('Y-m-d H:i:s'), 'datetime', false),
            'declined_date' => Helper::getFormattedDateObject(now()->format('Y-m-d H:i:s'), 'datetime', false),
            'assigned_to' => $assignedUser->display_name,
            'email' => $assignedUser->email,
            'employee_num' => $assignedUser->employee_num,
            'site_name' => $settings->site_name,
            'company_name' => $item->company?->name ?? $settings->site_name,
            'signature' => ($sig_filename !== '') ? $encodedSignatureImage : null,
            'logo' => ($encoded_logo) ?? null,
            'date_settings' => $settings->date_display_format,
            'qty' => $acceptance->qty ?? 1,
        ];

        // Include asset custom fields that are explicitly allowed in outbound emails/PDFs.
        if ($item instanceof Asset && $item->model && $item->model->fieldset) {
            $customFields = [];
            $fields = $item->model->fieldset->fields
                ->where('show_in_email', true)
                ->where('field_encrypted', false);

            foreach ($fields as $field) {
                $dbColumn = $field->db_column;
                $value = $item->{$dbColumn};

                if (! is_null($value) && $value !== '') {
                    $customFields[] = [
                        'label' => $field->name,
                        'value' => $value,
                    ];
                }
            }

            if (! empty($customFields)) {
                $data['custom_fields'] = $customFields;
            }
        }

        if ($request->input('asset_acceptance') === 'accepted') {

            $pdf_filename = 'accepted-'.$acceptance->checkoutable_id.'-'.$acceptance->display_checkoutable_type.'-eula-'.date('Y-m-d-h-i-s').'.pdf';

            // Generate the PDF content
            $pdf_content = $acceptance->generateAcceptancePdf($data, $acceptance);
            Storage::put('private_uploads/eula-pdfs/'.$pdf_filename, $pdf_content);

            // Log the acceptance
            $acceptance->accept($sig_filename, $item->getEula(), $pdf_filename, $request->input('note'));

            // Send the PDF to the signing user
            if (($request->input('send_copy') === '1') && ($assignedUser->email !== '')) {

                // Add the attachment for the signing user into the $data array
                $data['file'] = $pdf_filename;
                try {
                    $assignedUser->notify((new AcceptanceItemAcceptedToUserNotification($data))->locale($assignedUser->locale));
                } catch (Exception $e) {
                    Log::warning($e);
                }
            }
            try {
                $acceptance->notify((new AcceptanceItemAcceptedNotification($data))->locale(Setting::getSettings()->locale));
            } catch (Exception $e) {
                Log::warning($e);
            }
            event(new CheckoutAccepted($acceptance));

            $return_msg = trans('admin/users/message.accepted');

            // Item was declined
        } else {

            for ($i = 0; $i < ($acceptance->qty ?? 1); $i++) {
                $acceptance->decline($sig_filename, $request->input('note'));
            }

            $acceptance->notify(new AcceptanceItemDeclinedNotification($data));
            Log::debug('New event acceptance.');
            event(new CheckoutDeclined($acceptance));
            $return_msg = trans('admin/users/message.declined');
        }

        // Send an email notification if one is requested
        if ($acceptance->alert_on_response_id) {
            try {
                $recipient = User::find($acceptance->alert_on_response_id);

                if ($recipient?->email) {
                    Log::debug('Attempting to send email acceptance.');
                    Mail::to($recipient)->send(new CheckoutAcceptanceResponseMail(
                        $acceptance,
                        $recipient,
                        $request->input('asset_acceptance') === 'accepted',
                    ));
                    Log::debug('Send email notification success on checkout acceptance response.');
                }
            } catch (Exception $e) {
                Log::error($e->getMessage());
                Log::warning($e);
            }
        }

        if ($isSignInPlaceAdminFlow) {
            $request->request->add(['assigned_user' => $assignedUser?->id]);

            $redirect = Helper::getRedirectOption(
                $request,
                session('sign_in_place_item_id'),
                session('sign_in_place_resource_type'),
            );

            session()->forget([
                'sign_in_place_acceptance_id',
                'sign_in_place_item_id',
                'sign_in_place_resource_type',
            ]);

            return $redirect->with('success', $return_msg);
        }

        return redirect()->to('account/accept')->with('success', $return_msg);

    }

    private function isSignInPlaceAdminFlow(CheckoutAcceptance $acceptance): bool
    {
        $currentUser = auth()->user();

        return ((int) session('sign_in_place_acceptance_id') === (int) $acceptance->id)
            && ($currentUser?->can('checkout', $acceptance->checkoutable));
    }

    private function resolveCheckoutActorName(CheckoutAcceptance $acceptance): ?string
    {
        [$itemType, $itemId] = $this->resolveCheckoutLogItem($acceptance);

        $checkoutLog = Actionlog::query()
            ->where('action_type', 'checkout')
            ->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->where('target_type', User::class)
            ->where('target_id', $acceptance->assigned_to_id)
            ->where('created_at', '<=', $acceptance->created_at->copy()->addMinutes(5))
            ->latest('id')
            ->first();

        return $checkoutLog?->adminuser?->display_name;
    }

    /**
     * Action logs normalize license seat checkouts to the parent license.
     *
     * @return array{0: class-string, 1: int}
     */
    private function resolveCheckoutLogItem(CheckoutAcceptance $acceptance): array
    {
        $checkoutable = $acceptance->checkoutable;

        if ($checkoutable instanceof LicenseSeat) {
            return [License::class, (int) $checkoutable->license_id];
        }

        return [$acceptance->checkoutable_type, (int) $acceptance->checkoutable_id];
    }

    private function isStaleSignInPlaceAdminAttempt(CheckoutAcceptance $acceptance, User $currentUser): bool
    {
        $redirectOption = session('redirect_option');
        $checkoutToType = session('checkout_to_type');

        if (session('sign_in_place') !== true) {
            return false;
        }

        if ($redirectOption === null) {
            return false;
        }

        if ($redirectOption === 'target' && $checkoutToType === 'user' && empty($acceptance->assigned_to_id)) {
            return false;
        }

        return ! $acceptance->isCheckedOutTo($currentUser)
            && $currentUser->can('checkout', $acceptance->checkoutable)
            && ($checkoutToType === 'user');
    }

    private function redirectToIntendedSignInPlaceDestination(Request $request, CheckoutAcceptance $acceptance): RedirectResponse
    {
        if (empty($acceptance->assigned_to_id)) {
            return redirect()->route('account.accept');
        }

        [$itemId, $resourceType] = $this->resolveRedirectTarget($acceptance);

        $request->request->add(['assigned_user' => $acceptance->assigned_to_id]);

        return Helper::getRedirectOption($request, $itemId, $resourceType);
    }

    /**
     * @return array{0: int, 1: string}
     */
    private function resolveRedirectTarget(CheckoutAcceptance $acceptance): array
    {
        $checkoutable = $acceptance->checkoutable;

        if ($checkoutable instanceof Asset) {
            return [(int) $checkoutable->id, 'Assets'];
        }

        if ($checkoutable instanceof Accessory) {
            return [(int) $checkoutable->id, 'Accessories'];
        }

        if ($checkoutable instanceof Consumable) {
            return [(int) $checkoutable->id, 'Consumables'];
        }

        if ($checkoutable instanceof LicenseSeat) {
            return [(int) $checkoutable->license_id, 'Licenses'];
        }

        return [(int) $acceptance->checkoutable_id, session('sign_in_place_resource_type', 'Assets')];
    }

    private function flattenSignatureBackgroundToWhite(string $signatureBinary): string
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagecreatetruecolor')) {
            return $signatureBinary;
        }

        $source = @imagecreatefromstring($signatureBinary);

        if ($source === false) {
            return $signatureBinary;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $flattened = imagecreatetruecolor($width, $height);

        if ($flattened === false) {
            imagedestroy($source);

            return $signatureBinary;
        }

        $white = imagecolorallocate($flattened, 255, 255, 255);
        imagefilledrectangle($flattened, 0, 0, $width, $height, $white);
        imagecopy($flattened, $source, 0, 0, 0, 0, $width, $height);

        ob_start();
        imagepng($flattened);
        $output = ob_get_clean();

        imagedestroy($source);
        imagedestroy($flattened);

        return is_string($output) ? $output : $signatureBinary;
    }
}
