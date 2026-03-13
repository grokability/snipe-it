<?php

namespace App\Mail;

use App\Models\Asset;
use App\Models\LicenseSeat;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckoutLicenseMail extends BaseMailable
{
    use Queueable, SerializesModels;

    private bool $firstTimeSending;

    /**
     * Create a new message instance.
     */
    public function __construct(LicenseSeat $licenseSeat, $checkedOutTo, User $checkedOutBy, $acceptance, $note, bool $firstTimeSending = true)
    {
        $this->item = $licenseSeat;
        $this->admin = $checkedOutBy;
        $this->note = $note;
        $this->acceptance = $acceptance;
        $this->settings = Setting::getSettings();
        $this->target = $checkedOutTo;
        $this->firstTimeSending = $firstTimeSending;

        if($this->target instanceof User){
            $this->target = $this->target->display_name;
        }
        elseif($this->target instanceof Asset){
            $this->target = $this->target->display_name;
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $from = new Address(config('mail.from.address'), config('mail.from.name'));

        return new Envelope(
            from: $from,
            subject: $this->getSubject(),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $eula = method_exists($this->item, 'getEula') ? $this->item->getEula() : '';
        $req_accept = method_exists($this->item, 'requireAcceptance') ? $this->item->requireAcceptance() : 0;

        $accept_url = is_null($this->acceptance) ? null : route('account.accept.item', $this->acceptance);
        return new Content(
            markdown: 'mail.markdown.checkout-license',
            with:   [
                'license_seat'  => $this->item,
                'license'       => $this->item->license,
                'admin'         => $this->admin,
                'note'          => $this->note,
                'target'        => $this->target,
                'eula'          => $eula,
                'req_accept'    => $req_accept,
                'accept_url'    => $accept_url,
                'introduction_line' => $this->introductionLine(),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    private function getSubject(): string
    {
        if ($this->firstTimeSending) {
            return trans('mail.Confirm_license_delivery');
        }

        return trans('mail.unaccepted_asset_reminder');
    }

    private function introductionLine(): string
    {
        if ($this->firstTimeSending && $this->requiresAcceptance()) {
            return trans_choice('mail.new_item_checked_with_acceptance', 1);
        }

        if ($this->firstTimeSending && !$this->requiresAcceptance()) {
            return trans_choice('mail.new_item_checked', 1);
        }

        if (!$this->firstTimeSending && $this->requiresAcceptance()) {
            return trans('mail.recent_item_checked');
        }

        // we shouldn't get here but let's send a default message just in case
        return trans('new_item_checked');
    }

    private function requiresAcceptance(): int|bool
    {
        return method_exists($this->item, 'requireAcceptance') ? $this->item->requireAcceptance() : 0;
    }
}
