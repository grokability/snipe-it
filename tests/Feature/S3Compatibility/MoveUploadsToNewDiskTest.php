<?php

namespace Tests\Feature\S3Compatibility;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\S3Compatibility\FakeRemoteAdapter;
use Tests\Support\S3Compatibility\UsesFakeRemoteDisks;
use Tests\TestCase;

/**
 * FR-5 / FR-6: snipeit:move-uploads writes to the keys the application reads, includes
 * subdirectories and extension-less files, and deletes local sources only after every file has been
 * written and read back correctly and every source is confirmed unchanged.
 */
class MoveUploadsToNewDiskTest extends TestCase
{
    use UsesFakeRemoteDisks;

    private const OVERWRITE_LABEL = 'Overwrite destination files that already exist? Choose "No" to skip them and keep whatever is already on the destination.';

    private const DELETE_LABEL = 'Also delete the local source files? This cannot be undone.';

    private string $publicRoot;

    private string $privateRoot;

    /** @var array<int, string> */
    private array $created = [];

    private string $tag;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tag = 's3c'.bin2hex(random_bytes(3));
        $this->publicRoot = $this->fakeRemoteDisk('public');
        $this->privateRoot = $this->fakeRemoteDisk('custom_private');
        config(['filesystems.default' => 'custom_private']);
        Storage::forgetDisk('custom_private');
    }

    protected function tearDown(): void
    {
        foreach ($this->created as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
        foreach (['public/uploads/avatars', 'storage/private_uploads/eula-pdfs', 'storage/private_uploads/imports/sub', 'storage/private_uploads/imports'] as $dir) {
            $full = base_path($dir);
            if (is_dir($full) && count(glob($full.'/*') ?: []) === 0 && ! file_exists($full.'/.gitkeep')) {
                @rmdir($full);
            }
        }
        $this->cleanUpFakeRemoteDisks();

        parent::tearDown();
    }

    private function source(string $relative, string $contents): string
    {
        $path = base_path($relative);
        File::ensureDirectoryExists(dirname($path));
        file_put_contents($path, $contents);
        $this->created[] = $path;

        return $path;
    }

    private function move(array $options = ['--overwrite' => true, '--delete-local' => true])
    {
        return $this->artisan('snipeit:move-uploads', $options);
    }

    #[Test]
    public function files_land_on_the_keys_the_application_reads(): void
    {
        $pdf = $this->source('storage/private_uploads/eula-pdfs/'.$this->tag.'.pdf', 'PDF-BYTES');
        $csv = $this->source('storage/private_uploads/imports/sub/'.$this->tag.'.csv', 'a,b');
        $legacy = $this->source('storage/private_uploads/imports/'.$this->tag.'-legacy', 'no extension');
        $png = $this->source('public/uploads/avatars/'.$this->tag.'.png', 'PNG-BYTES');
        $this->source('storage/private_uploads/eula-pdfs/.s3c-hidden', '');

        $this->move(['--overwrite' => true])->expectsConfirmation(self::DELETE_LABEL, 'no')->assertExitCode(0);

        $this->assertSame('PDF-BYTES', file_get_contents($this->privateRoot.'/private_uploads/eula-pdfs/'.$this->tag.'.pdf'));
        $this->assertSame('a,b', file_get_contents($this->privateRoot.'/private_uploads/imports/sub/'.$this->tag.'.csv'));
        $this->assertSame('no extension', file_get_contents($this->privateRoot.'/private_uploads/imports/'.$this->tag.'-legacy'));
        $this->assertSame('PNG-BYTES', file_get_contents($this->publicRoot.'/avatars/'.$this->tag.'.png'));
        $this->assertFileDoesNotExist($this->privateRoot.'/private_uploads/eula-pdfs/.s3c-hidden');
        foreach ([$pdf, $csv, $legacy, $png] as $source) {
            $this->assertFileExists($source, 'without --delete-local nothing is deleted');
        }
    }

    #[Test]
    public function a_remote_public_disk_alone_is_enough_to_run(): void
    {
        config(['filesystems.default' => 'local']);
        Storage::forgetDisk('local');

        $png = $this->source('public/uploads/avatars/'.$this->tag.'.png', 'PNG-BYTES');
        $pdf = $this->source('storage/private_uploads/eula-pdfs/'.$this->tag.'.pdf', 'PDF-BYTES');

        $this->move(['--overwrite' => true, '--delete-local' => true])->assertExitCode(0);

        $this->assertSame('PNG-BYTES', file_get_contents($this->publicRoot.'/avatars/'.$this->tag.'.png'));
        $this->assertFileDoesNotExist($png, 'verified public source is removed');
        $this->assertFileExists($pdf, 'private files stay put when the private disk is local');
    }

    #[Test]
    public function two_local_disks_are_still_refused(): void
    {
        config(['filesystems.default' => 'local']);
        config(['filesystems.disks.public' => ['driver' => 'local', 'root' => sys_get_temp_dir()]]);
        Storage::forgetDisk('local');
        Storage::forgetDisk('public');

        $this->move()->assertExitCode(1);
    }

    #[Test]
    public function one_failed_write_keeps_every_source_and_fails_the_command(): void
    {
        $a = $this->source('storage/private_uploads/eula-pdfs/'.$this->tag.'-a.pdf', 'AAA');
        $b = $this->source('storage/private_uploads/eula-pdfs/'.$this->tag.'-b.pdf', 'BBB');
        $png = $this->source('public/uploads/avatars/'.$this->tag.'.png', 'PNG');
        FakeRemoteAdapter::$failWrites = ['private_uploads/eula-pdfs/'.$this->tag.'-b.pdf'];

        $this->move()->assertExitCode(1);

        foreach ([$a, $b, $png] as $source) {
            $this->assertFileExists($source, 'no source may be deleted after a failure');
        }
    }

    #[Test]
    public function a_destination_that_reads_back_differently_keeps_every_source(): void
    {
        $a = $this->source('storage/private_uploads/eula-pdfs/'.$this->tag.'-a.pdf', 'AAA');
        $png = $this->source('public/uploads/avatars/'.$this->tag.'.png', 'PNG');
        FakeRemoteAdapter::$corruptWrites = ['private_uploads/eula-pdfs/'.$this->tag.'-a.pdf'];

        $this->move()->assertExitCode(1);

        $this->assertFileExists($a);
        $this->assertFileExists($png);
    }

    #[Test]
    public function an_existing_destination_with_different_content_is_a_conflict(): void
    {
        $a = $this->source('storage/private_uploads/eula-pdfs/'.$this->tag.'.pdf', 'LOCAL-BYTES');
        File::ensureDirectoryExists($this->privateRoot.'/private_uploads/eula-pdfs');
        file_put_contents($this->privateRoot.'/private_uploads/eula-pdfs/'.$this->tag.'.pdf', 'DIFFERENT');

        $this->artisan('snipeit:move-uploads', ['--delete-local' => true])
            ->expectsConfirmation(self::OVERWRITE_LABEL, 'no')
            ->assertExitCode(1);

        $this->assertFileExists($a);
        $this->assertSame('DIFFERENT', file_get_contents($this->privateRoot.'/private_uploads/eula-pdfs/'.$this->tag.'.pdf'));
    }

    #[Test]
    public function an_existing_identical_destination_counts_as_verified(): void
    {
        $a = $this->source('storage/private_uploads/eula-pdfs/'.$this->tag.'.pdf', 'SAME-BYTES');
        File::ensureDirectoryExists($this->privateRoot.'/private_uploads/eula-pdfs');
        file_put_contents($this->privateRoot.'/private_uploads/eula-pdfs/'.$this->tag.'.pdf', 'SAME-BYTES');

        $this->artisan('snipeit:move-uploads', ['--delete-local' => true])
            ->expectsConfirmation(self::OVERWRITE_LABEL, 'no')
            ->assertExitCode(0);

        $this->assertFileDoesNotExist($a);
    }

    #[Test]
    public function a_source_changed_after_it_was_copied_is_never_deleted(): void
    {
        $a = $this->source('storage/private_uploads/eula-pdfs/'.$this->tag.'-a.pdf', 'AAA');
        $b = $this->source('storage/private_uploads/eula-pdfs/'.$this->tag.'-b.pdf', 'BBB');
        $changed = false;
        FakeRemoteAdapter::$afterWrite = function (string $key) use ($a, &$changed): void {
            // Once the remote has the first file, the source is modified.
            if (! $changed && str_contains($key, $this->tag.'-a.pdf')) {
                $changed = true;
                file_put_contents($a, 'AAA-CHANGED-LATER');
                touch($a, time() + 5);
            }
        };

        $this->move()->assertExitCode(1);

        $this->assertFileExists($a);
        $this->assertFileExists($b, 'a change in one source blocks deleting all sources');
    }

    #[Test]
    public function everything_verified_deletes_the_sources_and_succeeds(): void
    {
        $a = $this->source('storage/private_uploads/eula-pdfs/'.$this->tag.'-a.pdf', 'AAA');
        $png = $this->source('public/uploads/avatars/'.$this->tag.'.png', 'PNG');

        $this->move()->assertExitCode(0);

        $this->assertFileDoesNotExist($a);
        $this->assertFileDoesNotExist($png);
        $this->assertSame('AAA', file_get_contents($this->privateRoot.'/private_uploads/eula-pdfs/'.$this->tag.'-a.pdf'));
    }
}
