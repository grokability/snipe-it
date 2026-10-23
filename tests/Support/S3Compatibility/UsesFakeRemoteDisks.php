<?php

namespace Tests\Support\S3Compatibility;

use Illuminate\Filesystem\FilesystemAdapter as LaravelAdapter;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

/**
 * Registers disks whose driver is not "local" but whose objects live in a temporary directory.
 * Use for logic tests only; the integration run uses the real S3 adapter against an S3 mock.
 */
trait UsesFakeRemoteDisks
{
    /** @var array<int, string> */
    private array $fakeRemoteRoots = [];

    protected function fakeRemoteDisk(string $name, ?string $root = null): string
    {
        $root ??= sys_get_temp_dir().'/s3compat-'.$name.'-'.bin2hex(random_bytes(4));
        File::ensureDirectoryExists($root);
        $this->fakeRemoteRoots[] = $root;

        Storage::extend('fakeremote', function ($app, array $config) {
            $adapter = new FakeRemoteAdapter(new LocalFilesystemAdapter($config['root']), $config['label'] ?? 'remote');

            return new LaravelAdapter(new Filesystem($adapter, $config), $adapter, $config);
        });

        config(['filesystems.disks.'.$name => ['driver' => 'fakeremote', 'root' => $root, 'label' => $name, 'throw' => false]]);
        Storage::forgetDisk($name);

        return $root;
    }

    protected function cleanUpFakeRemoteDisks(): void
    {
        FakeRemoteAdapter::reset();
        foreach ($this->fakeRemoteRoots as $root) {
            File::deleteDirectory($root);
        }
        $this->fakeRemoteRoots = [];
    }
}
