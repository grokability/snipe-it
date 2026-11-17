<?php

namespace Tests;

use App\Http\Middleware\SecurityHeaders;
use App\Models\Asset;
use App\Models\Company;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\Support\AssertHasActionLogs;
use Tests\Support\AssertsAgainstSlackNotifications;
use Tests\Support\CanSkipTests;
use Tests\Support\CustomTestMacros;
use Tests\Support\InitializesSettings;
use Tests\Support\InteractsWithAuthentication;
use Tests\Support\SeedsShippedSyncAdapters;

abstract class TestCase extends BaseTestCase
{
    use AssertHasActionLogs;
    use AssertsAgainstSlackNotifications;
    use CanSkipTests;
    use CreatesApplication;
    use CustomTestMacros;
    use InitializesSettings;
    use InteractsWithAuthentication;
    use LazilyRefreshDatabase;
    use SeedsShippedSyncAdapters;

    private array $globallyDisabledMiddleware = [
        SecurityHeaders::class,
    ];

    /**
     * tempnam() source paths registered via trackTempFile(), unlinked
     * in tearDown. Laravel's test harness doesn't clean these on its
     * own and CI /tmp accretes a file per upload test otherwise.
     *
     * @var array<int, string>
     */
    private array $uploadedTempPaths = [];

    /**
     * Snapshot of every path under Snipe-IT's upload trees at setUp
     * time (recursive). tearDown diffs against the current contents to
     * unlink anything new, so files any controller / Storage::put /
     * factory happens to write during the test don't survive and
     * pollute the local dev dirs. Covers both the private-uploads tree
     * (acceptance signatures, eula pdfs, imports, audits, model-owned
     * attachments, etc) and the public-uploads tree (avatars, barcodes,
     * and model images). Skipped under `artisan test --parallel`
     * because workers share the filesystem, so Worker A's tearDown
     * would race Worker B's in-flight writes. Parallel mode uses
     * Storage::fake() in setUp instead.
     *
     * @var array<int, string>
     */
    private array $uploadTreeSnapshot = [];

    protected function setUp(): void
    {
        $this->guardAgainstMissingEnv();

        parent::setUp();

        $this->registerCustomMacros();

        $this->withoutMiddleware($this->globallyDisabledMiddleware);

        $this->initializeSettings();
        $this->seedShippedSyncAdapters();

        // Flush the custom field filter map cache between tests so that
        // dynamically-created custom fields are always picked up fresh.
        Asset::flushCustomFieldFilterMap();

        // Per-request memoization keyed by user id leaks across tests because
        // LazilyRefreshDatabase rolls back the DB but not PHP static state.
        // Auto-increment may hand the same id to a different test's user
        // with a different pivot set.
        Company::flushCompanyIdsCache();
        Company::flushCompanyColumnCache();
        Company::flushUserVisibilityCache();

        // Sync-adapter instance cache is another static that would hold
        // rolled-back model references between tests otherwise.
        \App\Models\SyncAdapterConfig::flushInstanceCache();

        if ($this->isRunningInParallel()) {
            // Parallel mode uses Laravel's fake public disk (rooted
            // per worker under storage/framework/testing/disks/public)
            // so controllers that call Storage::disk('public')->put()
            // for avatars, item images, and barcodes land in the test
            // garbage dir rather than polluting the real public tree.
            // The default disk stays real because the import flow
            // mixes Storage::put() writes with storage_path() reads
            // and the two must agree. Single-process mode keeps the
            // snapshot/diff sweep instead.
            Storage::fake('public');
        } else {
            $this->uploadTreeSnapshot = $this->currentUploadTreeListing();
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->uploadedTempPaths as $path) {
            @unlink($path);
        }
        $this->uploadedTempPaths = [];

        if (! $this->isRunningInParallel()) {
            $after = $this->currentUploadTreeListing();
            foreach (array_diff($after, $this->uploadTreeSnapshot) as $path) {
                @unlink($path);
            }
        }

        parent::tearDown();
    }

    /**
     * `artisan test --parallel` spawns multiple PHP workers that
     * isolate DBs per worker but share one filesystem. The upload-tree
     * snapshot/diff is not safe under that shape because Worker A's
     * tearDown would see Worker B's in-flight writes as "new since my
     * snapshot" and unlink them mid-test. ParaTest sets TEST_TOKEN per
     * worker. When it is absent we are running single-threaded (local
     * dev `./vendor/bin/phpunit`) and the cleanup is safe.
     */
    private function isRunningInParallel(): bool
    {
        return env('TEST_TOKEN') !== null;
    }

    /**
     * Shared helper for feature tests that round-trip a CSV (or any
     * file) through an upload endpoint. Creates a tempnam-backed
     * UploadedFile and registers the source path so base tearDown can
     * unlink it.
     */
    protected function tempUploadedFile(string $filename, string $content, string $mime = 'text/csv'): UploadedFile
    {
        $path = $this->trackTempFile(tempnam(sys_get_temp_dir(), 'upload'));
        file_put_contents($path, $content);

        return new UploadedFile($path, $filename, $mime, null, true);
    }

    /**
     * Register a filesystem path for cleanup in base tearDown. Returns
     * the same path for fluent one-line use alongside tempnam() /
     * file_put_contents() in tests that need to craft malformed PNGs,
     * SVG fixtures, cert plants, or anything outside the shape
     * tempUploadedFile() covers. The path does not need to exist at
     * registration time (tearDown unlink is noop safe).
     */
    protected function trackTempFile(string $path): string
    {
        $this->uploadedTempPaths[] = $path;

        return $path;
    }

    /**
     * Recursive listing of every file (not directory) under the two
     * upload trees Snipe-IT writes to. tearDown diffs this against the
     * setUp snapshot and unlinks anything new. Bind-mounted upload
     * dirs work transparently because the kernel resolves the mount
     * before PHP's iterator ever sees it.
     *
     * @return array<int, string>
     */
    private function currentUploadTreeListing(): array
    {
        $roots = array_filter([
            config('app.private_uploads'),
            public_path('uploads'),
        ], fn ($root) => is_string($root) && is_dir($root));

        $paths = [];
        foreach ($roots as $root) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
            );
            foreach ($iterator as $info) {
                /** @var \SplFileInfo $info */
                if ($info->isFile()) {
                    $paths[] = $info->getPathname();
                }
            }
        }

        return $paths;
    }

    private function guardAgainstMissingEnv(): void
    {
        if (! file_exists(realpath(__DIR__.'/../').'/.env.testing')) {
            throw new RuntimeException(
                '.env.testing file does not exist. Aborting to avoid wiping your local database.'
            );
        }
    }
}
