<?php

namespace App\Mail;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckoutAccessoryMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(Accessory $accessory, $checkedOutTo, User $checkedOutBy, $acceptance, $note)
    {
        $this->item = $accessory;
        $this->admin = $checkedOutBy;
        $this->note = $note;
        $this->checkout_qty = $accessory->checkout_qty;
        $this->target = $checkedOutTo;
        $this->acceptance = $acceptance;
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
            subject: trans_choice('mail.Accessory_Checkout_Notification', $this->checkout_qty),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        Log::debug($this->item->getImageUrl());
        $eula = $this->item->getEula();
        $req_accept = $this->item->requireAcceptance();
        $accept_url = is_null($this->acceptance) ? null : route('account.accept.item', $this->acceptance);
        $name = null;

        if($this->target instanceof User){
            $name = $this->target->display_name;
        }
        else if($this->target instanceof Asset){
            $name  = $this->target->assignedto?->display_name;
        }
        else if($this->target instanceof Location){
            $name  = $this->target->manager->name;
        }

        return new Content(
            markdown: 'mail.markdown.checkout-accessory',
            with:   [
                'item'          => $this->item,
                'admin'         => $this->admin,
                'note'          => $this->note,
                'target'        => $name,
                'eula'          => $eula,
                'req_accept'    => $req_accept,
                'accept_url'    => $accept_url,
                'checkout_qty'  => $this->checkout_qty,
                'introduction_line' => $this->introductionLine(),
            ],
        );
    }

    private function introductionLine(): string
    {
        if ($this->target instanceof Location) {
            return trans_choice('mail.new_item_checked_location', $this->checkout_qty, ['location' => $this->target->name]);
        }

        if ($this->requiresAcceptance()) {
            return trans_choice('mail.new_item_checked_with_acceptance', $this->checkout_qty);
        }

        if (!$this->requiresAcceptance()) {
            return trans_choice('mail.new_item_checked', $this->checkout_qty);
        }

        // we shouldn't get here but let's send a default message just in case
        return trans('new_item_checked');
    }
    private function requiresAcceptance(): int|bool
    {
        return method_exists($this->item, 'requireAcceptance') ? $this->item->requireAcceptance() : 0;
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
