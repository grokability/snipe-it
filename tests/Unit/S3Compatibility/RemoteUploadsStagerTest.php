<?php

namespace Tests\Unit\S3Compatibility;

use App\Services\RemoteUploadsException;
use App\Services\RemoteUploadsStager;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\S3Compatibility\FakeRemoteAdapter;
use Tests\Support\S3Compatibility\UsesFakeRemoteDisks;
use Tests\TestCase;

/**
 * FR-11: S3 hosted upload files are copied to a local staging directory that mirrors the archive paths
 * and described by a SHA-256 manifest; failures leave nothing behind.
 */
class RemoteUploadsStagerTest extends TestCase
{
    use UsesFakeRemoteDisks;

    private string $publicRoot;

    private string $privateRoot;

    private string $stagingBase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->publicRoot = $this->fakeRemoteDisk('public');
        $this->privateRoot = $this->fakeRemoteDisk('custom_private');
        config(['filesystems.default' => 'custom_private']);
        Storage::forgetDisk('custom_private');

        $this->stagingBase = sys_get_temp_dir().'/s3compat-staging-'.bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->stagingBase);
        $this->cleanUpFakeRemoteDisks();

        parent::tearDown();
    }

    private function putObject(string $disk, string $key, string $bytes): void
    {
        Storage::disk($disk)->put($key, $bytes);
    }

    private function stager(?callable $freeSpace = null): RemoteUploadsStager
    {
        $stager = new RemoteUploadsStager($this->stagingBase);
        $stager->useFreeSpaceProbe($freeSpace ?? fn (string $path) => 10 * 1024 * 1024 * 1024);

        return $stager;
    }

    #[Test]
    public function every_managed_object_is_mirrored_with_identical_bytes(): void
    {
        $files = [
            ['public', 'avatars/a.png', random_bytes(300)],
            ['public', 'models/sub/dir/b.jpg', random_bytes(300)],
            ['public', 'logo.png', random_bytes(50)],
            ['public', 'Setting-labels-1-abc.png', random_bytes(50)],
            ['custom_private', 'private_uploads/eula-pdfs/e.pdf', random_bytes(400)],
            ['custom_private', 'private_uploads/imports/legacycsv', 'a,b'],
            ['custom_private', 'private_uploads/imports/nested/x.csv', 'c,d'],
        ];
        foreach ($files as [$disk, $key, $bytes]) {
            $this->putObject($disk, $key, $bytes);
        }
        // Outside the managed prefixes, hidden, or the backups themselves: never staged.
        $this->putObject('public', 'unmanaged/other.png', 'x');
        $this->putObject('public', 'avatars/.gitkeep', '');
        $this->putObject('public', 'random-root-file.txt', 'x');
        $this->putObject('custom_private', 'other-prefix/data.bin', 'x');
        $this->putObject('custom_private', 'backups/old.zip', 'x');
        $this->putObject('custom_private', 'private_uploads/backups/old.zip', 'x');

        $stager = $this->stager();
        $this->assertTrue($stager->needsStaging());
        $result = $stager->stage();
        $root = $result['root'];

        $expected = [
            'public/uploads/avatars/a.png' => $files[0][2],
            'public/uploads/models/sub/dir/b.jpg' => $files[1][2],
            'public/uploads/logo.png' => $files[2][2],
            'public/uploads/Setting-labels-1-abc.png' => $files[3][2],
            'storage/private_uploads/eula-pdfs/e.pdf' => $files[4][2],
            'storage/private_uploads/imports/legacycsv' => $files[5][2],
            'storage/private_uploads/imports/nested/x.csv' => $files[6][2],
        ];

        $staged = [];
        foreach (File::allFiles($root, true) as $file) {
            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
            if ($relative !== RemoteUploadsStager::MANIFEST) {
                $staged[$relative] = file_get_contents($file->getPathname());
            }
        }
        ksort($staged);
        ksort($expected);
        $this->assertSame(array_map('sha1', $expected), array_map('sha1', $staged));

        $manifest = json_decode(file_get_contents($root.'/'.RemoteUploadsStager::MANIFEST), true);
        $this->assertSame('included', $manifest['remote_files']);
        $this->assertCount(count($expected), $manifest['files']);
        foreach ($manifest['files'] as $entry) {
            $this->assertSame(hash('sha256', $expected[$entry['archive_path']]), $entry['sha256']);
            $this->assertSame(strlen($expected[$entry['archive_path']]), $entry['bytes']);
            $this->assertContains($entry['role'], ['public', 'private']);
            $this->assertNotSame('', $entry['key']);
        }
        $this->assertSame($result['manifest'], $manifest);
    }

    #[Test]
    public function the_manifest_does_not_leak_credentials_or_bucket_names(): void
    {
        config(['filesystems.disks.custom_private.bucket' => 'very-secret-bucket']);
        config(['filesystems.disks.custom_private.secret' => 'very-secret-key']);
        config(['filesystems.disks.custom_private.endpoint' => 'https://s3.example.invalid']);
        $this->putObject('custom_private', 'private_uploads/eula-pdfs/e.pdf', 'pdf');

        $result = $this->stager()->stage();
        $raw = file_get_contents($result['root'].'/'.RemoteUploadsStager::MANIFEST);

        foreach (['very-secret-bucket', 'very-secret-key', 'example.invalid', 'X-Amz', 'http'] as $needle) {
            $this->assertStringNotContainsString($needle, $raw);
        }
    }

    #[Test]
    public function staging_directory_is_private_to_the_owner(): void
    {
        $this->putObject('custom_private', 'private_uploads/eula-pdfs/e.pdf', 'pdf');

        $result = $this->stager()->stage();

        $this->assertSame('0700', substr(sprintf('%o', fileperms($result['root'])), -4));
    }

    #[Test]
    public function not_enough_free_space_fails_before_any_object_is_read(): void
    {
        $this->putObject('custom_private', 'private_uploads/eula-pdfs/e.pdf', str_repeat('x', 5000));
        FakeRemoteAdapter::$log = [];

        $stager = $this->stager(fn (string $path) => 100);

        try {
            $stager->stage();
            $this->fail('expected RemoteUploadsException');
        } catch (RemoteUploadsException $e) {
            $this->assertStringContainsString('space', strtolower($e->getMessage()));
        }

        $this->assertSame([], FakeRemoteAdapter::keysFor('read'), 'no object may be read when space is insufficient');
        $this->assertSame([], glob($this->stagingBase.'/*') ?: []);
    }

    #[Test]
    public function the_space_requirement_counts_staging_zip_dump_and_a_safety_margin(): void
    {
        $this->putObject('custom_private', 'private_uploads/eula-pdfs/e.pdf', str_repeat('x', 1000));

        $required = $this->stager()->requiredBytes(remoteBytes: 1000, localBytes: 500, databaseBytes: 200);

        // staging + worst-case zip + dump, plus the margin; never just a fixed multiple of the remote size
        $this->assertGreaterThanOrEqual(1000 + (1000 + 500 + 200) + 200, $required);
        $this->assertGreaterThan($this->stager()->requiredBytes(1000, 0, 0), $required);
    }

    #[Test]
    public function a_read_failure_aborts_and_removes_the_staging_directory(): void
    {
        $this->putObject('custom_private', 'private_uploads/eula-pdfs/a.pdf', 'aaa');
        $this->putObject('custom_private', 'private_uploads/eula-pdfs/b.pdf', 'bbb');
        FakeRemoteAdapter::$failReads = ['private_uploads/eula-pdfs/b.pdf'];

        $this->expectException(RemoteUploadsException::class);
        try {
            $this->stager()->stage();
        } finally {
            $this->assertSame([], glob($this->stagingBase.'/*') ?: [], 'staging directory must be removed after a failure');
        }
    }

    #[Test]
    public function a_size_mismatch_aborts(): void
    {
        $this->putObject('custom_private', 'private_uploads/eula-pdfs/a.pdf', str_repeat('a', 400));
        FakeRemoteAdapter::$truncateReads = ['private_uploads/eula-pdfs/a.pdf'];

        $this->expectException(RemoteUploadsException::class);
        try {
            $this->stager()->stage();
        } finally {
            $this->assertSame([], glob($this->stagingBase.'/*') ?: []);
        }
    }

    #[Test]
    public function a_short_local_write_such_as_a_full_disk_aborts(): void
    {
        $this->putObject('custom_private', 'private_uploads/eula-pdfs/a.pdf', str_repeat('a', 400));

        $stager = new class($this->stagingBase) extends RemoteUploadsStager
        {
            protected function copyStream($source, string $destination): int
            {
                parent::copyStream($source, $destination);

                return 10; // as if the filesystem reported a short write
            }
        };
        $stager->useFreeSpaceProbe(fn (string $path) => 10 * 1024 * 1024 * 1024);

        $this->expectException(RemoteUploadsException::class);
        try {
            $stager->stage();
        } finally {
            $this->assertSame([], glob($this->stagingBase.'/*') ?: []);
        }
    }

    #[Test]
    public function nothing_is_staged_when_every_disk_is_local(): void
    {
        config(['filesystems.disks.public' => ['driver' => 'local', 'root' => $this->publicRoot]]);
        config(['filesystems.default' => 'local']);
        Storage::forgetDisk('public');
        Storage::forgetDisk('local');

        $stager = $this->stager();

        $this->assertFalse($stager->needsStaging());
        $this->assertDirectoryDoesNotExist($this->stagingBase);
    }

    #[Test]
    public function cleanup_removes_the_staging_directory(): void
    {
        $this->putObject('custom_private', 'private_uploads/eula-pdfs/a.pdf', 'aaa');
        $stager = $this->stager();
        $root = $stager->stage()['root'];
        $this->assertDirectoryExists($root);

        $stager->cleanup();

        $this->assertDirectoryDoesNotExist($root);
    }

    #[Test]
    public function large_objects_are_streamed_not_loaded_into_memory(): void
    {
        $size = 64 * 1024 * 1024;
        $path = $this->privateRoot.'/private_uploads/eula-pdfs';
        File::ensureDirectoryExists($path);
        $handle = fopen($path.'/big.bin', 'wb');
        $chunk = str_repeat('z', 1024 * 1024);
        for ($i = 0; $i < 64; $i++) {
            fwrite($handle, $chunk);
        }
        fclose($handle);

        $before = memory_get_peak_usage(true);
        $root = $this->stager()->stage()['root'];
        $growth = memory_get_peak_usage(true) - $before;

        $this->assertSame($size, filesize($root.'/storage/private_uploads/eula-pdfs/big.bin'));
        $this->assertLessThan(24 * 1024 * 1024, $growth, 'staging a 64 MB object must not buffer it in memory');
    }
}
