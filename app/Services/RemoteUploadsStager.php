<?php

namespace App\Services;

use App\Enums\FileStorage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Copies the uploads that live on a remote (S3) disk into a local staging directory laid out like the
 * paths of a backup archive, and describes them in a manifest with a SHA-256 per file.
 *
 *   <root>/public/uploads/<public key>
 *   <root>/storage/private_uploads/<type>/...
 *   <root>/snipeit-remote-manifest.json
 *
 * Only the directories FileStorage manages are copied (plus the logo-style image files at the root of the
 * public disk), never the backups themselves. Objects are streamed; nothing is held in memory. Any failure
 * removes the staging directory again.
 *
 * Callers must stop the web, queue, scheduler and sync jobs first: nothing here blocks writers, so the
 * result is a copy of the files at the time they were read, not a consistent snapshot.
 */
class RemoteUploadsStager
{
    public const MANIFEST = 'snipeit-remote-manifest.json';

    private const MANIFEST_VERSION = 1;

    private const ROOT_IMAGE_EXTENSIONS = ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'ico', 'avif'];

    private const SPACE_MARGIN_MINIMUM = 64 * 1024 * 1024;

    /** @var callable|null */
    private $freeSpaceProbe = null;

    private ?string $root = null;

    public function __construct(private readonly string $stagingBase) {}

    /**
     * Where staged copies are placed when the caller does not say otherwise.
     */
    public static function defaultBase(): string
    {
        return storage_path('framework/backup-staging');
    }

    public function useFreeSpaceProbe(callable $probe): void
    {
        $this->freeSpaceProbe = $probe;
    }

    public function needsStaging(): bool
    {
        return $this->publicIsRemote() || $this->privateIsRemote();
    }

    /**
     * Free space needed: the staged copy, the worst case zip (nothing compresses), the database dump, plus
     * a margin. This is an estimate for failing early; the copy still checks every write.
     */
    public function requiredBytes(int $remoteBytes, int $localBytes, int $databaseBytes): int
    {
        $payload = $remoteBytes + $localBytes + $databaseBytes;

        return $remoteBytes + $payload + max(self::SPACE_MARGIN_MINIMUM, (int) ceil($payload * 0.1));
    }

    /**
     * @return array{root: string, manifest: array}
     *
     * @throws RemoteUploadsException
     */
    public function stage(): array
    {
        $plan = $this->plan();
        $remoteBytes = array_sum(array_column($plan, 'bytes'));

        $this->ensureBase();
        $required = $this->requiredBytes($remoteBytes, $this->localBytes(), $this->databaseBytes());
        $free = $this->freeSpace($this->stagingBase);
        if ($free !== null && $free < $required) {
            throw new RemoteUploadsException(sprintf(
                'Not enough free disk space to stage the S3 files: about %d bytes are needed in %s, %d are free.',
                $required,
                $this->stagingBase,
                $free
            ));
        }

        $this->root = $this->stagingBase.'/'.bin2hex(random_bytes(8));
        if (! @mkdir($this->root, 0700, true) && ! is_dir($this->root)) {
            $this->root = null;
            throw new RemoteUploadsException('Could not create the staging directory in '.$this->stagingBase.'.');
        }
        @chmod($this->root, 0700);

        try {
            $files = [];
            foreach ($plan as $item) {
                $files[] = $this->copyOne($item);
            }

            $manifest = [
                'version' => self::MANIFEST_VERSION,
                'created_at' => gmdate('c'),
                'remote_files' => 'included',
                'files' => $files,
            ];
            $this->writeManifest($manifest);
        } catch (Throwable $e) {
            $this->cleanup();

            throw $e instanceof RemoteUploadsException ? $e : new RemoteUploadsException('Staging the S3 files failed: '.$e->getMessage(), 0, $e);
        }

        return ['root' => $this->root, 'manifest' => $manifest];
    }

    /**
     * Creates a directory that holds only a manifest saying the S3 files are not part of the backup, so
     * that a restore can tell "left out on purpose" from "an old backup without a manifest". Reads nothing
     * from S3.
     *
     * @return string the directory to add to the backup
     */
    public function markExcluded(): string
    {
        $this->ensureBase();
        $this->root = $this->stagingBase.'/'.bin2hex(random_bytes(8));
        if (! @mkdir($this->root, 0700, true) && ! is_dir($this->root)) {
            $this->root = null;
            throw new RemoteUploadsException('Could not create the staging directory in '.$this->stagingBase.'.');
        }
        @chmod($this->root, 0700);

        try {
            $this->writeManifest([
                'version' => self::MANIFEST_VERSION,
                'created_at' => gmdate('c'),
                'remote_files' => 'excluded',
                'files' => [],
            ]);
        } catch (Throwable $e) {
            $this->cleanup();

            throw $e;
        }

        return $this->root;
    }

    public function cleanup(): void
    {
        if ($this->root !== null) {
            File::deleteDirectory($this->root);
            $this->root = null;
        }
    }

    /**
     * Copies one object and returns its manifest entry.
     *
     * @param  array{role: string, disk: string, key: string, archive_path: string, bytes: int}  $item
     */
    private function copyOne(array $item): array
    {
        $destination = $this->root.'/'.$item['archive_path'];
        File::ensureDirectoryExists(dirname($destination), 0700);

        $source = Storage::disk($item['disk'])->readStream($item['key']);
        if (! is_resource($source)) {
            throw new RemoteUploadsException('Could not read "'.$item['key'].'" from the '.$item['role'].' storage.');
        }

        try {
            $copied = $this->copyStream($source, $destination);
        } finally {
            fclose($source);
        }

        clearstatcache(true, $destination);
        $onDisk = @filesize($destination);
        if ($copied !== $item['bytes'] || $onDisk !== $item['bytes']) {
            throw new RemoteUploadsException(sprintf(
                'Copy of "%s" is incomplete: expected %d bytes, copied %d, found %s on disk.',
                $item['key'],
                $item['bytes'],
                $copied,
                var_export($onDisk, true)
            ));
        }

        $sha256 = hash_file('sha256', $destination);
        if ($sha256 === false) {
            throw new RemoteUploadsException('Could not read back the staged copy of "'.$item['key'].'".');
        }

        return [
            'role' => $item['role'],
            'key' => $item['key'],
            'archive_path' => $item['archive_path'],
            'bytes' => $item['bytes'],
            'sha256' => $sha256,
        ];
    }

    /**
     * Streams $source into $destination and returns the number of bytes written.
     *
     * @param  resource  $source
     */
    protected function copyStream($source, string $destination): int
    {
        $target = @fopen($destination, 'wb');
        if ($target === false) {
            throw new RemoteUploadsException('Could not open "'.$destination.'" for writing.');
        }

        $written = stream_copy_to_stream($source, $target);
        $flushed = fflush($target);
        fclose($target);

        if ($written === false || ! $flushed) {
            throw new RemoteUploadsException('Writing the staged copy failed (disk full?).');
        }

        return $written;
    }

    private function writeManifest(array $manifest): void
    {
        $path = $this->root.'/'.self::MANIFEST;
        $json = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        if (@file_put_contents($path, $json) !== strlen($json)) {
            throw new RemoteUploadsException('Could not write the manifest to '.$path.'.');
        }
    }

    /**
     * Lists what has to be copied, with sizes, without reading any object body.
     *
     * @return list<array{role: string, disk: string, key: string, archive_path: string, bytes: int}>
     */
    private function plan(): array
    {
        $plan = [];

        try {
            if ($this->publicIsRemote()) {
                $disk = Storage::disk('public');
                $keys = [];
                foreach (FileStorage::cases() as $case) {
                    if ($case->hasPublicScope()) {
                        $keys = array_merge($keys, $disk->allFiles($case->publicPath()));
                    }
                }
                foreach ($disk->files('') as $key) {
                    if (in_array(strtolower(pathinfo($key, PATHINFO_EXTENSION)), self::ROOT_IMAGE_EXTENSIONS, true)) {
                        $keys[] = $key;
                    }
                }
                foreach ($this->visible($keys) as $key) {
                    $plan[] = [
                        'role' => 'public',
                        'disk' => 'public',
                        'key' => $key,
                        'archive_path' => 'public/uploads/'.$key,
                        'bytes' => $disk->size($key),
                    ];
                }
            }

            if ($this->privateIsRemote()) {
                $name = config('filesystems.default');
                $disk = Storage::disk($name);
                $keys = [];
                foreach (FileStorage::cases() as $case) {
                    // The backups are not uploads: they are what is being written.
                    if ($case !== FileStorage::Backups && $case->hasPrivateScope()) {
                        $keys = array_merge($keys, $disk->allFiles($case->privateStorageKey()));
                    }
                }
                foreach ($this->visible($keys) as $key) {
                    $plan[] = [
                        'role' => 'private',
                        'disk' => $name,
                        'key' => $key,
                        'archive_path' => 'storage/'.$key,
                        'bytes' => $disk->size($key),
                    ];
                }
            }
        } catch (Throwable $e) {
            throw new RemoteUploadsException('Could not list the S3 files: '.$e->getMessage(), 0, $e);
        }

        return $plan;
    }

    /**
     * @param  array<int, string>  $keys
     * @return list<string>
     */
    private function visible(array $keys): array
    {
        $keys = array_filter($keys, fn (string $key) => ! str_starts_with(basename($key), '.'));
        $keys = array_values(array_unique($keys));
        sort($keys);

        return $keys;
    }

    private function publicIsRemote(): bool
    {
        return config('filesystems.disks.public.driver') !== 'local';
    }

    private function privateIsRemote(): bool
    {
        return config('filesystems.disks.'.config('filesystems.default').'.driver') !== 'local';
    }

    private function localBytes(): int
    {
        $total = 0;
        $dirs = [];
        if (! $this->publicIsRemote()) {
            $dirs[] = base_path('public/uploads');
        }
        if (! $this->privateIsRemote()) {
            $dirs[] = base_path('storage/private_uploads');
        }

        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                foreach (File::allFiles($dir, true) as $file) {
                    $total += $file->getSize();
                }
            }
        }

        return $total;
    }

    private function databaseBytes(): int
    {
        try {
            $row = DB::selectOne('select coalesce(sum(data_length + index_length), 0) as bytes from information_schema.tables where table_schema = database()');

            return (int) ($row->bytes ?? 0);
        } catch (Throwable) {
            return 0;
        }
    }

    private function ensureBase(): void
    {
        if (! is_dir($this->stagingBase) && ! @mkdir($this->stagingBase, 0700, true) && ! is_dir($this->stagingBase)) {
            throw new RemoteUploadsException('Could not create the staging directory '.$this->stagingBase.'.');
        }
    }

    private function freeSpace(string $path): ?int
    {
        $free = $this->freeSpaceProbe !== null ? ($this->freeSpaceProbe)($path) : @disk_free_space($path);

        return $free === false || $free === null ? null : (int) $free;
    }
}
