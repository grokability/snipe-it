<?php

use App\Console\Commands\RestoreFromBackup;

beforeEach(function () {
    $this->sandbox = sys_get_temp_dir().'/snipeit-restore-prune-'.uniqid();
    mkdir($this->sandbox, 0755, true);
});

afterEach(function () {
    recursiveDelete($this->sandbox);
});

test('prunes files from each directory except gitkeep', function () {
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

    expect($publicDir.'/.gitkeep')->toBeFile('.gitkeep must survive pruning so the empty directory stays checked in.');
    expect($privateDir.'/.gitkeep')->toBeFile();
});

test('handles missing directories silently', function () {
    $missing = $this->sandbox.'/does/not/exist';

    $prune = new ReflectionMethod(RestoreFromBackup::class, 'pruneUploadDirectories');

    // Should not throw even when the enumerated directory does not exist.
    $prune->invoke(null, [$missing], []);

    $this->assertDirectoryDoesNotExist($missing);
});

test('prunes glob pattern matches at the uploads root', function () {
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

    expect($uploadsRoot.'/unrelated.txt')->toBeFile('Non-matching files must survive.');
});

test('leaves nested subdirectories alone', function () {
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
    expect($nested)->toBeDirectory('Nested subdirectory should survive pruning.');
    expect($nested.'/nested.png')->toBeFile('Nested file should survive pruning.');
});

function recursiveDelete(string $path): void
{
    if (! is_dir($path)) {
        @unlink($path);

        return;
    }
    foreach (scandir($path) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        recursiveDelete($path.'/'.$entry);
    }
    @rmdir($path);
}
