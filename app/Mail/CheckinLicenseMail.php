<?php

namespace App\Mail;

use App\Models\LicenseSeat;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckinLicenseMail extends BaseMailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(LicenseSeat $licenseSeat, $checkedOutTo, User $checkedInBy, $note)
    {
        $this->target = $checkedOutTo;
        $this->item = $licenseSeat;
        $this->admin = $checkedInBy;
        $this->note = $note;
        $this->settings = Setting::getSettings();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $from = new Address(config('mail.from.address'), config('mail.from.name'));

        return new Envelope(
            from: $from,
            subject: trans('mail.License_Checkin_Notification'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.markdown.checkin-license',
            with:   [
                'license_seat'  => $this->item,
                'license'       => $this->item->license,
                'admin'         => $this->admin,
                'note'          => $this->note,
                'target'        => $this->target,
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
