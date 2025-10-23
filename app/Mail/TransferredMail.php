<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Mail\Mailables\Envelope;

class TransferredMail extends Mailable
{
    use Queueable, SerializesModels;
    public bool $require_acceptance;

    public function __construct(
        public Collection $items,
        public Model $target,
        public User $admin,
        public Model $acceptance,
        public string $transferred_at,
        public string $expected_checkin,
        public string $note,

    ) {
        $this->require_acceptance = $this->requireAcceptance();
    }

    public function envelope() : Envelope
    {
        $from = new Address(config('mail.from.address'), config('mail.from.name'));

        return new Envelope(
            from: $from,
            subject: $this->getSubject(),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.markdown.transfer-items',
            with: [
                'introduction' => $this->getIntroduction(),
                'requires_acceptance' => $this->requireAcceptance(),
                'acceptance_url' => $this->acceptanceUrl(),
                'eula' => $this->getEula(),
            ]
        );
    }
    public function attachments(): array
    {
        return [];
    }

    private function getSubject(): string
    {
        return trans('mail.Asset_Transferred_Notification', $this->items->count());
    }

    private function getIntroduction(): string
    {
        if ($this->items->count() > 1) {
            // @todo: translate
            return 'Assets have been checked out to you.';
        }

        // @todo: translate
        return 'An asset has been checked out to you.';
    }

    private function requiresAcceptance(): bool
    {
        return (bool) $this->assets->reduce(
            fn($count, $asset) => $count + $asset->requireAcceptance()
        );
    }

    private function acceptanceUrl()
    {
        if ($this->assets->count() > 1) {
            return route('account.accept');
        }

        return route('account.accept.item', $this->assets->first());
    }

    private function getEula()
    {
        // if assets do not have the same category then return early...
        $categories = $this->assets->pluck('model.category.id')->unique();

        if ($categories->count() > 1) {
            return;
        }

        // if assets do have the same category then return the shared EULA
        if ($categories->count() === 1) {
            return $this->assets->first()->getEula();
        }

        // @todo: if the categories use the default eula then return that
    }
}