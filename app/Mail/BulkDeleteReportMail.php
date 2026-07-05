<?php

namespace App\Mail;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BulkDeleteReportMail extends BaseMailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $admin,
        public readonly bool $dryRun,
        public readonly array $companyNames,
        public readonly array $selectedTypes,
        public readonly string $deleteType,
        public readonly array $reportLines,
        public readonly Carbon $runAt,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->dryRun
            ? '[Dry Run] Bulk Check-in/Delete Report'
            : 'Bulk Check-in/Delete Report';

        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'notifications.markdown.report-bulk-delete',
            with: [
                'admin' => $this->admin,
                'dryRun' => $this->dryRun,
                'companyNames' => $this->companyNames,
                'selectedTypes' => $this->selectedTypes,
                'deleteType' => $this->deleteType,
                'reportLines' => $this->reportLines,
                'runAt' => $this->runAt,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
