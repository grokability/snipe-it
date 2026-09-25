<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AcceptanceReRequestMail extends BaseMailable
{
    use Queueable, SerializesModels;

    /**
     * How many items the message names before it stops listing and counts the rest.
     */
    private const ITEM_LIST_LIMIT = 10;

    /**
     * @param  array<int, array{name: string, type: 'asset'|'license'|'accessory'|'consumable'|'component', qty: int|null}>  $items
     */
    public function __construct(
        public readonly User $holder,
        public readonly array $items,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: trans('mail.acceptance_re_request'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'notifications.markdown.acceptance-re-request',
            with: [
                'assigned_to' => $this->holder->present()->fullName,
                'count' => count($this->items),
                'shown_items' => $this->shownItems(),
                'remaining' => max(count($this->items) - self::ITEM_LIST_LIMIT, 0),
                'accept_url' => route('account.accept'),
            ],
        );
    }

    /**
     * The items the message names, each labelled in words a holder uses.
     *
     * @return array<int, array{name: string, type: string, qty: int|null}>
     */
    private function shownItems(): array
    {
        return collect($this->items)
            ->take(self::ITEM_LIST_LIMIT)
            ->map(fn (array $item) => [
                'name' => $item['name'],
                'type' => $this->typeLabel($item['type']),
                'qty' => $item['qty'],
            ])
            ->all();
    }

    /**
     * What a holder calls the kind of thing they hold.
     *
     * `class_basename()` is what the operator's report table prints, but "LicenseSeat"
     * is not a word anyone outside this codebase uses. This runs while the mailable is
     * being rendered, so it is already inside the holder's locale.
     *
     * @param  'asset'|'license'|'accessory'|'consumable'|'component'  $type
     */
    private function typeLabel(string $type): string
    {
        return match ($type) {
            'asset' => trans('general.asset'),
            'license' => trans('general.license'),
            'accessory' => trans('general.accessory'),
            'consumable' => trans('general.consumable'),
            'component' => trans('general.component'),
        };
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
