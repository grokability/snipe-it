<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ReacceptanceRequestMail extends BaseMailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $assignedTo,
        public Collection $acceptances,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $from = new Address(config('mail.from.address'), config('mail.from.name'));

        return new Envelope(
            from: $from,
            subject: trans_choice('mail.reacceptance_required', $this->acceptances->count()),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // A single new acceptance links straight to its signature page; multiple
        // link to the acceptance index where the user can work through them.
        $link = $this->acceptances->count() === 1
            ? route('account.accept.item', $this->acceptances->first())
            : route('account.accept');

        return new Content(
            markdown: 'notifications.markdown.reacceptance-request',
            with: [
                'count' => $this->acceptances->count(),
                'assigned_to' => $this->assignedTo->present()->fullName,
                'link' => $link,
            ]
        );
    }
}
