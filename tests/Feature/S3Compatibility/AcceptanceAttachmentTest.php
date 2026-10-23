<?php

namespace Tests\Feature\S3Compatibility;

use App\Enums\FileStorage;
use App\Notifications\AcceptanceItemAcceptedToUserNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Mime\Email;
use Tests\TestCase;

/**
 * FR-1: the acceptance-copy mail must attach the bytes stored on the configured
 * private disk, whatever the disk is called, and must fail rather than send an
 * empty or missing attachment.
 */
class AcceptanceAttachmentTest extends TestCase
{
    private string $remoteRoot;

    private string $localCopy = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->remoteRoot = sys_get_temp_dir().'/s3compat-private-'.bin2hex(random_bytes(4));
        File::ensureDirectoryExists($this->remoteRoot);

        // A disk whose name is not `s3_private`, standing in for any remote private disk.
        config(['filesystems.disks.custom_private' => ['driver' => 'local', 'root' => $this->remoteRoot]]);
        config(['filesystems.default' => 'custom_private']);
        Storage::forgetDisk('custom_private');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->remoteRoot);
        if ($this->localCopy !== '' && file_exists($this->localCopy)) {
            unlink($this->localCopy);
        }

        parent::tearDown();
    }

    private function notification(string $file): AcceptanceItemAcceptedToUserNotification
    {
        return new AcceptanceItemAcceptedToUserNotification([
            'item_tag' => 'AT-1',
            'item_name' => 'Test',
            'item_model' => 'Model',
            'item_serial' => 'SN',
            'item_status' => 'Deployed',
            'accepted_date' => '27/08/2026 10:00',
            'assigned_to' => 'Test User',
            'company_name' => 'Test Co',
            'qty' => 1,
            'file' => $file,
        ]);
    }

    private function sendAndGetAttachments(AcceptanceItemAcceptedToUserNotification $notification): array
    {
        config(['mail.default' => 'array']);
        Mail::purge('array');

        $notifiable = new AnonymousNotifiable;
        $notifiable->route('mail', 'recipient@example.invalid');
        Notification::sendNow($notifiable, $notification);

        $sent = Mail::mailer('array')->getSymfonyTransport()->messages();
        $this->assertCount(1, $sent);
        $email = $sent[0]->getOriginalMessage();
        $this->assertInstanceOf(Email::class, $email);

        return $email->getAttachments();
    }

    #[Test]
    public function attachment_bytes_come_from_the_configured_private_disk(): void
    {
        $pdf = "%PDF-1.4\n".random_bytes(2048)."\n%%EOF";
        $file = 'accepted-'.bin2hex(random_bytes(4)).'.pdf';
        Storage::disk('custom_private')->put(FileStorage::EulaPdfs->privateStorageKey().$file, $pdf);

        $attachments = $this->sendAndGetAttachments($this->notification($file));

        $this->assertCount(1, $attachments);
        $this->assertSame(hash('sha256', $pdf), hash('sha256', $attachments[0]->getBody()));
        $this->assertSame('application/pdf', $attachments[0]->getMediaType().'/'.$attachments[0]->getMediaSubtype());
        $this->assertSame($file, $attachments[0]->getPreparedHeaders()->getHeaderParameter('Content-Disposition', 'filename'));
    }

    #[Test]
    public function missing_object_on_the_private_disk_fails_instead_of_sending_a_stale_local_file(): void
    {
        $file = 'missing-'.bin2hex(random_bytes(4)).'.pdf';

        // A file at the old local path must not be picked up: only the configured disk counts.
        $this->localCopy = storage_path(FileStorage::EulaPdfs->privateStorageKey().$file);
        File::ensureDirectoryExists(dirname($this->localCopy));
        file_put_contents($this->localCopy, 'STALE LOCAL COPY');

        $this->expectException(\Throwable::class);
        $this->sendAndGetAttachments($this->notification($file));
    }

    #[Test]
    public function local_default_disk_still_attaches_the_stored_pdf(): void
    {
        config(['filesystems.default' => 'local']);
        Storage::forgetDisk('local');

        $pdf = "%PDF-1.4\n".random_bytes(512)."\n%%EOF";
        $file = 'local-'.bin2hex(random_bytes(4)).'.pdf';
        $key = FileStorage::EulaPdfs->privateStorageKey().$file;
        Storage::disk('local')->put($key, $pdf);
        $this->localCopy = Storage::disk('local')->path($key);

        $attachments = $this->sendAndGetAttachments($this->notification($file));

        $this->assertCount(1, $attachments);
        $this->assertSame(hash('sha256', $pdf), hash('sha256', $attachments[0]->getBody()));
    }
}
