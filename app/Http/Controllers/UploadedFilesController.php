<?php

namespace App\Http\Controllers;

use App\Enums\FileStorage;
use App\Helpers\StorageHelper;
use App\Http\Requests\UploadFileRequest;
use App\Models\Actionlog;
use App\Models\Import;
use Illuminate\Http\JsonResponse;
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
 * @return JsonResponse
 */
class UploadedFilesController extends Controller
{
    /**
     * Accepts a POST to upload a file to the server.
     *
     * @param  string  $object_type  the type of object to upload the file to
     * @param  int  $id  the ID of the object to store so we can check permisisons
     *
     * @since  [v8.2.2]
     *
     * @author [A. Gianotto <snipe@snipe.net>]
     */
    public function store(UploadFileRequest $request, $object_type, $id): RedirectResponse
    {

        // Check the permissions to make sure the user is allowed to upload
        // to the object. `manageFiles` is stricter than `files` (used by
        // show/download) so a read-only cascade like the one on
        // AssetModelPolicy does not accidentally grant write access.
        $object = parent::getMapObjectType()[$object_type]::withTrashed()->find($id);
        $this->authorize('manageFiles', $object);

        if (! $object) {
            return redirect()->back()->withFragment('files')->with('error', trans('general.file_upload_status.invalid_object'));
        }

        if ($request->hasFile('file')) {
            // Loop over the attached files and add them to the object
            foreach ($request->file('file') as $file) {
                $file_name = $request->handleFile(parent::getMapStoragePath()[$object_type], parent::getMapFilePrefix()[$object_type].'-'.$object->id, $file);
                $files[] = $file_name;
                $object->logUpload($file_name, $request->input('notes'));
            }

            $files = Actionlog::select('action_logs.*')->where('action_type', '=', 'uploaded')
                ->where('item_type', '=', parent::getMapObjectType()[$object_type])
                ->where('item_id', '=', $id)->whereIn('filename', $files)
                ->get();

            return redirect()->back()->withFragment('files')->with('success', trans_choice('general.file_upload_status.upload.success', count($files)));
        }

        // No files were submitted
        return redirect()->back()->withFragment('files')->with('error', trans('general.file_upload_status.nofiles'));
    }

    /**
     * Check for permissions and display the file.
     * This isn't currently used, but is here for future use.
     *
     * @param  string  $object_type  the type of object to upload the file to
     * @param  int  $id  the ID of the object to delete from so we can check permisisons
     * @param  $file_id  the ID of the file to show from the action_logs table
     *
     * @since  [v8.2.2]
     *
     * @author [A. Gianotto <snipe@snipe.net>]
     */
    public function show($object_type, $id, $file_id): RedirectResponse|StreamedResponse|Storage|StorageHelper|BinaryFileResponse
    {
        // Check the permissions to make sure the user can view the object
        $object = parent::getMapObjectType()[$object_type]::withTrashed()->find($id);
        $this->authorize('files', $object);

        if (! $object) {
            return redirect()->back()->withFragment('files')->with('error', trans('general.file_upload_status.invalid_object'));
        }

        // Check that the file being requested exists for the object
        if (! $log = Actionlog::whereNotNull('filename')->where('item_type', parent::getMapObjectType()[$object_type])->where('item_id', $object->id)->find($file_id)) {
            return redirect()->back()->withFragment('files')->with('error', trans('general.file_upload_status.invalid_id'));
        }

        if (! Storage::exists(parent::getMapStoragePath()[$object_type].$log->filename)) {
            return redirect()->back()->withFragment('files')->with('error', trans('general.file_upload_status.file_not_found'));
        }

        if (request('inline') == 'true') {
            $path = parent::getMapStoragePath()[$object_type];

            if (! StorageHelper::allowSafeInline($path.$log->filename)) {
                return StorageHelper::downloader($path.$log->filename);
            }

            return Storage::download($path.$log->filename, $log->filename, ['Content-Disposition' => 'inline']);
        }

        return StorageHelper::downloader(parent::getMapStoragePath()[$object_type].$log->filename);

    }

    /**
     * Delete the associated file
     *
     * @param  string  $object_type  the type of object to upload the file to
     * @param  int  $id  the ID of the object to delete from so we can check permisisons
     * @param  $file_id  the ID of the file to delete from the action_logs table
     *
     * @since  [v8.2.2]
     *
     * @author [A. Gianotto <snipe@snipe.net>]
     */
    public function destroy($object_type, $id, $file_id): RedirectResponse
    {

        // See store(): `manageFiles` is the strict write ability so a
        // read-only cascade in files() does not authorize deletion.
        $object = parent::getMapObjectType()[$object_type]::withTrashed()->find($id);
        $this->authorize('manageFiles', $object);

        if (! $object) {
            return redirect()->back()->withFragment('files')->with('error', trans('general.file_upload_status.invalid_object'));
        }

        // Check for the file
        $log = Actionlog::where('id', $file_id)->where('item_type', parent::getMapObjectType()[$object_type])
            ->where('item_id', $object->id)->first();

        if ($log) {
            // Check the file actually exists, and delete it.
            //
            // Storage::delete returns false on silent delete failures on
            // non-throwing filesystem drivers. Ignoring the return let a
            // failed physical delete produce an "upload deleted" action-log
            // entry, which HasUploads::uploads uses to exclude the row from
            // normal listings. Net effect: bytes still on disk, action log
            // shows the file as deleted, admin sees a success response, and
            // the file is invisible through the ordinary UI. Refuse to log
            // the deletion when the physical delete did not succeed.
            // Reported by Christopher Finks (christopherfi-dev) on
            // 2026-08-02.
            if (Storage::exists(parent::getMapStoragePath()[$object_type].$log->filename)) {
                if (! Storage::delete(parent::getMapStoragePath()[$object_type].$log->filename)) {
                    \Log::warning('File storage delete failed for '.$log->filename.' on '.parent::getMapObjectType()[$object_type].' id '.$id);

                    return redirect()->back()->withFragment('files')->with('error', trans_choice('general.file_upload_status.delete.error', 1));
                }
            }
            // Delete the record of the file
            if ($log->logUploadDelete($object, $log->filename)) {
                return redirect()->back()->withFragment('files')->with('success', trans_choice('general.file_upload_status.delete.success', 1));
            }

        }

        // The file doesn't seem to really exist, so report an error
        return redirect()->back()->withFragment('files')->with('error', trans_choice('general.file_upload_status.delete.error', 1));

    }

    public function downloadImport(Import $import)
    {

        $this->authorize('import');

        if ($import = Import::find($import->id)) {

            if ((auth()->user()->id != $import->created_by) && (! auth()->user()->isSuperUser())) {
                return redirect()->back()->with('error', trans('general.file_upload_status.file_not_found'));
            }

            $storedPath = FileStorage::Imports->privateStorageKey().$import->file_path;
            if (! Storage::exists($storedPath)) {
                return redirect()->back()->with('error', trans('general.file_upload_status.file_not_found'));
            }

            $defaultDisk = config('filesystems.default');
            if (config("filesystems.disks.$defaultDisk.driver") === 's3') {
                return redirect()->away(Storage::temporaryUrl($storedPath, now()->addMinutes(5), [
                    'ResponseContentType' => 'application/octet-stream',
                    'ResponseContentDisposition' => 'attachment; filename="'.basename($import->file_path).'"',
                ]));
            }

            return response()->download(Storage::path($storedPath));

        }

        return redirect()->back()->with('error', trans('general.file_upload_status.file_not_found'));

    }
}
