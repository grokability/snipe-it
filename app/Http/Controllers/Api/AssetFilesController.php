<?php

namespace App\Http\Controllers\Api;

use App\Helpers\StorageHelper;
use App\Http\Transformers\UploadedFilesTransformer;
use Illuminate\Support\Facades\Storage;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Actionlog;
use App\Http\Requests\UploadFileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Http\Request;


/**
 * This class controls file related actions related
 * to assets for the Snipe-IT Asset Management application.
 *
 * Based on the Assets/AssetFilesController by A. Gianotto <snipe@snipe.net>
 *
 * @version    v1.0
 * @author [T. Scarsbrook] [<snipe@scarzybrook.co.uk>]
 */
class AssetFilesController extends Controller
{
    /**
     * Accepts a POST to upload a file to the server.
     *
     * @param \App\Http\Requests\UploadFileRequest $request
     * @param int $assetId
     * @since [v6.0]
     * @author [T. Scarsbrook] [<snipe@scarzybrook.co.uk>]
     */
    public function store(UploadFileRequest $request, $assetId = null) : JsonResponse
    {
        // Start by checking if the asset being acted upon exists
        if (! $asset = Asset::find($assetId)) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/hardware/message.does_not_exist')), 404);
        }

        // Make sure we are allowed to update this asset
        $this->authorize('update', $asset);

	    if ($request->hasFile('file')) {
            // If the file storage directory doesn't exist; create it
            if (! Storage::exists('private_uploads/assets')) {
                Storage::makeDirectory('private_uploads/assets', 775);
            }

            // Loop over the attached files and add them to the asset
            foreach ($request->file('file') as $file) {
                $file_name = $request->handleFile('private_uploads/assets/','hardware-'.$asset->id, $file);
                
                $asset->logUpload($file_name, e($request->get('notes')));
            }

            // All done - report success
            return response()->json(Helper::formatStandardApiResponse('success', $asset, trans('admin/hardware/message.upload.success')));
        }

        // We only reach here if no files were included in the POST, so tell the user this
        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/hardware/message.upload.nofiles')), 500);
    }

    /**
     * List the files for an asset.
     *
     * @param  int $assetId
     * @since [v6.0]
     * @author [T. Scarsbrook] [<snipe@scarzybrook.co.uk>]
     */
    public function list(Asset $asset, Request $request) : JsonResponse | array
    {

        $this->authorize('view', $asset);

        $allowed_columns =
            [
                'id',
                'filename',
                'eol',
                'notes',
                'created_at',
                'updated_at',
            ];

        $files = Actionlog::select('action_logs.*')->where('action_type', '=', 'uploaded')->where('item_type', '=', Asset::class)->where('item_id', '=', $asset->id);

        if ($request->filled('search')) {
            $files = $files->TextSearch($request->input('search'));
        }

        // Make sure the offset and limit are actually integers and do not exceed system limits
        $offset = ($request->input('offset') > $files->count()) ? $files->count() : abs($request->input('offset'));
        $limit = app('api_limit_value');
        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';
        $files = $files->orderBy($sort, $order);

        $files = $files->skip($offset)->take($limit)->get();
        return (new UploadedFilesTransformer())->transformFiles($files, $files->count());

    }

    /**
     * Check for permissions and display the file.
     *
     * @param  int $assetId
     * @param  int $fileId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @since [v6.0]
     * @author [T. Scarsbrook] [<snipe@scarzybrook.co.uk>]
     */
    public function show(Asset $asset, $fileId = null) : JsonResponse | StreamedResponse | Storage | StorageHelper | BinaryFileResponse
    {

        // the asset is valid
        if (isset($asset->id)) {
            $this->authorize('view', $asset);

            // Check that the file being requested exists for the asset
            if (! $log = Actionlog::whereNotNull('filename')->where('item_id', $asset->id)->find($fileId)) {
                return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/hardware/message.download.no_match', ['id' => $fileId])), 404);
            }

	    // Form the full filename with path
            $file = 'private_uploads/assets/'.$log->filename;
            Log::debug('Checking for '.$file);

            if ($log->action_type == 'audit') {
                $file = 'private_uploads/audits/'.$log->filename;
            }

            // Check the file actually exists on the filesystem
            if (! Storage::exists($file)) {
                return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/hardware/message.download.does_not_exist', ['id' => $fileId])), 404);
            }

            if (request('inline') == 'true') {

                $headers = [
                    'Content-Disposition' => 'inline',
                ];

                return Storage::download($file, $log->filename, $headers);
            }

            return StorageHelper::downloader($file);
        }

        // Send back an error message
        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/hardware/message.download.error', ['id' => $fileId])), 500);
    }

    /**
     * Delete the associated file
     *
     * @param  int $assetId
     * @param  int $fileId
     * @since [v6.0]
     * @author [T. Scarsbrook] [<snipe@scarzybrook.co.uk>]
     */
    public function destroy(Asset $asset, $fileId = null) : JsonResponse
    {

        $rel_path = 'private_uploads/assets';

        // the asset is valid
        if (isset($asset->id)) {
            $this->authorize('update', $asset);

            // Check for the file
            $log = Actionlog::find($fileId);

                if ($log) {
                        // Check the file actually exists, and delete it
                        if (Storage::exists($rel_path.'/'.$log->filename)) {
                            Storage::delete($rel_path.'/'.$log->filename);
                }

		        // Delete the record of the file
                $log->delete();

                // All deleting done - notify the user of success
                return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/hardware/message.deletefile.success')), 200);
            }

            // The file doesn't seem to really exist, so report an error
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/hardware/message.deletefile.error')), 500);
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/hardware/message.deletefile.error')), 500);
    }
}
