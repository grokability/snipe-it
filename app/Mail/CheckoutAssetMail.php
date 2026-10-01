<?php

namespace App\Mail;

use App\Helpers\Helper;
use App\Models\Asset;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckoutAssetMail extends BaseMailable
{
    use Queueable, SerializesModels;

    private bool $firstTimeSending;

    private Asset $item;

    private ?User $admin;

    private mixed $note;

    private mixed $acceptance;

    private ?Setting $settings;

    private mixed $target;

    private string|array|null $last_checkout;

    private string|array|null $expected_checkin;

    /**
     * Create a new message instance.
     *
     * @throws \Exception
     */
    public function __construct(Asset $asset, $checkedOutTo, ?User $checkedOutBy, $acceptance, $note, bool $firstTimeSending = true)
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
            $this->expected_checkin = Helper::getFormattedDateObject($this->item->expected_checkin, 'datetime',
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
     */
    public function content(): Content
    {
        $this->item->load('status');
        $eula = $this->item->getEula();
        $req_accept = $this->requiresAcceptance();
        $fields = [];
        $customFields = [];
        $name = null;

        if ($this->target instanceof User) {
            $name = $this->target->display_name;
        } elseif ($this->target instanceof Asset) {
            $name = $this->target->assignedto?->display_name;
        } elseif ($this->target instanceof Location) {
            $name = $this->target->manager?->name;
        }

        // Check if the item has custom fields associated with it
        if (($this->item->model) && ($this->item->model->fieldset)) {
            $fields = $this->item->model->fieldset->fields;

            foreach ($fields as $field) {
                if (! $field->show_in_email || $field->field_encrypted == '1') {
                    continue;
                }

                $value = $this->item->{$field->db_column_name()};

                if (! is_null($value) && $value !== '') {
                    $customFields[] = [
                        'label' => $field->name,
                        'value' => $value,
                    ];
                }
            }
        }

        $accept_url = is_null($this->acceptance) ? null : route('account.accept.item', $this->acceptance);

        return new Content(
            markdown: 'mail.markdown.checkout-asset',
            with: [
                'item' => $this->item,
                'admin' => $this->admin,
                'status' => $this->item->status?->name,
                'note' => $this->note,
                'target' => $name,
                'fields' => $fields,
                'custom_fields' => $customFields,
                'eula' => $eula,
                'req_accept' => $req_accept,
                'accept_url' => $accept_url,
                'last_checkout' => $this->last_checkout,
                'expected_checkin' => $this->expected_checkin,
                'location' => $this->getLocation(),
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
        if (is_null($this->acceptance)) {
            return trans_choice('mail.new_item_checked', 1);
        }
        if ($this->firstTimeSending && $this->target instanceof Location) {
            return trans_choice('mail.new_item_checked_location', 1, ['location' => $this->target->name]);
        }

        if ($this->firstTimeSending && $this->requiresAcceptance()) {
            return trans_choice('mail.new_item_checked_with_acceptance', 1);
        }

        if ($this->firstTimeSending && ! $this->requiresAcceptance()) {
            return trans_choice('mail.new_item_checked', 1);
        }

        if (! $this->firstTimeSending && $this->requiresAcceptance()) {
            return trans('mail.recent_item_checked');
        }

        // we shouldn't get here but let's send a default message just in case
        return trans('mail.new_item_checked');
    }

    private function requiresAcceptance(): bool
    {
        return $this->item->requireAcceptance();
    }

    private function getLocation(): ?string
    {
        if ($this->target instanceof Asset || $this->target instanceof User) {
            return $this->target->location?->name;
        }

        if ($this->target instanceof Location) {
            return $this->target->name;
        }

        return null;
    }
}
