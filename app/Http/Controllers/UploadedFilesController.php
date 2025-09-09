<?php

namespace App\Http\Controllers;

use App\Helpers\StorageHelper;
use App\Http\Requests\UploadFileRequest;
use App\Models\Actionlog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * This controller provide the health route  for
 * the Snipe-IT Asset Management application.
 *
 * @version   v1.0
 *
 * @return \Illuminate\Http\JsonResponse

 */
class UploadedFilesController extends Controller
{


    /**
     * Accepts a POST to upload a file to the server.
     *
     * @param  \App\Http\Requests\UploadFileRequest $request
     * @param  string                               $object_type the type of object to upload the file to
     * @param  int                                  $id          the ID of the object to store so we can check permisisons
     * @since  [v8.2.2]
     * @author [A. Gianotto <snipe@snipe.net>]
     */
    public function store(UploadFileRequest $request, $object_type, $id) : RedirectResponse
    {

        // Check the permissions to make sure the user can view the object
        $object = self::$map_object_type[$object_type]::withTrashed()->find($id);
        $this->authorize('update', $object);

        if (!$object) {
            return redirect()->back()->withFragment('files')->with('error',trans('general.file_upload_status.invalid_object'));
        }

        // If the file storage directory doesn't exist, create it
        if (! Storage::exists(self::$map_storage_path[$object_type])) {
            Storage::makeDirectory(self::$map_storage_path[$object_type], 775);
        }


        if ($request->hasFile('file')) {
            // Loop over the attached files and add them to the object
            foreach ($request->file('file') as $file) {
                $file_name = $request->handleFile(self::$map_storage_path[$object_type], self::$map_file_prefix[$object_type].'-'.$object->id, $file);
                $files[] = $file_name;
                $object->logUpload($file_name, $request->get('notes'));
            }

            $files = Actionlog::select('action_logs.*')->where('action_type', '=', 'uploaded')
                ->where('item_type', '=', self::$map_object_type[$object_type])
                ->where('item_id', '=', $id)->whereIn('filename', $files)
                ->get();

            return redirect()->back()->withFragment('files')->with('success', trans_choice('general.file_upload_status.upload.success',  count($files)));
        }

        // No files were submitted
        return redirect()->back()->withFragment('files')->with('error', trans('general.file_upload_status.nofiles'));
    }



    /**
     * Check for permissions and display the file.
     * This isn't currently used, but is here for future use.
     *
     * @param  \App\Http\Requests\UploadFileRequest $request
     * @param  string                               $object_type the type of object to upload the file to
     * @param  int                                  $id          the ID of the object to delete from so we can check permisisons
     * @param  $file_id     the ID of the file to show from the action_logs table
     * @since  [v8.2.2]
     * @author [A. Gianotto <snipe@snipe.net>]
     */
    public function show($object_type, $id, $file_id) : RedirectResponse | StreamedResponse | Storage | StorageHelper | BinaryFileResponse
    {
        // Check the permissions to make sure the user can view the object
        $object = self::$map_object_type[$object_type]::withTrashed()->find($id);
        $this->authorize('view', $object);

        if (!$object) {
            return redirect()->back()->withFragment('files')->with('error',trans('general.file_upload_status.invalid_object'));
        }


        // Check that the file being requested exists for the object
        if (! $log = Actionlog::whereNotNull('filename')->where('item_type', self::$map_object_type[$object_type])->where('item_id', $object->id)->find($file_id))
        {
            return redirect()->back()->withFragment('files')->with('error', trans('general.file_upload_status.invalid_id'));
        }


        if (! Storage::exists(self::$map_storage_path[$object_type].'/'.$log->filename))
        {
            return redirect()->back()->withFragment('files')->with('error', trans('general.file_upload_status.file_not_found'));
        }

        if (request('inline') == 'true') {
            $headers = [
                'Content-Disposition' => 'inline',
            ];
            return Storage::download(self::$map_storage_path[$object_type].'/'.$log->filename, $log->filename, $headers);
        }

        return StorageHelper::downloader(self::$map_storage_path[$object_type].'/'.$log->filename);

    }

    /**
     * Delete the associated file
     *
     * @param  \App\Http\Requests\UploadFileRequest $request
     * @param  string                               $object_type the type of object to upload the file to
     * @param  int                                  $id          the ID of the object to delete from so we can check permisisons
     * @param  $file_id     the ID of the file to delete from the action_logs table
     * @since  [v8.2.2]
     * @author [A. Gianotto <snipe@snipe.net>]
     */
    public function destroy($object_type, $id, $file_id) : RedirectResponse
    {

        // Check the permissions to make sure the user can view the object
        $object = self::$map_object_type[$object_type]::withTrashed()->find($id);
        $this->authorize('update', self::$map_object_type[$object_type]);

        if (!$object) {
            return redirect()->back()->withFragment('files')->with('error',trans('general.file_upload_status.invalid_object'));
        }


        // Check for the file
        $log = Actionlog::where('id',$file_id)->where('item_type', self::$map_object_type[$object_type])
            ->where('item_id', $object->id)->first();

        if ($log) {
            // Check the file actually exists, and delete it
            if (Storage::exists(self::$map_storage_path[$object_type].'/'.$log->filename)) {
                Storage::delete(self::$map_storage_path[$object_type].'/'.$log->filename);
            }
            // Delete the record of the file
            if ($log->logUploadDelete($object, $log->filename)) {
                return redirect()->back()->withFragment('files')->with('success', trans_choice('general.file_upload_status.delete.success', 1));
            }

        }

        // The file doesn't seem to really exist, so report an error
        return redirect()->back()->withFragment('files')->with('success', trans_choice('general.file_upload_status.delete.error', 1));

    }

}
