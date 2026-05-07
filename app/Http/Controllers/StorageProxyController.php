<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StorageProxyController extends Controller
{
    /**
     * Proxy files from the "public" storage disk through the application.
     *
     * When PUBLIC_S3_PROXY is enabled, this serves files that would normally
     * be accessed directly from S3 (images, logos, avatars, etc.), allowing
     * a fully private S3 bucket setup.
     */
    public function show(string $path): Response|StreamedResponse
    {
        if ($this->hasPathTraversalSegments($path)) {
            abort(404);
        }

        $disk = Storage::disk('public');

        // The S3 adapter includes the disk's root prefix in generated URLs,
        // but Flysystem also prepends it internally on every operation.
        // Strip it here to avoid double-prefixing.
        $root = trim(config('filesystems.disks.public.root', ''), '/');
        if ($root !== '' && str_starts_with($path, $root.'/')) {
            $path = substr($path, strlen($root) + 1);
        }

        if (! $disk->exists($path)) {
            abort(404);
        }

        $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';
        $lastModified = $disk->lastModified($path);
        $etag = md5($path.$lastModified);
        $size = $disk->size($path);

        if ($this->isNotModified($etag, $lastModified)) {
            return response('', 304)
                ->header('ETag', '"'.$etag.'"')
                ->header('Cache-Control', 'public, max-age=86400');
        }

        return new StreamedResponse(function () use ($disk, $path) {
            $stream = $disk->readStream($path);
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Length' => $size,
            'ETag' => '"'.$etag.'"',
            'Last-Modified' => gmdate('D, d M Y H:i:s', $lastModified).' GMT',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function isNotModified(string $etag, int $lastModified): bool
    {
        $requestEtag = request()->header('If-None-Match');
        if ($requestEtag && $requestEtag === '"'.$etag.'"') {
            return true;
        }

        $ifModifiedSince = request()->header('If-Modified-Since');
        if ($ifModifiedSince && strtotime($ifModifiedSince) >= $lastModified) {
            return true;
        }

        return false;
    }

    private function hasPathTraversalSegments(string $path): bool
    {
        $normalizedPath = str_replace('\\', '/', $path);

        return str_contains($normalizedPath, "\0")
            || str_starts_with($normalizedPath, '/')
            || str_contains($normalizedPath, '../')
            || str_contains($normalizedPath, '/..')
            || str_ends_with($normalizedPath, '/..')
            || $normalizedPath === '..';
    }
}
