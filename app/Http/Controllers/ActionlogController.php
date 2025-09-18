<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Actionlog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use \Illuminate\Http\Response;

class ActionlogController extends Controller
{
    public function displaySig($filename) : RedirectResponse | Response | bool
    {
        // PHP doesn't let you handle file not found errors well with 
        // file_get_contents, so we set the error reporting for just this class
        error_reporting(0);

        $disk = config('filesystems.default');
        switch (config("filesystems.disks.$disk.driver")) {

            case 's3':
                $file = 'private_uploads/signatures/'.$filename;
                return redirect()->away(Storage::disk($disk)->temporaryUrl($file, now()->addMinutes(5)));
            default:
                $this->authorize('view', \App\Models\Asset::class);
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

    public function getStoredEula($filename) : Response | BinaryFileResponse | RedirectResponse
    {

        if ($actionlog = Actionlog::where('filename', $filename)->with('user')->with('target')->firstOrFail()) {

            $this->authorize('view', $actionlog->target);
            $this->authorize('view', $actionlog->user);


            if (config('filesystems.default') == 's3_private') {
                return redirect()->away(Storage::disk('s3_private')->temporaryUrl('private_uploads/eula-pdfs/' . $filename, now()->addMinutes(5)));
            }

            if (Storage::exists('private_uploads/eula-pdfs/' . $filename)) {

                if (request()->input('inline') == 'true') {
                    return response()->file(config('app.private_uploads') . '/eula-pdfs/' . $filename);
                }

                return response()->download(config('app.private_uploads') . '/eula-pdfs/' . $filename);
            }

            return redirect()->back()->with('error', trans('general.file_does_not_exist'));
        }

        return redirect()->back()->with('error', trans('general.record_not_found'));
    }
}
