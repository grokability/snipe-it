<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExpiringAssetsMail extends BaseMailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct($params, $threshold)
    {
        $this->assets = $params;
        $this->threshold = $threshold;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $from = new Address(config('mail.from.address'), config('mail.from.name'));

        return new Envelope(
            from: $from,
            subject: trans('mail.Expiring_Assets_Report'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'notifications.markdown.report-expiring-assets',
            with: [
                'assets'  => $this->assets,
                'threshold'  => $this->threshold,
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
