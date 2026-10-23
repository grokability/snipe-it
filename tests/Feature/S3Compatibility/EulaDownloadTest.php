<?php

namespace Tests\Feature\S3Compatibility;

use App\Enums\FileStorage;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * FR-2: EULA downloads decide between S3 and local by the driver of the disk named by
 * filesystems.default, not by the disk's name.
 */
class EulaDownloadTest extends TestCase
{
    private string $localFile = '';

    protected function tearDown(): void
    {
        if ($this->localFile !== '' && file_exists($this->localFile)) {
            unlink($this->localFile);
        }

        parent::tearDown();
    }

    public static function routes(): array
    {
        return [
            'profile route' => ['profile.storedeula.download', false],
            'action log route' => ['log.storedeula.download', true],
        ];
    }

    private function useRemoteDisk(string $name): void
    {
        config(['filesystems.disks.'.$name => [
            'driver' => 's3',
            'key' => 'example-key',
            'secret' => 'example-secret',
            'region' => 'us-east-1',
            'bucket' => 'example-bucket',
            'endpoint' => 'https://s3.example.invalid',
            'use_path_style_endpoint' => true,
        ]]);
        config(['filesystems.default' => $name]);
        Storage::forgetDisk($name);
    }

    private function acceptedLog(User $target, ?string $filename = null): Actionlog
    {
        $asset = Asset::factory()->create();

        return Actionlog::factory()->create([
            'action_type' => 'accepted',
            'item_id' => $asset->id,
            'item_type' => Asset::class,
            'target_id' => $target->id,
            'target_type' => User::class,
            'created_by' => $target->id,
            'filename' => $filename ?? 'accepted-'.bin2hex(random_bytes(4)).'.pdf',
        ]);
    }

    #[Test]
    #[DataProvider('routes')]
    public function s3_driver_disk_with_a_custom_name_redirects_to_a_presigned_url(string $route, bool $needsViewPermission): void
    {
        $this->useRemoteDisk('custom_private');
        $user = $needsViewPermission ? User::factory()->superuser()->create() : User::factory()->create();
        $log = $this->acceptedLog($user);

        $response = $this->actingAs($user)->get(route($route, ['filename' => $log->filename]));

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertStringStartsWith('https://s3.example.invalid/example-bucket/', $location);
        $this->assertStringContainsString(FileStorage::EulaPdfs->privateStorageKey().$log->filename, urldecode($location));
        $this->assertStringContainsString('X-Amz-Signature=', $location);
    }

    #[Test]
    #[DataProvider('routes')]
    public function s3_private_disk_name_still_redirects_to_a_presigned_url(string $route, bool $needsViewPermission): void
    {
        $this->useRemoteDisk('s3_private');
        $user = $needsViewPermission ? User::factory()->superuser()->create() : User::factory()->create();
        $log = $this->acceptedLog($user);

        $response = $this->actingAs($user)->get(route($route, ['filename' => $log->filename]));

        $response->assertRedirect();
        $this->assertStringContainsString('X-Amz-Signature=', $response->headers->get('Location'));
    }

    #[Test]
    #[DataProvider('routes')]
    public function local_disk_still_downloads_the_stored_file(string $route, bool $needsViewPermission): void
    {
        config(['filesystems.default' => 'local']);
        Storage::forgetDisk('local');

        $user = $needsViewPermission ? User::factory()->superuser()->create() : User::factory()->create();
        $log = $this->acceptedLog($user);
        $pdf = "%PDF-1.4\n".random_bytes(256);
        $key = FileStorage::EulaPdfs->privateStorageKey().$log->filename;
        Storage::disk('local')->put($key, $pdf);
        $this->localFile = Storage::disk('local')->path($key);
        $this->assertSame(config('app.private_uploads').'/eula-pdfs/'.$log->filename, $this->localFile);

        $response = $this->actingAs($user)->get(route($route, ['filename' => $log->filename]));

        $response->assertOk();
        $this->assertSame(hash('sha256', $pdf), hash('sha256', File::get($response->baseResponse->getFile()->getPathname())));
    }

    #[Test]
    public function inline_request_on_local_disk_still_serves_the_file_inline(): void
    {
        config(['filesystems.default' => 'local']);
        Storage::forgetDisk('local');

        $user = User::factory()->superuser()->create();
        $log = $this->acceptedLog($user);
        $key = FileStorage::EulaPdfs->privateStorageKey().$log->filename;
        Storage::disk('local')->put($key, "%PDF-1.4\nbody");
        $this->localFile = Storage::disk('local')->path($key);

        $response = $this->actingAs($user)->get(route('log.storedeula.download', ['filename' => $log->filename, 'inline' => 'true']));

        $response->assertOk();
        // response()->file() sends no attachment disposition, so the browser shows the PDF inline.
        $this->assertStringNotContainsString('attachment', (string) $response->headers->get('Content-Disposition'));
        $this->assertSame(hash('sha256', "%PDF-1.4\nbody"), hash('sha256', File::get($response->baseResponse->getFile()->getPathname())));
    }

    #[Test]
    #[DataProvider('routes')]
    public function users_without_permission_are_still_refused_on_a_custom_s3_disk(string $route, bool $needsViewPermission): void
    {
        $this->useRemoteDisk('custom_private');
        $owner = User::factory()->create();
        $log = $this->acceptedLog($owner);

        $this->actingAs(User::factory()->create())
            ->get(route($route, ['filename' => $log->filename]))
            ->assertForbidden();
    }
}
