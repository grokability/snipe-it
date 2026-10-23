<?php

namespace Tests\Support\S3Compatibility;

use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;

/**
 * A Flysystem adapter that behaves like a remote (non-"local" driver) disk for the tests, stores its
 * objects in a plain directory, can be told to fail or corrupt particular keys, and records which
 * keys were listed, read or written so tests can assert on access patterns.
 */
class FakeRemoteAdapter implements FilesystemAdapter
{
    /** @var array<int, string> keys (or key prefixes ending in '/') whose writes throw */
    public static array $failWrites = [];

    /** @var array<int, string> keys whose stored bytes are altered after a successful write */
    public static array $corruptWrites = [];

    /** @var array<int, string> keys whose reads throw */
    public static array $failReads = [];

    /** @var array<int, string> keys whose readStream() yields only the first half of the bytes */
    public static array $truncateReads = [];

    /** @var (\Closure(string): void)|null called with the key after every successful write */
    public static ?\Closure $afterWrite = null;

    /** @var array<int, array{0: string, 1: string}> [operation, key] in call order */
    public static array $log = [];

    public function __construct(private readonly FilesystemAdapter $inner, private readonly string $label = 'remote') {}

    public static function reset(): void
    {
        self::$failWrites = [];
        self::$corruptWrites = [];
        self::$failReads = [];
        self::$truncateReads = [];
        self::$afterWrite = null;
        self::$log = [];
    }

    /** @return array<int, string> keys touched by an operation, in call order */
    public static function keysFor(string $operation): array
    {
        return array_values(array_map(fn ($e) => $e[1], array_filter(self::$log, fn ($e) => $e[0] === $operation)));
    }

    private static function matches(array $rules, string $path): bool
    {
        foreach ($rules as $rule) {
            if ($rule === $path || (str_ends_with($rule, '/') && str_starts_with($path, $rule))) {
                return true;
            }
        }

        return false;
    }

    private function record(string $op, string $path): void
    {
        self::$log[] = [$op, $this->label.':'.$path];
    }

    public function fileExists(string $path): bool
    {
        $this->record('exists', $path);

        return $this->inner->fileExists($path);
    }

    public function directoryExists(string $path): bool
    {
        return $this->inner->directoryExists($path);
    }

    public function write(string $path, string $contents, Config $config): void
    {
        $this->record('write', $path);
        if (self::matches(self::$failWrites, $path)) {
            throw UnableToWriteFile::atLocation($path, 'injected write failure');
        }
        $this->inner->write($path, $contents, $config);
        $this->maybeCorrupt($path);
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->record('write', $path);
        if (self::matches(self::$failWrites, $path)) {
            throw UnableToWriteFile::atLocation($path, 'injected write failure');
        }
        $this->inner->writeStream($path, $contents, $config);
        $this->maybeCorrupt($path);
    }

    private function maybeCorrupt(string $path): void
    {
        if (self::matches(self::$corruptWrites, $path)) {
            $this->inner->write($path, $this->inner->read($path).'-corrupted', new Config);
        }
        if (self::$afterWrite !== null) {
            (self::$afterWrite)($path);
        }
    }

    public function read(string $path): string
    {
        $this->record('read', $path);
        if (self::matches(self::$failReads, $path)) {
            throw UnableToReadFile::fromLocation($path, 'injected read failure');
        }

        return $this->inner->read($path);
    }

    public function readStream(string $path)
    {
        $this->record('read', $path);
        if (self::matches(self::$failReads, $path)) {
            throw UnableToReadFile::fromLocation($path, 'injected read failure');
        }

        if (self::matches(self::$truncateReads, $path)) {
            $bytes = $this->inner->read($path);
            $stream = fopen('php://temp', 'w+b');
            fwrite($stream, substr($bytes, 0, intdiv(strlen($bytes), 2)));
            rewind($stream);

            return $stream;
        }

        return $this->inner->readStream($path);
    }

    public function delete(string $path): void
    {
        $this->record('delete', $path);
        $this->inner->delete($path);
    }

    public function deleteDirectory(string $path): void
    {
        $this->record('delete', $path);
        $this->inner->deleteDirectory($path);
    }

    public function createDirectory(string $path, Config $config): void
    {
        $this->inner->createDirectory($path, $config);
    }

    public function setVisibility(string $path, string $visibility): void
    {
        $this->inner->setVisibility($path, $visibility);
    }

    public function visibility(string $path): FileAttributes
    {
        return $this->inner->visibility($path);
    }

    public function mimeType(string $path): FileAttributes
    {
        return $this->inner->mimeType($path);
    }

    public function lastModified(string $path): FileAttributes
    {
        return $this->inner->lastModified($path);
    }

    public function fileSize(string $path): FileAttributes
    {
        $this->record('size', $path);

        return $this->inner->fileSize($path);
    }

    public function listContents(string $path, bool $deep): iterable
    {
        $this->record('list', $path);

        return $this->inner->listContents($path, $deep);
    }

    public function move(string $source, string $destination, Config $config): void
    {
        $this->inner->move($source, $destination, $config);
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        $this->inner->copy($source, $destination, $config);
    }

    /**
     * Pages that print an image URL (the layout shows the logo) need the disk to be able to name one.
     */
    public function getUrl(string $path): string
    {
        return 'https://remote.invalid/'.ltrim($path, '/');
    }
}
