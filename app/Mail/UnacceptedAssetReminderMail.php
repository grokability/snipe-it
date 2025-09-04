<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UnacceptedAssetReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct($checkout_info, $count)
    {

        $this->count = $count;
        $this->target = $checkout_info?->assignedTo;
        $this->acceptance = $checkout_info;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $from = new Address(config('mail.from.address'), config('mail.from.name'));

        return new Envelope(
            from: $from,
            subject: trans('mail.unaccepted_asset_reminder'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $accept_url = route('account.accept');

        return new Content(
            markdown: 'notifications.markdown.asset-reminder',
            with: [
                'count'        => $this->count,
                'assigned_to'  => $this->target?->present()->fullName,
                'link'         => route('account.accept'),
                'accept_url'   => $accept_url,
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
