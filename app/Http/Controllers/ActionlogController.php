<?php

namespace App\Http\Controllers;

use App\Enums\FileStorage;
use App\Helpers\Helper;
use App\Models\Actionlog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ActionlogController extends Controller
{
    public function displaySig($filename): RedirectResponse|Response|bool
    {
        $filename = basename((string) $filename);

        $actionlog = Actionlog::where('accept_signature', $filename)->with('item')->firstOrFail();
        $this->authorize('view', $actionlog->item);

        // PHP doesn't let you handle file not found errors well with
        // file_get_contents, so we set the error reporting for just this class
        error_reporting(0);

        $disk = config('filesystems.default');
        switch (config("filesystems.disks.$disk.driver")) {

            case 's3':
                $file = FileStorage::Signatures->privateStorageKey().$filename;

                return redirect()->away(Storage::disk($disk)->temporaryUrl($file, now()->addMinutes(5)));
            default:
                $file = config('app.private_uploads').'/signatures/'.$filename;
                $filetype = Helper::checkUploadIsImage($file);

                $contents = file_get_contents($file, false, stream_context_create(['http' => ['ignore_errors' => true]]));
                if ($contents === false) {
                    Log::warning('File '.$file.' not found');

                    return false;
                } else {
                    return response()->make($contents)->header('Content-Type', $filetype);
                }
        }
    }

    public function getStoredEula($filename): Response|BinaryFileResponse|RedirectResponse
    {
        $filename = basename((string) $filename);

        $actionlog = Actionlog::where('filename', $filename)->with('user')->with('target')->firstOrFail();

        $this->authorize('view', $actionlog->target);
        $this->authorize('view', $actionlog->user);

        // Decide by the driver of the configured private disk, not by the name it was given.
        $disk = config('filesystems.default');
        $key = FileStorage::EulaPdfs->privateStorageKey().$filename;

        if (config("filesystems.disks.$disk.driver") === 's3') {
            return redirect()->away(Storage::disk($disk)->temporaryUrl($key, now()->addMinutes(5)));
        }

        if (Storage::disk($disk)->exists($key)) {
            $path = Storage::disk($disk)->path($key);

            if (request()->input('inline') == 'true') {
                return response()->file($path);
            }

            return response()->download($path);
        }

        return redirect()->back()->with('error', trans('general.file_does_not_exist'));
    }
}
