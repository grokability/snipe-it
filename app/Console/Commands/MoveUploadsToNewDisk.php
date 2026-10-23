<?php

namespace App\Console\Commands;

use App\Enums\FileStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\progress;
use function Laravel\Prompts\table;
use function Laravel\Prompts\warning;

class MoveUploadsToNewDisk extends Command
{
    protected $signature = 'snipeit:move-uploads
        {--delete-local : Delete the local source files after a successful copy. Prompts if omitted.}
        {--overwrite : Overwrite destination files that already exist. Prompts if omitted.}';

    protected $description = 'Move locally uploaded files to the currently configured storage disk.';

    /**
     * Per-type tally rendered in the summary table at the end. One row
     * per FileStorage case the sweep touched, so admins can eyeball
     * what moved without reading the full per-file stream.
     *
     * @var array<string, array{copied: int, skipped: int, private: int, private_skipped: int, errors: int}>
     */
    private array $summary = [];

    private bool $overwrite = false;

    /**
     * Every source file that was written (or found identical) and verified, keyed by its local path,
     * with what it looked like when it was copied. The final phase re-checks each one before any
     * local file is deleted.
     *
     * @var array<string, array{size: int, mtime: int, sha256: string}>
     */
    private array $verified = [];

    /** @var array<int, string> human readable reasons, listed at the end */
    private array $problems = [];

    public function handle(): int
    {
        $publicRemote = $this->isRemote('public');
        $privateRemote = $this->isRemote(config('filesystems.default'));

        if (! $publicRemote && ! $privateRemote) {
            error('Both the public and the private disk are local. Nothing to move.');
            note('Set PUBLIC_FILESYSTEM_DISK=s3_public and/or PRIVATE_FILESYSTEM_DISK=s3_private in your .env, then rerun.');

            return self::FAILURE;
        }

        $publicDisk = config('filesystems.disks.public.driver', '?');
        $privateDisk = config('filesystems.disks.'.config('filesystems.default').'.driver', '?');
        info("Target public disk: {$publicDisk}. Target private disk: {$privateDisk}.");
        if (! $publicRemote) {
            note('The public disk is local, so public files are left where they are.');
        }
        if (! $privateRemote) {
            note('The private disk is local, so private files are left where they are.');
        }

        $this->overwrite = $this->option('overwrite')
            || confirm(
                label: 'Overwrite destination files that already exist? Choose "No" to skip them and keep whatever is already on the destination.',
                default: false,
            );

        $publicSources = $publicRemote ? $this->collectPublicSources() : [];
        $privateSources = $privateRemote ? $this->collectPrivateSources() : [];
        $logoSources = $publicRemote ? array_merge(
            glob('public/uploads/setting*.*') ?: [],
            file_exists('public/uploads/default.png') ? ['public/uploads/default.png'] : [],
            file_exists('public/uploads/snipe-logo.png') ? ['public/uploads/snipe-logo.png'] : [],
        ) : [];

        $this->copyPublic($publicSources);
        $this->copyLogos($logoSources);
        $this->copyPrivate($privateSources);

        $this->renderSummary();

        $errors = $this->totalErrors();
        if ($errors > 0) {
            error("{$errors} file(s) could not be moved or verified. No local file was deleted.");
            foreach ($this->problems as $problem) {
                warning($problem);
            }
        }

        $deleteLocal = $this->option('delete-local')
            || ($errors === 0 && confirm(
                label: 'Also delete the local source files? This cannot be undone.',
                default: false,
            ));

        if ($deleteLocal && $errors === 0) {
            if (! $this->deleteLocalSources()) {
                return self::FAILURE;
            }
        } elseif ($deleteLocal) {
            warning('Local source files were kept because something failed.');
        }

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function isRemote(string $disk): bool
    {
        return config("filesystems.disks.$disk.driver", 'local') !== 'local';
    }

    /**
     * Every regular file under $dir (any depth, any name except dotfiles), as
     * [local path, path relative to $dir].
     *
     * @return array<int, array{0: string, 1: string}>
     */
    private function listFiles(string $dir): array
    {
        if (! is_dir($dir)) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS),
        );
        foreach ($iterator as $file) {
            if (! $file->isFile() || str_starts_with($file->getFilename(), '.')) {
                continue;
            }
            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen(rtrim($dir, '/\\')) + 1));
            $files[] = [str_replace('\\', '/', $file->getPathname()), $relative];
        }
        usort($files, fn (array $a, array $b) => strcmp($a[1], $b[1]));

        return $files;
    }

    /**
     * @return array<string, array<int, array{0: string, 1: string}>>
     */
    private function collectPublicSources(): array
    {
        $sources = [];
        foreach (FileStorage::cases() as $case) {
            if (! $case->hasPublicScope() || $case === FileStorage::Barcodes) {
                continue;
            }
            $sources[$case->value] = $this->listFiles($case->publicDir());
        }

        return $sources;
    }

    /**
     * @return array<string, array<int, array{0: string, 1: string}>>
     */
    private function collectPrivateSources(): array
    {
        $sources = [];
        foreach (FileStorage::cases() as $case) {
            if (! $case->hasPrivateScope()) {
                continue;
            }
            $sources[$case->value] = $this->listFiles($case->privateDir());
        }

        return $sources;
    }

    /**
     * Copy one local file to $disk/$key and verify it. Returns null when the file is fine (written, or
     * already there with identical bytes) and an error string otherwise. A file found at the destination
     * with different content counts as a conflict unless --overwrite was given.
     *
     * @return array{status: string, error: ?string} status is "copied" or "skipped"
     */
    private function transfer(string $localPath, string $diskName, string $key): array
    {
        $disk = Storage::disk($diskName);
        $size = filesize($localPath);
        $mtime = filemtime($localPath);
        $sha = hash_file('sha256', $localPath);

        if ($size === false || $mtime === false || $sha === false) {
            return ['status' => 'error', 'error' => "{$localPath}: cannot read the local file"];
        }

        $exists = $disk->exists($key);

        if ($exists && ! $this->overwrite) {
            [$remoteSize, $remoteSha] = $this->describeRemote($disk, $key);
            if ($remoteSize === $size && $remoteSha === $sha) {
                $this->verified[$localPath] = ['size' => $size, 'mtime' => $mtime, 'sha256' => $sha];

                return ['status' => 'skipped', 'error' => null];
            }

            return ['status' => 'error', 'error' => "{$key}: already exists on {$diskName} with different content (use --overwrite to replace it)"];
        }

        $stream = fopen($localPath, 'rb');
        if ($stream === false) {
            return ['status' => 'error', 'error' => "{$localPath}: cannot open the local file"];
        }
        try {
            $written = $disk->writeStream($key, $stream);
        } catch (Throwable $e) {
            Log::debug($e);

            return ['status' => 'error', 'error' => "{$key}: write to {$diskName} failed"];
        } finally {
            fclose($stream);
        }
        if ($written === false) {
            return ['status' => 'error', 'error' => "{$key}: write to {$diskName} failed"];
        }

        [$remoteSize, $remoteSha] = $this->describeRemote($disk, $key);
        if ($remoteSize !== $size || $remoteSha !== $sha) {
            return ['status' => 'error', 'error' => "{$key}: read back from {$diskName} does not match the local file"];
        }

        $this->verified[$localPath] = ['size' => $size, 'mtime' => $mtime, 'sha256' => $sha];

        return ['status' => 'copied', 'error' => null];
    }

    /**
     * Size and SHA-256 of an object, read as a stream. [null, null] when it cannot be read.
     *
     * @return array{0: ?int, 1: ?string}
     */
    private function describeRemote($disk, string $key): array
    {
        try {
            $stream = $disk->readStream($key);
            if (! is_resource($stream)) {
                return [null, null];
            }
            $context = hash_init('sha256');
            $size = 0;
            while (! feof($stream)) {
                $chunk = fread($stream, 1024 * 1024);
                if ($chunk === false) {
                    fclose($stream);

                    return [null, null];
                }
                $size += strlen($chunk);
                hash_update($context, $chunk);
            }
            fclose($stream);

            return [$size, hash_final($context)];
        } catch (Throwable $e) {
            Log::debug($e);

            return [null, null];
        }
    }

    /**
     * @param  array<string, array<int, array{0: string, 1: string}>>  $sources
     */
    private function copyPublic(array $sources): void
    {
        foreach ($sources as $type => $files) {
            if ($files === []) {
                continue;
            }

            $copied = 0;
            $skipped = 0;
            $errors = 0;

            progress(
                label: "Copying public {$type}",
                steps: $files,
                callback: function (array $file) use ($type, &$copied, &$skipped, &$errors): void {
                    // The `public` disk is already rooted at the uploads
                    // directory on local and the per-install bucket root
                    // on S3, so the destination key is `<type>/<file>`
                    // without an `uploads/` prefix. The application reads
                    // image URLs via FileStorage::<Case>->publicPath()
                    // which emits `<type>/`. Prefixing here would land
                    // the file at a key the app never resolves.
                    $result = $this->transfer($file[0], 'public', $type.'/'.$file[1]);
                    $this->count($result, $copied, $skipped, $errors);
                },
            );

            $this->tally($type, copied: $copied, skipped: $skipped, errors: $errors);
        }
    }

    /**
     * @param  array<int, string>  $logoSources
     */
    private function copyLogos(array $logoSources): void
    {
        if ($logoSources === []) {
            return;
        }

        $copied = 0;
        $skipped = 0;
        $errors = 0;

        progress(
            label: 'Copying branding logos',
            steps: $logoSources,
            callback: function (string $filepath) use (&$copied, &$skipped, &$errors): void {
                // Branding logos (logo.*, favicon.*, Setting-*) live at
                // the root of the public disk, so no subdirectory
                // prefix.
                $result = $this->transfer($filepath, 'public', basename($filepath));
                $this->count($result, $copied, $skipped, $errors);
            },
        );

        $this->tally('logos', copied: $copied, skipped: $skipped, errors: $errors);
    }

    /**
     * @param  array<string, array<int, array{0: string, 1: string}>>  $sources
     */
    private function copyPrivate(array $sources): void
    {
        $diskName = config('filesystems.default');

        foreach ($sources as $type => $files) {
            if ($files === []) {
                continue;
            }

            $copied = 0;
            $skipped = 0;
            $errors = 0;
            $prefix = FileStorage::from($type)->privateStorageKey();

            progress(
                label: "Copying private {$type}",
                steps: $files,
                callback: function (array $file) use ($diskName, $prefix, &$copied, &$skipped, &$errors): void {
                    // The key is the one the application reads, e.g. private_uploads/eula-pdfs/<file>.
                    $result = $this->transfer($file[0], $diskName, $prefix.$file[1]);
                    $this->count($result, $copied, $skipped, $errors);
                },
            );

            $this->tally($type, private: $copied, private_skipped: $skipped, errors: $errors);
        }
    }

    /**
     * @param  array{status: string, error: ?string}  $result
     */
    private function count(array $result, int &$copied, int &$skipped, int &$errors): void
    {
        match ($result['status']) {
            'copied' => $copied++,
            'skipped' => $skipped++,
            default => $errors++,
        };
        if ($result['error'] !== null) {
            $this->problems[] = $result['error'];
        }
    }

    private function totalErrors(): int
    {
        return array_sum(array_column($this->summary, 'errors'));
    }

    /**
     * Delete the verified local sources, but only if every one of them still looks exactly as it did
     * when it was copied. If anything changed, nothing is deleted.
     */
    private function deleteLocalSources(): bool
    {
        foreach ($this->verified as $path => $before) {
            clearstatcache(true, $path);
            $size = @filesize($path);
            $mtime = @filemtime($path);
            $sha = $size === false ? false : @hash_file('sha256', $path);

            if ($size !== $before['size'] || $mtime !== $before['mtime'] || $sha !== $before['sha256']) {
                error("{$path} changed after it was copied. No local file was deleted; run the command again.");

                return false;
            }
        }

        warning('Deleting local source files. This cannot be undone.');

        $deleted = 0;
        foreach (array_keys($this->verified) as $path) {
            if ($this->tryUnlink($path)) {
                $deleted++;
            }
        }

        info("Deleted {$deleted} file(s) from the local filesystem.");

        return true;
    }

    private function tryUnlink(string $filepath): bool
    {
        try {
            return @unlink($filepath);
        } catch (Throwable $e) {
            Log::debug($e);

            return false;
        }
    }

    /**
     * Separate public / private counters so the summary table can show
     * both axes without conflating them. "copied" and "skipped" are
     * the public-side tallies, "private" and "private_skipped" the
     * matching pair for the private disk. "errors" aggregates both.
     */
    private function tally(string $type, int $copied = 0, int $skipped = 0, int $private = 0, int $private_skipped = 0, int $errors = 0): void
    {
        if (! isset($this->summary[$type])) {
            $this->summary[$type] = [
                'copied' => 0,
                'skipped' => 0,
                'private' => 0,
                'private_skipped' => 0,
                'errors' => 0,
            ];
        }
        $this->summary[$type]['copied'] += $copied;
        $this->summary[$type]['skipped'] += $skipped;
        $this->summary[$type]['private'] += $private;
        $this->summary[$type]['private_skipped'] += $private_skipped;
        $this->summary[$type]['errors'] += $errors;
    }

    private function renderSummary(): void
    {
        if ($this->summary === []) {
            info('No files found to move.');

            return;
        }

        ksort($this->summary);

        $rows = [];
        $totals = [
            'copied' => 0,
            'skipped' => 0,
            'private' => 0,
            'private_skipped' => 0,
            'errors' => 0,
        ];
        foreach ($this->summary as $type => $counts) {
            $rows[] = [
                $type,
                $counts['copied'],
                $counts['skipped'],
                $counts['private'],
                $counts['private_skipped'],
                $counts['errors'],
            ];
            foreach (array_keys($totals) as $k) {
                $totals[$k] += $counts[$k];
            }
        }
        $rows[] = [
            'TOTAL',
            $totals['copied'],
            $totals['skipped'],
            $totals['private'],
            $totals['private_skipped'],
            $totals['errors'],
        ];

        table(['Type', 'Public copied', 'Public skipped', 'Private copied', 'Private skipped', 'Errors'], $rows);
    }
}
