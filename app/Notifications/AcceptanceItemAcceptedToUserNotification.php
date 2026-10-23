<?php

namespace App\Notifications;

use App\Enums\FileStorage;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Attachment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Mime\Email;

class AcceptanceItemAcceptedToUserNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $item_tag;

    public $item_name;

    public $item_model;

    public $item_serial;

    public $item_status;

    public $accepted_date;

    public $assigned_to;

    public $note;

    public $company_name;

    public $file;

    public $qty;

    public $custom_fields;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        $this->item_tag = $params['item_tag'];
        $this->item_name = $params['item_name'];
        $this->item_model = $params['item_model'];
        $this->item_serial = $params['item_serial'];
        $this->item_status = $params['item_status'];
        // The controller already runs the accepted_date through
        // Helper::getFormattedDateObject, so it arrives pre-formatted
        // in the caller's locale. Don't invoke it here.
        $this->accepted_date = $params['accepted_date'];
        $this->assigned_to = $params['assigned_to'];
        $this->note = $params['note'] ?? null;
        $this->company_name = $params['company_name'];
        $this->file = $params['file'] ?? null;
        $this->qty = $params['qty'] ?? null;
        $this->custom_fields = $params['custom_fields'] ?? [];
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {

        $notifyBy = ['mail'];

        return $notifyBy;

    }

    /**
     * Get the mail representation of the notification.
     *
     * @return MailMessage
     */
    public function toMail()
    {
        // Read the PDF from the configured private disk when the mail is built, so the same code works for
        // local storage and for S3 (or any other disk), whatever the disk is called. A missing PDF fails
        // the notification rather than sending a mail with an empty or stale attachment.
        $disk = config('filesystems.default');
        $pdf_key = FileStorage::EulaPdfs->privateStorageKey().$this->file;

        if (! Storage::disk($disk)->exists($pdf_key)) {
            throw new RuntimeException('Acceptance PDF "'.$pdf_key.'" was not found on the "'.$disk.'" disk; the acceptance copy was not sent.');
        }

        $message = (new MailMessage)->markdown('notifications.markdown.asset-acceptance',
            [
                'item_tag' => $this->item_tag,
                'item_name' => $this->item_name,
                'item_model' => $this->item_model,
                'item_serial' => $this->item_serial,
                'item_status' => $this->item_status,
                'note' => $this->note,
                'accepted_date' => $this->accepted_date,
                'assigned_to' => $this->assigned_to,
                'company_name' => $this->company_name,
                'qty' => $this->qty,
                'custom_fields' => $this->custom_fields,
                'intro_text' => trans_choice('mail.acceptance_asset_accepted_to_user', $this->qty, ['qty' => $this->qty, 'site_name' => Setting::getSettings()->site_name]),
            ])
            ->attach(Attachment::fromStorageDisk($disk, $pdf_key)->as($this->file)->withMime('application/pdf'))
            ->subject('✅ '.trans_choice('mail.acceptance_asset_accepted_to_user', $this->qty, ['qty' => $this->qty, 'site_name' => Setting::getSettings()->site_name]))
            ->withSymfonyMessage(function (Email $message) {
                $message->getHeaders()->addTextHeader(
                    'X-System-Sender', 'Snipe-IT'
                );
            });

        return $message;
    }
}
