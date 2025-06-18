<?php

namespace App\Http\Controllers\Api;

use App\Helpers\StorageHelper;
use Illuminate\Support\Facades\Storage;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\AssetModel;
use App\Models\Actionlog;
use App\Http\Requests\UploadFileRequest;
use App\Http\Transformers\AssetModelsTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


/**
 * This class controls file related actions related
 * to assets for the Snipe-IT Asset Management application.
 *
 * Based on the Assets/AssetFilesController by A. Gianotto <snipe@snipe.net>
 *
 * @version    v1.0
 * @author [T. Scarsbrook] [<snipe@scarzybrook.co.uk>]
 */
class AssetModelFilesController extends Controller
{
    /**
     * Accepts a POST to upload a file to the server.
     *
     * @param \App\Http\Requests\UploadFileRequest $request
     * @param int $assetModelId
     * @since [v7.0.12]
     * @author [r-xyz]
     */
    public function store(UploadFileRequest $request, $assetModelId = null) : JsonResponse
    {
        // Start by checking if the asset being acted upon exists
        if (! $assetModel = AssetModel::find($assetModelId)) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/models/message.does_not_exist')), 404);
        }

        // Make sure we are allowed to update this asset
        $this->authorize('update', $assetModel);

            if ($request->hasFile('file')) {
            // If the file storage directory doesn't exist; create it
            if (! Storage::exists('private_uploads/assetmodels')) {
                Storage::makeDirectory('private_uploads/assetmodels', 775);
            }

            // Loop over the attached files and add them to the asset
            foreach ($request->file('file') as $file) {
                $file_name = $request->handleFile('private_uploads/assetmodels/','model-'.$assetModel->id, $file);
                
                $assetModel->logUpload($file_name, e($request->get('notes')));
            }

            // All done - report success
            return response()->json(Helper::formatStandardApiResponse('success', $assetModel, trans('admin/models/message.upload.success')));
        }

        // We only reach here if no files were included in the POST, so tell the user this
        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/models/message.upload.nofiles')), 500);
    }

    /**
     * List the files for an asset.
     *
     * @param  int $assetmodel
     * @since [v7.0.12]
     * @author [r-xyz]
     */
    public function list($assetmodel_id) : JsonResponse | array
    {
        // Start by checking if the asset being acted upon exists
        if (! $assetModel = AssetModel::find($assetmodel_id)) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/models/message.does_not_exist')), 404);
        }

            $assetmodel = AssetModel::with('uploads')->find($assetmodel_id);
            $this->authorize('view', $assetmodel);
            return (new AssetModelsTransformer)->transformAssetModelFiles($assetmodel, $assetmodel->uploads()->count());
    }

    /**
     * Check for permissions and display the file.
     *
     * @param  int $assetModelId
     * @param  int $fileId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @since [v7.0.12]
     * @author [r-xyz]
     */
    public function show($assetModelId = null, $fileId = null) : JsonResponse | StreamedResponse | Storage | StorageHelper | BinaryFileResponse
    {
        // Start by checking if the asset being acted upon exists
        if (! $assetModel = AssetModel::find($assetModelId)) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/models/message.does_not_exist')), 404);
        }

        // the asset is valid
        if (isset($assetModel->id)) {
            $this->authorize('view', $assetModel);

            // Check that the file being requested exists for the asset
            if (! $log = Actionlog::whereNotNull('filename')->where('item_id', $assetModel->id)->find($fileId)) {
                return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/models/message.download.no_match', ['id' => $fileId])), 404);
            }

            // Form the full filename with path
            $file = 'private_uploads/assetmodels/'.$log->filename;
            Log::debug('Checking for '.$file);

            if ($log->action_type == 'audit') {
                $file = 'private_uploads/audits/'.$log->filename;
            }

            // Check the file actually exists on the filesystem
            if (! Storage::exists($file)) {
                return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/models/message.download.does_not_exist', ['id' => $fileId])), 404);
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
        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/models/message.download.error', ['id' => $fileId])), 500);
    }

    /**
     * Delete the associated file
     *
     * @param  int $assetModelId
     * @param  int $fileId
     * @since [v7.0.12]
     * @author [r-xyz]
     */
    public function destroy($assetModelId = null, $fileId = null) : JsonResponse
    {
        // Start by checking if the asset being acted upon exists
        if (! $assetModel = AssetModel::find($assetModelId)) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/models/message.does_not_exist')), 404);
        }

        $rel_path = 'private_uploads/assetmodels';

        // the asset is valid
        if (isset($assetModel->id)) {
            $this->authorize('update', $assetModel);

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
                return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/models/message.deletefile.success')), 200);
            }

            // The file doesn't seem to really exist, so report an error
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/models/message.deletefile.error')), 500);
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/models/message.deletefile.error')), 500);
    }
}
