<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\UploadFileRequest;
use App\Http\Transformers\UploadedFilesTransformer;
use App\Models\Actionlog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UploadedFilesController extends Controller
{
    /**
     * List files for an object
     *
     * @param  UploadFileRequest  $request
     * @param  string  $object_type  the type of object to upload the file to
     * @param  int  $id  the ID of the object to list files for
     *
     * @since  [v8.1.17]
     *
     * @author [A. Gianotto <snipe@snipe.net>]
     */
    public function index(Request $request, $object_type, $id): JsonResponse|array
    {

        // Check the permissions to make sure the user can view the object
        $object = parent::getMapObjectType()[$object_type]::withTrashed()->find($id);
        $this->authorize('files', $object);

        if (! $object) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.file_upload_status.invalid_object')));
        }

        // Columns allowed for sorting
        $allowed_columns =
            [
                'id',
                'filename',
                'action_type',
                'action_date',
                'note',
                'created_at',
            ];

        $uploads = parent::getMapObjectType()[$object_type]::withTrashed()->find($id)->uploads()
            ->with('adminuser');

        $limit = app('api_limit_value');
        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';

        // Text search on action_logs fields
        // We could use the normal Actionlogs text scope, but it's a very heavy query since it's searching across all relations
        // and we generally won't need that here
        if ($request->filled('search')) {

            $uploads->where(
                function ($query) use ($request) {
                    $query->where('filename', 'LIKE', '%'.$request->input('search').'%')
                        ->orWhere('note', 'LIKE', '%'.$request->input('search').'%');
                }
            );
        }

        // $total must be computed after the search where() block so both
        // the offset clamp and the response total reflect the filtered
        // set. Previously the offset ran on the unfiltered count, which
        // could over-clamp when a search narrowed results.
        $total = $uploads->count();
        // Secondary sort by id so files uploaded within the same second (a
        // common case in tests and bulk uploads) return in a stable order
        // rather than whatever the engine happens to pick.
        $offset = ($request->input('offset') > $total) ? $total : app('api_offset_value');
        $uploads = $uploads->skip($offset)->take($limit)->orderBy($sort, $order)->orderBy('id', $order)->get();

        return (new UploadedFilesTransformer)->transformFiles($uploads, $total);
    }

    /**
     * Accepts a POST to upload a file to the server.
     *
     * @param  string  $object_type  the type of object to upload the file to
     * @param  int  $id  the ID of the object to store so we can check permisisons
     *
     * @since  [v8.1.17]
     *
     * @author [A. Gianotto <snipe@snipe.net>]
     */
    public function store(UploadFileRequest $request, $object_type, $id): JsonResponse
    {

        // Check the permissions to make sure the user is allowed to upload
        // to the object. `manageFiles` is stricter than `files` (used by
        // index/show below) so a read-only cascade like the one on
        // AssetModelPolicy does not accidentally grant write access.
        $object = parent::getMapObjectType()[$object_type]::withTrashed()->find($id);
        $this->authorize('manageFiles', $object);

        if (! $object) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.file_upload_status.invalid_object')));
        }

        if ($request->hasFile('file')) {
            // Loop over the attached files and add them to the object
            foreach ($request->file('file') as $file) {
                $file_name = $request->handleFile(parent::getMapStoragePath()[$object_type], parent::getMapFilePrefix()[$object_type].'-'.$object->id, $file);
                $files[] = $file_name;
                $object->logUpload($file_name, $request->input('notes'));
            }

            if (isset($files)) {
                $file_results = Actionlog::select('action_logs.*')->where('action_type', '=', 'uploaded')
                    ->where('item_type', '=', parent::getMapObjectType()[$object_type])
                    ->where('item_id', '=', $id)->whereIn('filename', $files)
                    ->get();

                return response()->json(Helper::formatStandardApiResponse('success', (new UploadedFilesTransformer)->transformFiles($file_results, count($file_results)), trans_choice('general.file_upload_status.upload.success', count($files))));
            }

        }

        // No files were submitted
        return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.file_upload_status.nofiles')));
    }

    /**
     * Check for permissions and display the file.
     *
     * @param  string  $object_type  the type of object to upload the file to
     * @param  int  $id  the ID of the object to delete from so we can check permisisons
     * @param  $file_id  the ID of the file to delete from the action_logs table
     *
     * @since  [v8.1.17]
     *
     * @author [A. Gianotto <snipe@snipe.net>]
     */
    public function show($object_type, $id, $file_id): JsonResponse|StreamedResponse|Storage|StorageHelper|BinaryFileResponse
    {
        // Check the permissions to make sure the user can view the object
        $object = parent::getMapObjectType()[$object_type]::withTrashed()->find($id);
        $this->authorize('files', $object);

        if (! $object) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.file_upload_status.invalid_object')));
        }

        // Check that the file being requested exists for the object
        if (! $log = Actionlog::whereNotNull('filename')->where('item_type', parent::getMapObjectType()[$object_type])->where('item_id', $object->id)->find($file_id)
        ) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.file_upload_status.invalid_id')), 200);
        }

        if (! Storage::exists(parent::getMapStoragePath()[$object_type].$log->filename)) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.file_upload_status.file_not_found'), 200));
        }

        if (request('inline') == 'true') {
            $path = parent::getMapStoragePath()[$object_type];

            // Only allowlisted extensions may be served inline. Everything
            // else (including XML, which can pull an XSLT stylesheet and
            // execute script in-origin) falls through to a download response.
            if (! StorageHelper::allowSafeInline($path.$log->filename)) {
                return StorageHelper::downloader($path.$log->filename);
            }

            return Storage::download($path.$log->filename, $log->filename, [
                'Content-Disposition' => 'inline',
                'X-Content-Type-Options' => 'nosniff',
            ]);
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
     * @since  [v8.1.17]
     *
     * @author [A. Gianotto <snipe@snipe.net>]
     */
    public function destroy($object_type, $id, $file_id): JsonResponse
    {

        // See store(): `manageFiles` is the strict write ability so a
        // read-only cascade in files() does not authorize deletion.
        $object = parent::getMapObjectType()[$object_type]::withTrashed()->find($id);
        $this->authorize('manageFiles', $object);

        if (! $object) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.file_upload_status.invalid_object')));
        }

        // Check for the file
        $log = Actionlog::query()
            ->where('id', $file_id)
            ->where('action_type', 'uploaded')
            ->where('item_type', parent::getMapObjectType()[$object_type])
            ->where('item_id', $object->id)
            ->first();

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

                    return response()->json(Helper::formatStandardApiResponse('error', null, trans_choice('general.file_upload_status.delete.error', 1)), 500);
                }
            }
            // Delete the record of the file
            if ($log->logUploadDelete($object, $log->filename)) {
                return response()->json(Helper::formatStandardApiResponse('success', null, trans_choice('general.file_upload_status.delete.success', 1)), 200);
            }

        }

        // The file doesn't seem to really exist, so report an error
        return response()->json(Helper::formatStandardApiResponse('error', null, trans_choice('general.file_upload_status.delete.error', 1)), 500);

    }
}
