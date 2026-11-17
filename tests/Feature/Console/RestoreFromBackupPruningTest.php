<?php

namespace Tests\Feature\Console;

use App\Console\Commands\RestoreFromBackup;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Covers the per-upload-directory pruning step in the restore flow.
 * Pre-fix, the restore extracted the backup's files on top of the
 * existing install without clearing them, so any image or file that
 * the backup did not carry survived the restore and compounded into
 * every subsequent backup. #19770.
 *
 * The pruning method is factored out of handle() so this test can
 * drive it directly against a sandbox directory tree rather than
 * invoking the full restore (which needs a real zip + a wiped DB).
 */
class RestoreFromBackupPruningTest extends TestCase
{
    private string $sandbox;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sandbox = sys_get_temp_dir().'/snipeit-restore-prune-'.uniqid();
        mkdir($this->sandbox, 0755, true);
    }

    protected function tearDown(): void
    {
        $this->recursiveDelete($this->sandbox);
        parent::tearDown();
    }

    public function test_prunes_files_from_each_directory_except_gitkeep(): void
    {
        $publicDir = $this->sandbox.'/public/uploads/assets';
        $privateDir = $this->sandbox.'/storage/private_uploads/signatures';
        mkdir($publicDir, 0755, true);
        mkdir($privateDir, 0755, true);

        file_put_contents($publicDir.'/asset-1.png', 'bytes');
        file_put_contents($publicDir.'/asset-2.png', 'bytes');
        file_put_contents($publicDir.'/.gitkeep', '');
        file_put_contents($privateDir.'/siglog-abc.png', 'bytes');
        file_put_contents($privateDir.'/.gitkeep', '');

        $prune = new ReflectionMethod(RestoreFromBackup::class, 'pruneUploadDirectories');
        $prune->invoke(null, [$publicDir], [$privateDir]);

        $this->assertFileDoesNotExist($publicDir.'/asset-1.png');
        $this->assertFileDoesNotExist($publicDir.'/asset-2.png');
        $this->assertFileDoesNotExist($privateDir.'/siglog-abc.png');

        $this->assertFileExists($publicDir.'/.gitkeep', '.gitkeep must survive pruning so the empty directory stays checked in.');
        $this->assertFileExists($privateDir.'/.gitkeep');
    }

    public function test_handles_missing_directories_silently(): void
    {
        $missing = $this->sandbox.'/does/not/exist';

        $prune = new ReflectionMethod(RestoreFromBackup::class, 'pruneUploadDirectories');
        // Should not throw even when the enumerated directory does not exist.
        $prune->invoke(null, [$missing], []);

        $this->assertDirectoryDoesNotExist($missing);
    }

    public function test_prunes_glob_pattern_matches_at_the_uploads_root(): void
    {
        // Root-level Settings branding files (logo.*, favicon.*, Setting-*)
        // live outside the enumerated upload subdirectories. They need
        // their own pruning pass so stale branding does not survive
        // the restore. #19770.
        $uploadsRoot = $this->sandbox.'/public/uploads';
        mkdir($uploadsRoot, 0755, true);

        file_put_contents($uploadsRoot.'/logo.png', 'bytes');
        file_put_contents($uploadsRoot.'/logo.svg', 'bytes');
        file_put_contents($uploadsRoot.'/favicon.ico', 'bytes');
        file_put_contents($uploadsRoot.'/favicon-uploaded.png', 'bytes');
        file_put_contents($uploadsRoot.'/Setting-labels-1-abc.png', 'bytes');
        file_put_contents($uploadsRoot.'/setting-pdfs-1-xyz.png', 'bytes');
        file_put_contents($uploadsRoot.'/unrelated.txt', 'bytes');

        $prune = new ReflectionMethod(RestoreFromBackup::class, 'pruneUploadFiles');
        $prune->invoke(null, [
            $uploadsRoot.'/Setting-*',
            $uploadsRoot.'/setting-*',
            $uploadsRoot.'/logo.*',
            $uploadsRoot.'/favicon.*',
            $uploadsRoot.'/favicon-uploaded.*',
        ]);

        $this->assertFileDoesNotExist($uploadsRoot.'/logo.png');
        $this->assertFileDoesNotExist($uploadsRoot.'/logo.svg');
        $this->assertFileDoesNotExist($uploadsRoot.'/favicon.ico');
        $this->assertFileDoesNotExist($uploadsRoot.'/favicon-uploaded.png');
        $this->assertFileDoesNotExist($uploadsRoot.'/Setting-labels-1-abc.png');
        $this->assertFileDoesNotExist($uploadsRoot.'/setting-pdfs-1-xyz.png');

        $this->assertFileExists($uploadsRoot.'/unrelated.txt', 'Non-matching files must survive.');
    }

    public function test_leaves_nested_subdirectories_alone(): void
    {
        // Snipe-IT's upload dirs are flat today, but defensive against
        // future shapes: pruning deletes only direct files, not whole
        // subtrees.
        $dir = $this->sandbox.'/public/uploads/assets';
        $nested = $dir.'/subfolder';
        mkdir($nested, 0755, true);

        file_put_contents($dir.'/top-level.png', 'bytes');
        file_put_contents($nested.'/nested.png', 'bytes');

        $prune = new ReflectionMethod(RestoreFromBackup::class, 'pruneUploadDirectories');
        $prune->invoke(null, [$dir], []);

        $this->assertFileDoesNotExist($dir.'/top-level.png');
        $this->assertDirectoryExists($nested, 'Nested subdirectory should survive pruning.');
        $this->assertFileExists($nested.'/nested.png', 'Nested file should survive pruning.');
    }

    private function recursiveDelete(string $path): void
    {
        if (! is_dir($path)) {
            @unlink($path);

            return;
        }
        foreach (scandir($path) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $this->recursiveDelete($path.'/'.$entry);
        }
        @rmdir($path);
    }
}
