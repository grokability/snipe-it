<?php

namespace App\Mail;

use App\Models\Asset;
use App\Models\LicenseSeat;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckoutLicenseMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(LicenseSeat $licenseSeat, $checkedOutTo, User $checkedOutBy, $acceptance, $note)
    {
        $this->item = $licenseSeat;
        $this->admin = $checkedOutBy;
        $this->note = $note;
        $this->acceptance = $acceptance;
        $this->settings = Setting::getSettings();
        $this->target = $checkedOutTo;

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
            subject: trans('mail.Confirm_license_delivery'),
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
}
