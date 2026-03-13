<?php

namespace App\Mail;

use App\Models\Accessory;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckinAccessoryMail extends BaseMailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(Accessory $accessory, $checkedOutTo, User $checkedInby, $note)
    {
        $this->item = $accessory;
        $this->target = $checkedOutTo;
        $this->admin = $checkedInby;
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
            subject: trans('mail.Accessory_Checkin_Notification'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.markdown.checkin-accessory',
            with:   [
                'item'          => $this->item,
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
