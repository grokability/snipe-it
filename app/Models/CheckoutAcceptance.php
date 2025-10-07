<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use TCPDF;

class CheckoutAcceptance extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $casts = [
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
        'alert_on_response_id' => 'integer',
    ];

    /**
     * Get the mail recipient from the config
     *
     * @return mixed|string|null
     */
    public function routeNotificationForMail()
    {
        // At this point the endpoint is the same for everything.
        //  In the future this may want to be adapted for individual notifications.
        $recipients_string = explode(',', Setting::getSettings()->alert_email);
        $recipients = array_map('trim', $recipients_string);

        return array_filter($recipients);
    }
    public function getCheckoutableItemTypeAttribute(): string
    {
        $type = $this->checkoutable_type;

        return match ($type) {
            Asset::class       => trans('general.asset'),
            LicenseSeat::class => trans('general.license'),
            Accessory::class   => trans('general.accessory'),
            Component::class   => trans('general.component'),
            Consumable::class  => trans('general.consumable'),
            default            => class_basename($type),
        };
    }
    /**
     * The resource that was is out
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function checkoutable()
    {
        return $this->morphTo();
    }

    /**
     * The user that the checkoutable was checked out to
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Is this checkout acceptance pending?
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->accepted_at == null && $this->declined_at == null;
    }

    /**
     * Was the checkoutable checked out to this user?
     *
     * @param  User $user
     * @return bool
     */
    public function isCheckedOutTo(User $user)
    {
        return $this->assignedTo?->is($user);
    }

    /**
     * Add a record to the checkout_acceptance table ONLY.
     * Do not add stuff here that doesn't have a corresponding column in the
     * checkout_acceptances table or you'll get an error.
     *
     * @param string $signature_filename
     */
    public function accept($signature_filename, $eula = null, $filename = null, $note = null)
    {
        $this->accepted_at = now();
        $this->signature_filename = $signature_filename;
        $this->stored_eula = $eula;
        $this->stored_eula_file = $filename;
        $this->note = $note;
        $this->save();

        /**
         * Update state for the checked out item
         */
        $this->checkoutable->acceptedCheckout($this->assignedTo, $signature_filename, $filename);
    }

    /**
     * Decline the checkout acceptance
     *
     * @param string $signature_filename
     */
    public function decline($signature_filename, $note = null)
    {
        $this->declined_at = now();
        $this->note = $note;
        $this->signature_filename = $signature_filename;
        $this->save();

        /**
         * Update state for the checked out item
         */
        $this->checkoutable->declinedCheckout($this->assignedTo, $signature_filename);
    }

    /**
     * Filter checkout acceptences by the user
     *
     * @param  User  $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser(Builder $query, User $user)
    {
        return $query->where('assigned_to_id', $user->id);
    }

    /**
     * Filter to only get pending acceptances
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending(Builder $query)
    {
        return $query->whereNull('accepted_at')->whereNull('declined_at');
    }

    public function scopeDeclined(Builder $query)
    {
        return $query->whereNull('accepted_at')->whereNotNull('declined_at');
    }

    protected function displayCheckoutableType(): Attribute
    {
        return Attribute:: make(
            get: fn(mixed $value) => strtolower(str_replace('App\Models\\', '', $this->checkoutable_type)),
        );
    }

    public function generateAcceptancePdf($data, $pdf_filename) {

        // set some language dependent data:
        $lg = Array();
        $lg['a_meta_charset'] = 'UTF-8';
        $lg['w_page'] = 'page';

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setRTL(false);
        $pdf->setLanguageArray($lg);
        $pdf->SetFontSubsetting(true);
        $pdf->SetCreator('Snipe-IT Asset Management System');
        $pdf->SetAuthor($data['assigned_to']);
        $pdf->SetTitle('Asset Acceptance: '.$data['item_tag']);
        $pdf->SetSubject('Asset Acceptance: '.$data['item_tag']);
        $pdf->SetKeywords('Snipe-IT, assets, acceptance, eula, tos');
        $pdf->SetFont('dejavusans', '', 8, '', true);
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);

        $pdf->AddPage();
        if ($data['logo'] != null) {
            $pdf->writeHTML('<img src="'.$data['logo'].'">', true, 0, true, 0, '');
        } else {
            $pdf->writeHTML('<h3>'.$data['site_name'].'</h3><br /><br />', true, 0, true, 0, 'C');
        }

        $pdf->Ln();
        $pdf->writeHTML(trans('general.date') . ': ' . Helper::getFormattedDateObject(now(), 'datetime', false), true, 0, true, 0, '');

        if ($data['company_name'] != null) {
            $pdf->writeHTML(trans('general.company') . ': ' . e($data['company_name']), true, 0, true, 0, '');
        }
        if ($data['item_tag'] != null) {
            $pdf->writeHTML(trans('general.asset_tag') . ': ' . e($data['item_tag']), true, 0, true, 0, '');
        }
        if ($data['item_name'] != null) {
            $pdf->writeHTML(trans('general.name') . ': ' . e($data['item_name']), true, 0, true, 0, '');
        }
        if ($data['item_model'] != null) {
            $pdf->writeHTML(trans('general.asset_model') . ': ' . e($data['item_model']), true, 0, true, 0, '');
        }
        if ($data['item_serial'] != null) {
            $pdf->writeHTML(trans('admin/hardware/form.serial').': '.e($data['item_serial']), true, 0, true, 0, '');
        }
        if (($data['qty'] != null) && ($data['qty'] > 1)) {
            $pdf->writeHTML(trans('general.qty').': '.e($data['qty']), true, 0, true, 0, '');
        }
        $pdf->writeHTML(trans('general.assignee').': '.e($data['assigned_to']), true, 0, true, 0, '');
        $pdf->Ln();
        $pdf->writeHTML('<hr>', true, 0, true, 0, '');


        // Break the EULA into lines based on newlines, and check each line for RTL or CJK characters
        $eula_lines = preg_split("/\r\n|\n|\r/", $data['eula']);

        foreach ($eula_lines as $eula_line) {
            Helper::hasRtl($eula_line) ? $pdf->setRTL(true) : $pdf->setRTL(false);
            Helper::isCjk($eula_line) ? $pdf->SetFont('cid0cs', '', 9) : $pdf->SetFont('dejavusans', '', 8, '', true);

            $pdf->writeHTML(Helper::parseEscapedMarkedown($eula_line), true, 0, true, 0, '');
        }
        $pdf->Ln();
        $pdf->Ln();
        $pdf->setRTL(false);
        $pdf->Ln();

        if ($data['signature'] != null) {
            $pdf->writeHTML('<img src="'.$data['signature'].'">', true, 0, true, 0, '');
            $pdf->writeHTML('<hr>', true, 0, true, 0, '');
            $pdf->writeHTML(e($data['assigned_to']), true, 0, true, 0, 'C');
            $pdf->Ln();
        }

        if ($data['note'] != null) {
            Helper::isCjk($data['note']) ? $pdf->SetFont('cid0cs', '', 9) : $pdf->SetFont('dejavusans', '', 8, '', true);
            $pdf->writeHTML(trans('general.notes') . ': ' . e($data['note']), true, 0, true, 0, '');
            $pdf->Ln();
        }


        $pdf->writeHTML(trans('general.assigned_date').': '.e($data['check_out_date']), true, 0, true, 0, '');
        $pdf->writeHTML(trans('general.accepted_date').': '.e($data['accepted_date']), true, 0, true, 0, '');

        return $pdf->Output($pdf_filename, 'S');


    }
}
