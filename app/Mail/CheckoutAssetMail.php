<?php

namespace App\Mail;

use App\Helpers\Helper;
use App\Models\Asset;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\SerializesModels;

class CheckoutAssetMail extends Mailable
{
    use Queueable, SerializesModels;

    private bool $firstTimeSending;

    /**
     * Create a new message instance.
     * @throws \Exception
     */
    public function __construct(Asset $asset, $checkedOutTo, User $checkedOutBy, $acceptance, $note, bool $firstTimeSending = true)
    {
        $this->item = $asset;
        $this->admin = $checkedOutBy;
        $this->note = $note;
        $this->acceptance = $acceptance;

        $this->settings = Setting::getSettings();
        $this->target = $checkedOutTo;

        $this->last_checkout = '';
        $this->expected_checkin = '';

        $this->firstTimeSending = $firstTimeSending;

        if ($this->item->last_checkout) {
            $this->last_checkout = Helper::getFormattedDateObject($this->item->last_checkout, 'date',
                false);
        }

        if ($this->item->expected_checkin) {
            $this->expected_checkin = Helper::getFormattedDateObject($this->item->expected_checkin, 'date',
                false);
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
            subject: $this->getSubject(),
        );
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return Content
     */
    public function content(): Content
    {
        $this->item->load('assetstatus');
        $eula = method_exists($this->item, 'getEula') ? $this->item->getEula() : '';
        $req_accept = $this->requiresAcceptance();
        $fields = [];
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

        // Check if the item has custom fields associated with it
        if (($this->item->model) && ($this->item->model->fieldset)) {
            $fields = $this->item->model->fieldset->fields;
        }

        $accept_url = is_null($this->acceptance) ? null : route('account.accept.item', $this->acceptance);

        return new Content(
            markdown: 'mail.markdown.checkout-asset',
            with:  [
                'item'          => $this->item,
                'admin'         => $this->admin,
                'status'        => $this->item->assetstatus?->name,
                'note'          => $this->note,
                'target'        => $name,
                'fields'        => $fields,
                'eula'          => $eula,
                'req_accept'    => $req_accept,
                'accept_url'    => $accept_url,
                'last_checkout' => $this->last_checkout,
                'expected_checkin'  => $this->expected_checkin,
                'introduction_line' => $this->introductionLine(),
            ],
        );
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

    private function getSubject(): string
    {
        if ($this->firstTimeSending) {
            return trans('mail.Asset_Checkout_Notification', ['tag' => $this->item->asset_tag]);
        }

        return trans('mail.unaccepted_asset_reminder');
    }

    private function introductionLine(): string
    {
        if ($this->firstTimeSending && $this->target instanceof Location) {
            return trans_choice('mail.new_item_checked_location', 1, ['location' => $this->target->name]);
        }

        if ($this->firstTimeSending && $this->requiresAcceptance()) {
            return trans_choice('mail.new_item_checked_with_acceptance', 1);
        }

        if ($this->firstTimeSending && !$this->requiresAcceptance()) {
            return trans_choice('mail.new_item_checked', 1);
        }

        if (!$this->firstTimeSending && $this->requiresAcceptance()) {
            return trans('mail.recent_item_checked');
        }

        // we shouldn't get here but let's send a default message just in case
        return trans('new_item_checked');
    }

    private function requiresAcceptance(): int|bool
    {
        return method_exists($this->item, 'requireAcceptance') ? $this->item->requireAcceptance() : 0;
    }
}
