<?php

namespace App\Helpers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StorageHelper
{
    public static function downloader($filename, $disk = 'default'): BinaryFileResponse|RedirectResponse|StreamedResponse
    {
        if ($disk == 'default') {
            $disk = config('filesystems.default');
        }

        // Neutralize the response so a browser can't be tricked into treating
        // an uploaded file as active content: force a generic content type,
        // stop MIME sniffing, and keep the attachment disposition.
        $safeHeaders = [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ];

        switch (config("filesystems.disks.$disk.driver")) {
            case 'local':
                return response()->download(Storage::disk($disk)->path($filename), null, $safeHeaders);

            case 's3':
                Storage::disk($disk)->temporaryUrl(
                    $filename,
                    now()->addMinutes(5),
                    [
                        'ResponseContentType' => 'application/octet-stream',
                        'ResponseContentDisposition' => 'attachment; filename=download-file',
                    ]
                );

            default:
                return Storage::disk($disk)->download($filename, null, $safeHeaders);
        }
    }

    public static function getMediaType($file_with_path)
    {

        // Get the file extension and determine the media type
        if (Storage::exists($file_with_path)) {
            $fileinfo = pathinfo($file_with_path);
            $extension = strtolower($fileinfo['extension']);
            switch ($extension) {
                case 'avif':
                case 'jpg':
                case 'png':
                case 'gif':
                case 'svg':
                case 'webp':
                    return 'image';
                case 'pdf':
                    return 'pdf';
                case 'mp3':
                case 'wav':
                case 'ogg':
                    return 'audio';
                case 'mp4':
                case 'webm':
                case 'mov':
                    return 'video';
                case 'doc':
                case 'docx':
                    return 'document';
                case 'txt':
                    return 'text';
                case 'xls':
                case 'xlsx':
                case 'ods':
                    return 'spreadsheet';
                default:
                    return $extension; // Default for unknown types
            }
        }

        return null;
    }

    /**
     * This determines the file types that should be allowed inline and checks their fileinfo extension
     * to determine that they are safe to display inline.
     *
     * @author <A. Gianotto> [<snipe@snipe.net]>
     *
     * @since  v7.0.14
     *
     * @return bool
     */
    public static function allowSafeInline($file_with_path)
    {
        // Extension is the coarse gate; the server-detected MIME must also
        // land in the extension's allowed set (config/filesystems.php →
        // allowed_inline_display), so a .png that is actually XML/HTML/XSLT
        // can't ride the extension check into an inline response.
        $allowed_inline = config('filesystems.allowed_inline_display', []);

        if (! Storage::exists($file_with_path)) {
            return false;
        }

        $extension = strtolower(pathinfo($file_with_path, PATHINFO_EXTENSION));

        if (! isset($allowed_inline[$extension])) {
            return false;
        }

        try {
            $detected = Storage::mimeType($file_with_path);
        } catch (\Throwable) {
            return false;
        }

        return $detected && in_array($detected, $allowed_inline[$extension], true);
    }

    public static function getFiletype($file_with_path)
    {

        // The file exists and is allowed to be displayed inline
        if (Storage::exists($file_with_path)) {
            return pathinfo($file_with_path, PATHINFO_EXTENSION);
        }

        return null;

    }
}
