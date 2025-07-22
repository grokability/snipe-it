<?php

namespace App\Http\Controllers;

use App\Helpers\StorageHelper;
use App\Http\Requests\UploadFileRequest;
use App\Models\Actionlog;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use \Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LocationsFilesController extends Controller
{
    /**
     * Upload a file to the server.
     *
     * @param UploadFileRequest $request
     * @param int $modelId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *@since [v1.0]
     * @author [A. Gianotto] [<snipe@snipe.net>]
     */
    public function store(UploadFileRequest $request, Location $location) : RedirectResponse
    {
        $this->authorize('update', $location);

        if ($request->hasFile('file')) {

            if (! Storage::exists('private_uploads/locations')) {
                Storage::makeDirectory('private_uploads/locations', 775);
            }

            foreach ($request->file('file') as $file) {
                $file_name = $request->handleFile('private_uploads/locations/','location-'.$location->id, $file);
                $location->logUpload($file_name, $request->get('notes'));
            }

            return redirect()->back()->withFragment('files')->with('success', trans('general.file_upload_success'));
        }

        return redirect()->back()->withFragment('files')->with('error', trans('admin/hardware/message.upload.nofiles'));
    }

    /**
     * Check for permissions and display the file.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param  int $modelId
     * @param  int $fileId
     * @since [v1.0]
     */
    public function show(Location $location, $fileId = null) : StreamedResponse | Response | RedirectResponse | BinaryFileResponse
    {

        $this->authorize('view', $location);

        if (! $log = Actionlog::find($fileId)) {
            return redirect()->back()->withFragment('files')->with('error', 'No matching file record');
        }

        $file = 'private_uploads/locations/'.$log->filename;

        if (! Storage::exists($file)) {
            return redirect()->back()->withFragment('files')->with('error', 'No matching file on server');
        }

        if (request('inline') == 'true') {

            $headers = [
                'Content-Disposition' => 'inline',
            ];

            return Storage::download($file, $log->filename, $headers);
        }

        return StorageHelper::downloader($file);
    }

    /**
     * Delete the associated file
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param  int $modelId
     * @param  int $fileId
     * @since [v1.0]
     */
    public function destroy(Location $location, $fileId = null) : RedirectResponse
    {
        $rel_path = 'private_uploads/locations';
        $this->authorize('update', $location);
        $log = Actionlog::find($fileId);

        if ($log) {

            // This should be moved to purge
//            if (Storage::exists($rel_path.'/'.$log->filename)) {
//                Storage::delete($rel_path.'/'.$log->filename);
//            }
            $log->delete();

            return redirect()->back()->withFragment('files')->with('success', trans('admin/hardware/message.deletefile.success'));
        }

        return redirect()->back()->withFragment('files')->with('success', trans('admin/hardware/message.deletefile.success'));

    }
}
