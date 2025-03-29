<?php

namespace App\Http\Controllers;

use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\UploadFileRequest;
use App\Models\Actionlog;
use App\Models\Location;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use enshrined\svgSanitize\Sanitizer;
use Illuminate\Support\Facades\Storage;

class LocationFilesController extends Controller
{
    /**
     * Return JSON response with a list of Location details for the getIndex() view.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v6.3]
     * @param UploadFileRequest $request
     * @param int $locationId
     * @return string JSON
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(UploadFileRequest $request, $locationId = null)
    {
        $location = Location::find($locationId);
        $destinationPath = config('app.private_uploads').'/locations';

        if (isset($location->id)) {
            $this->authorize('update', $location);

            $logActions = [];
            $files = $request->file('file');

            if (is_null($files)) {
                return redirect()->back()->with('error', trans('admin/locations/message.upload.nofiles'));
            }
            foreach ($files as $file) {
                
                $extension = $file->getClientOriginalExtension();
                $file_name = 'Location-'.$location->id.'-'.str_random(8).'-'.str_slug(basename($file->getClientOriginalName(), '.'.$extension)).'.'.$extension;


                    // Check for SVG and sanitize it
                    if ($extension == 'svg') {
                        \Log::debug('This is an SVG');
                        \Log::debug($file_name);

                            $sanitizer = new Sanitizer();

                            $dirtySVG = file_get_contents($file->getRealPath());
                            $cleanSVG = $sanitizer->sanitize($dirtySVG);

                            try {
                                Storage::put('private_uploads/locations/'.$file_name, $cleanSVG);
                            } catch (\Exception $e) {
                                \Log::debug('Upload no workie :( ');
                                \Log::debug($e);
                            }

                    } else {
                        Storage::put('private_uploads/locations/'.$file_name, file_get_contents($file));
                }

                //Log the uploaded file to the log
                $logAction = new Actionlog();
                $logAction->item_id = $location->id;
                $logAction->item_type = Location::class;
                $logAction->user_id = Auth::id();
                $logAction->note = $request->input('notes');
                $logAction->target_id = null;
                $logAction->created_at = date("Y-m-d H:i:s");
                $logAction->filename = $file_name;
                $logAction->action_type = 'uploaded';

                if (! $logAction->save()) {
                    return JsonResponse::create(['error' => 'Failed validation: '.print_r($logAction->getErrors(), true)], 500);
                }
                $logActions[] = $logAction;
            }
            // dd($logActions);
            return redirect()->back()->with('success', trans('admin/locations/message.upload.success'));
        }
        return redirect()->back()->with('error', trans('admin/locations/message.upload.nofiles'));


    }

    /**
     * Delete file
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.6]
     * @param  int $locationId
     * @param  int $fileId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy($locationId = null, $fileId = null)
    {
        $location = Location::find($locationId);
        $destinationPath = config('app.private_uploads').'/locations';

        if (isset($location->id)) {
            $this->authorize('update', $location);
            $log = Actionlog::find($fileId);
            $full_filename = $destinationPath.'/'.$log->filename;
            if (file_exists($full_filename)) {
                unlink($destinationPath.'/'.$log->filename);
            }
            $log->delete();

            return redirect()->back()->with('success', trans('admin/locations/message.deletefile.success'));
        }
        // Prepare the error message
        $error = trans('admin/locations/message.Location_not_found', ['id' => $locationId]);
        // Redirect to the licence management page
        return redirect()->route('Locations.index')->with('error', $error);

    }

    /**
     * Display/download the uploaded file
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.6]
     * @param  int $locationId
     * @param  int $fileId
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show($locationId = null, $fileId = null)
    {
        $location = Location::find($locationId);

        // the license is valid
        if (isset($location->id)) {

            $this->authorize('view', $location);

            $log = Actionlog::find($fileId);

            // Display the file inline
            if (request('inline') == 'true') {
                $headers = [
                    'Content-Disposition' => 'inline',
                ];
                return Storage::download('private_uploads/locations/'.$log->filename, $log->filename, $headers);
            }

            return Storage::download('private_uploads/locations/'.$log->filename);

        }

        // Redirect to the Location management page if the Location doesn't exist
        return redirect()->route('Locations.index')->with('error',  trans('admin/locations/message.Location_not_found', ['id' => $locationId]));
    }

}
