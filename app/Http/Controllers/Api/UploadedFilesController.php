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
     * @param  \App\Http\Requests\UploadFileRequest $request
     * @param  string                               $object_type the type of object to upload the file to
     * @param  int                                  $id          the ID of the object to list files for
     * @since  [v8.1.17]
     * @author [A. Gianotto <snipe@snipe.net>]
     */
    public function index(Request $request, $object_type, $id) : JsonResponse | array
    {

        // Check the permissions to make sure the user can view the object
        $object = self::$map_object_type[$object_type]::withTrashed()->find($id);
        $this->authorize('view', $object);

        if (!$object) {
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


        $uploads = self::$map_object_type[$object_type]::withTrashed()->find($id)->uploads()
            ->with('adminuser');

        $offset = ($request->input('offset') > $uploads->count()) ? $uploads->count() : abs($request->input('offset'));
        $limit = app('api_limit_value');
        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';

        // Text search on action_logs fields
        // We could use the normal Actionlogs text scope, but it's a very heavy query since it's searching across all relations
        // and we generally won't need that here
        if ($request->filled('search')) {

            $uploads->where(
                function ($query) use ($request) {
                    $query->where('filename', 'LIKE', '%' . $request->input('search') . '%')
                        ->orWhere('note', 'LIKE', '%' . $request->input('search') . '%');
                }
            );
        }

        $total = $uploads->count();
        $uploads = $uploads->skip($offset)->take($limit)->orderBy($sort, $order)->get();

        return (new UploadedFilesTransformer())->transformFiles($uploads, $total);
    }


    /**
     * Accepts a POST to upload a file to the server.
     *
     * @param  \App\Http\Requests\UploadFileRequest $request
     * @param  string                               $object_type the type of object to upload the file to
     * @param  int                                  $id          the ID of the object to store so we can check permisisons
     * @since  [v8.1.17]
     * @author [A. Gianotto <snipe@snipe.net>]
     */
    public function store(UploadFileRequest $request, $object_type, $id) : JsonResponse
    {

        // Check the permissions to make sure the user can view the object
        $object = self::$map_object_type[$object_type]::withTrashed()->find($id);
        $this->authorize('view', $object);

        if (!$object) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.file_upload_status.invalid_object')));
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

            return response()->json(Helper::formatStandardApiResponse('success', (new UploadedFilesTransformer())->transformFiles($files, count($files)), trans_choice('general.file_upload_status.upload.success',  count($files))));
        }

        // No files were submitted
        return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.file_upload_status.nofiles')));
    }



    /**
     * Check for permissions and display the file.
     *
     * @param  \App\Http\Requests\UploadFileRequest $request
     * @param  string                               $object_type the type of object to upload the file to
     * @param  int                                  $id          the ID of the object to delete from so we can check permisisons
     * @param  $file_id     the ID of the file to delete from the action_logs table
     * @since  [v8.1.17]
     * @author [A. Gianotto <snipe@snipe.net>]
     */
    public function show($object_type, $id, $file_id) : JsonResponse | StreamedResponse | Storage | StorageHelper | BinaryFileResponse
    {
        // Check the permissions to make sure the user can view the object
        $object = self::$map_object_type[$object_type]::withTrashed()->find($id);
        $this->authorize('view', $object);

        if (!$object) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.file_upload_status.invalid_object')));
        }


        // Check that the file being requested exists for the object
        if (! $log = Actionlog::whereNotNull('filename')->where('item_type', self::$map_object_type[$object_type])->where('item_id', $object->id)->find($file_id)
        ) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.file_upload_status.invalid_id')), 200);
        }


        if (! Storage::exists(self::$map_storage_path[$object_type].'/'.$log->filename)) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.file_upload_status.file_not_found'), 200));
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
     * @since  [v8.1.17]
     * @author [A. Gianotto <snipe@snipe.net>]
     */
    public function destroy($object_type, $id, $file_id) : JsonResponse
    {

        // Check the permissions to make sure the user can view the object
        $object = self::$map_object_type[$object_type]::withTrashed()->find($id);
        $this->authorize('update', self::$map_object_type[$object_type]);

        if (!$object) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.file_upload_status.invalid_object')));
        }


        // Check for the file
        $log = Actionlog::query()
            ->where('id', $file_id)
            ->where('action_type', 'uploaded')
            ->where('item_type', self::$map_object_type[$object_type])
            ->where('item_id', $object->id)
            ->first();

        if ($log) {
            // Check the file actually exists, and delete it
            if (Storage::exists(self::$map_storage_path[$object_type].'/'.$log->filename)) {
                Storage::delete(self::$map_storage_path[$object_type].'/'.$log->filename);
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
