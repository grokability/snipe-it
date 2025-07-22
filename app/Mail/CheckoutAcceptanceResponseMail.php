<?php

namespace App\Mail;

use App\Models\CheckoutAcceptance;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckoutAcceptanceResponseMail extends Mailable
{
    use Queueable, SerializesModels;

    public CheckoutAcceptance $acceptance;
    public User $recipient;
    public bool $wasAccepted;

    /**
     * Create a new message instance.
     */
    public function __construct(CheckoutAcceptance $acceptance, User $recipient, bool $wasAccepted)
    {
        $this->acceptance = $acceptance;
        $this->recipient = $recipient;
        $this->wasAccepted = $wasAccepted;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->wasAccepted
            ? trans('mail.initiated_accepted')
            : trans('mail.initiated_declined');

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.markdown.checkout-acceptance-response',
            with: [
                'assignedTo' => $this->acceptance->assignedTo,
                'introduction' => $this->introduction(),
                'item' => $this->acceptance->checkoutable,
                'note' => $this->acceptance->note,
                'recipient' => $this->recipient,
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

    private function introduction(): string
    {
        return $this->wasAccepted
            ? trans('mail.following_accepted')
            : trans('mail.following_declined');
    }
}
