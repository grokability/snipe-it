<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
class StorageHelper
{
    public static function downloader($filename, $disk = 'default') : BinaryFileResponse | RedirectResponse | StreamedResponse
    {
        if ($disk == 'default') {
            $disk = config('filesystems.default');
        }
        switch (config("filesystems.disks.$disk.driver")) {
        case 'local':
            return response()->download(Storage::disk($disk)->path($filename)); //works for PRIVATE or public?!

        case 's3':
            return redirect()->away(Storage::disk($disk)->temporaryUrl($filename, now()->addMinutes(5))); //works for private or public, I guess?

        default:
            return Storage::disk($disk)->download($filename);
        }
    }

    public static function getMediaType($file_with_path) {

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
     * @since  v7.0.14
     * @param  $file_with_path
     * @return bool
     */
    public static function allowSafeInline($file_with_path)
    {

        $allowed_inline = [
            'avif',
            'gif',
            'gif',
            'jpg',
            'mov',
            'mp3',
            'mp4',
            'ogg',
            'pdf',
            'png',
            'svg',
            'wav',
            'webm',
            'webp',
        ];


        // The file exists and is allowed to be displayed inline
        if (Storage::exists($file_with_path) && (in_array(pathinfo($file_with_path, PATHINFO_EXTENSION), $allowed_inline))) {
            return true;
        }
        return false;

    }

    public static function getFiletype($file_with_path)
    {

        // The file exists and is allowed to be displayed inline
        if (Storage::exists($file_with_path)) {
            return pathinfo($file_with_path, PATHINFO_EXTENSION);
        }

        return null;

    }


    /**
     * Decide whether to show the file inline or download it.
     */
    public static function showOrDownloadFile($file, $filename)
    {

        $headers = [];

        if (request('inline') == 'true') {

            $headers = [
                'Content-Disposition' => 'inline',
            ];

            // This is NOT allowed as inline - force it to be displayed as text in the browser
            if (self::allowSafeInline($file) != true) {
                $headers = array_merge($headers, ['Content-Type' => 'text/plain']);
            }
        }

        // Everything else seems okay, but the file doesn't exist on the server.
        if (Storage::missing($file)) {
            throw new FileNotFoundException();
        }

        return Storage::download($file, $filename, $headers);

    }
}
