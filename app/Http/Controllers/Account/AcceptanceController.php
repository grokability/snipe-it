<?php

namespace App\Http\Controllers\Account;

use App\Enums\FileStorage;
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

        // Bound the note server-side. Unbounded notes were reaching synchronous
        // CommonMark rendering in the acceptance notification email and
        // consuming worker CPU on a per-request basis (defense in depth against
        // the parser CVE, with the commonmark bump to 2.9.0 as the primary fix).
        //
        // Bound signature_output as well. Legitimate signaturepad canvas output
        // is well under 100 KB base64. The 2 MB cap here keeps a crafted
        // payload from forcing base64_decode and the downstream image
        // flattening into very large allocations before any dimension check
        // runs.
        $request->validate([
            'note' => 'nullable|string|max:1000',
            'signature_output' => 'nullable|string|max:2097152',
        ]);

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

        $item = $acceptance->checkoutable_type::find($acceptance->checkoutable_id);

        $username_slug = Str::slug($assignedUser->username);
        $asset_tag_slug = ($item instanceof Asset && $item->asset_tag) ? '-'.Str::slug($item->asset_tag) : '';

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

                // Validate the decoded bytes are a PNG and bound dimensions
                // BEFORE allocating any pixel buffer. getimagesizefromstring
                // reads headers only, so a crafted payload with large declared
                // width/height is rejected without imagecreatefromstring
                // decoding the raster or the flatten path allocating a second
                // same-size buffer. IMAGETYPE_PNG is enforced because the
                // signature-pad JavaScript creates only PNG files.
                //
                // Dimension ceiling is operator-tunable via the
                // ACCEPTANCE_SIGNATURE_MAX_DIMENSION env var. 
                $maxSignatureDimension = (int) config('app.acceptance_signature_max_dimension', 6000);
                $imgInfo = @getimagesizefromstring($decoded_image);
                if ($imgInfo === false || $imgInfo[2] !== IMAGETYPE_PNG) {
                    return redirect()->back()->with('error', trans('general.shitty_browser'));
                }
                if ($imgInfo[0] > $maxSignatureDimension || $imgInfo[1] > $maxSignatureDimension) {
                    // Specific error for the operator-tunable dimension
                    // cap so a user on a high-DPI browser doesn't get
                    // the misleading "use a more modern browser" message
                    // when their browser is fine and their signature is
                    // just physically too wide. The message tells the
                    // user what to try and the admin what to tune.
                    return redirect()->back()->with('error', trans('general.signature_too_large', ['max' => $maxSignatureDimension]));
                }

                // Flatten the signature's transparent background onto
                // white and canonically re-encode. Fail-closed if that
                // round-trip does not produce bytes: the previous
                // "keep the original bytes" fallback let header-only
                // PNG payloads (valid IHDR, no IDAT) through
                // getimagesizefromstring's shallow check and down to
                // Storage::put under a .png filename. Reported by
                // Wojciech Ciemski (WojciechCiemski) post-FD-57825 fix.
                $decoded_image = $this->flattenSignatureBackgroundToWhite($decoded_image);
                if ($decoded_image === null) {
                    Log::warning('Acceptance signature re-encode failed. Rejecting the submission to avoid storing non-decodable bytes.');

                    return redirect()->back()->with('error', trans('general.shitty_browser'));
                }
                $encodedSignatureImage = base64_encode($decoded_image);

                // Storage::put returns false on silent write failures on
                // non-throwing filesystem drivers. Ignoring the return let
                // acceptance finalization proceed while the signature file
                // was absent from disk, producing an "accepted" record whose
                // evidence file did not exist. Refuse to advance when the
                // write did not land. Reported by Christopher Finks
                // (christopherfi-dev) on 2026-08-02.
                if (! Storage::put(FileStorage::Signatures->privateStorageKey().$sig_filename, (string) $decoded_image)) {
                    Log::warning('Acceptance signature write failed for '.$sig_filename);

                    return redirect()->back()->with('error', trans('admin/users/message.accept_signature_write_failed'));
                }

                // No image data is present, kick them back.
                // This mostly only applies to users on super-duper crapola browsers *cough* IE *cough*
            } else {
                return redirect()->back()->with('error', trans('general.shitty_browser'));
            }
        }

        // Convert PDF logo to base64 for TCPDF. Reading via the disk (rather
        // than file_get_contents on a local path) keeps this working when
        // uploads live on s3 or another non-local filesystem.
        $encoded_logo = null;
        if (($settings->acceptance_pdf_logo) && (Storage::disk('public')->exists($settings->acceptance_pdf_logo))) {
            $encoded_logo = base64_encode(Storage::disk('public')->get($settings->acceptance_pdf_logo));
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

            $pdf_filename = 'accepted-'.$username_slug.$asset_tag_slug.'-'.date('Y-m-d-h-i-s').'.pdf';

            // Generate the PDF content
            $pdf_content = $acceptance->generateAcceptancePdf($data, $acceptance);

            // Storage::put returns false on silent write failures on
            // non-throwing filesystem drivers. Ignoring the return let
            // acceptance finalization proceed while the acceptance PDF was
            // absent from disk, producing an "accepted" record whose
            // evidence file did not exist. Refuse to advance when the
            // write did not land. Reported by Christopher Finks
            // (christopherfi-dev) on 2026-08-02.
            if (! Storage::put(FileStorage::EulaPdfs->privateStorageKey().$pdf_filename, $pdf_content)) {
                Log::warning('Acceptance PDF write failed for '.$pdf_filename);

                return redirect()->back()->with('error', trans('admin/users/message.accept_pdf_write_failed'));
            }

            // Log the acceptance. accept() runs a compare-and-set UPDATE
            // under the hood, so if another request already finalized this
            // row, we bail here before any notification, event, or follow-up
            // side effect fires. The PDF file we just wrote is left behind
            // (same disposition as the signature file above) rather than
            // rolled back, since cleaning up on the loser side would race
            // the winner reading the same file. Not ideal, but the alternative
            // is a more complex transactional file store that can roll back on failure.
            if (! $acceptance->accept($sig_filename, $item->getEula(), $pdf_filename, $request->input('note'))) {
                return redirect()->route('account.accept')->with('error', trans('admin/users/message.error.asset_already_accepted'));
            }

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

            // decline() does its own compare-and-set and loops the per-unit
            // declinedCheckout side effects internally for qty > 1. If another
            // request already finalized this row, bail before notifications
            // and events fire.
            if (! $acceptance->decline($sig_filename, $request->input('note'))) {
                return redirect()->route('account.accept')->with('error', trans('admin/users/message.error.asset_already_accepted'));
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
            ->when($acceptance->created_at, fn ($q) => $q->where('created_at', '<=', $acceptance->created_at->copy()->addMinutes(5)))
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

    private function flattenSignatureBackgroundToWhite(string $signatureBinary): ?string
    {
        // Fail-closed on every unexpected branch rather than returning
        // the original bytes. Each branch below returns null so the
        // the submission gets rejected instead of storing an
        // unverified blob under a .png filename.
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagecreatetruecolor')) {
            return null;
        }

        $source = @imagecreatefromstring($signatureBinary);

        if ($source === false) {
            return null;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $flattened = imagecreatetruecolor($width, $height);

        if ($flattened === false) {
            return null;
        }

        $white = imagecolorallocate($flattened, 255, 255, 255);
        imagefilledrectangle($flattened, 0, 0, $width, $height, $white);
        imagecopy($flattened, $source, 0, 0, 0, 0, $width, $height);

        ob_start();
        $encoded = @imagepng($flattened);
        $output = ob_get_clean();

        // Fail-closed if either imagepng() reported failure or the
        // output buffer did not produce a string. Previously fell back
        // to the original bytes, which bypassed the canonicalization
        // guarantee the caller is relying on.
        if ($encoded === false || !is_string($output) || $output === '') {
            return null;
        }

        return $output;
    }
}
