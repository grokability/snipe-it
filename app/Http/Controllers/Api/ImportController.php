<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ItemImportRequest;
use App\Http\Transformers\ImportsTransformer;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Import;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use Onnov\DetectEncoding\EncodingDetector;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class ImportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index() : JsonResponse | array
    {
        $this->authorize('import');
        $imports = Import::with('adminuser')->latest()->get();
        return (new ImportsTransformer)->transformImports($imports);
    }

    /**
     * Process and store a CSV upload file.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store() : JsonResponse
    {
        $this->authorize('import');
        if (! config('app.lock_passwords')) {
            $files = Request::file('files');
            $path = config('app.private_uploads').'/imports';
            $results = [];
            $import = new Import;
            $detector = new EncodingDetector();

            foreach ($files as $file) {
                if (! in_array($file->getMimeType(), [
                    'application/vnd.ms-excel',
                    'text/csv',
                    'application/csv',
                    'text/x-Algol68', // because wtf CSV files?
                    'text/plain',
                    'text/comma-separated-values',
                    'text/tsv', ])) {
                    $results['error'] = 'File type must be CSV. Uploaded file is '.$file->getMimeType();
                    return response()->json(Helper::formatStandardApiResponse('error', null, $results['error']), 422);
                }

                //TODO: is there a lighter way to do this?
                if (! ini_get('auto_detect_line_endings')) {
                    ini_set('auto_detect_line_endings', '1');
                }
                if (function_exists('iconv')) {
                    $file_contents = $file->getContent(); //TODO - this *does* load the whole file in RAM, but we need that to be able to 'iconv' it?
                    $encoding = $detector->getEncoding($file_contents);
                    \Log::debug("Discovered encoding: $encoding in uploaded CSV");
                    $reader = null;
                    if (strcasecmp($encoding, 'UTF-8') != 0) {
                        $transliterated = false;
                        try {
                            $transliterated = iconv(strtoupper($encoding), 'UTF-8', $file_contents);
                        } catch (\Exception $e) {
                            $transliterated = false; //blank out the partially-decoded string
                            return response()->json(
                                Helper::formatStandardApiResponse(
                                    'error',
                                    null,
                                    trans('admin/hardware/message.import.transliterate_failure', ["encoding" => $encoding])
                                ),
                                422
                            );
                        }
                        if ($transliterated !== false) {
                            $tmpname = tempnam(sys_get_temp_dir(), '');
                            $tmpresults = file_put_contents($tmpname, $transliterated);
                            $transliterated = null; //save on memory?
                            if ($tmpresults !== false) {
                                $newfile = new UploadedFile($tmpname, $file->getClientOriginalName(), null, null, true); //WARNING: this is enabling 'test mode' - which is gross, but otherwise the file won't be treated as 'uploaded'
                                if ($newfile->isValid()) {
                                    $file = $newfile;
                                }
                            }
                        }
                    }
                    $file_contents = null; //try to save on memory, I guess?
                }
                $reader = Reader::createFromFileObject($file->openFile('r')); //file pointer leak?

                try {
                    $import->header_row = $reader->nth(0);
                } catch (JsonEncodingException $e) {
                    return response()->json(
                        Helper::formatStandardApiResponse(
                            'error',
                            null,
                            trans('admin/hardware/message.import.header_row_has_malformed_characters')
                        ),
                        422
                    );
                }

                //duplicate headers check
                $duplicate_headers = [];

                for ($i = 0; $i < count($import->header_row); $i++) {
                    $header = $import->header_row[$i];
                    if (in_array($header, $import->header_row)) {
                        $found_at = array_search($header, $import->header_row);
                        if ($i > $found_at) {
                            //avoid reporting duplicates twice, e.g. "1 is same as 17! 17 is same as 1!!!"
                            //as well as "1 is same as 1!!!" (which is always true)
                            //has to be > because otherwise the first result of array_search will always be $i itself(!)
                            array_push($duplicate_headers, "Duplicate header '$header' detected, first at column: ".($found_at + 1).', repeats at column: '.($i + 1));
                        }
                    }
                }
                if (count($duplicate_headers) > 0) {
                    return response()->json(Helper::formatStandardApiResponse('error', null, implode('; ', $duplicate_headers)),422);
                }

                try {
                    // Grab the first row to display via ajax as the user picks fields
                    $import->first_row = $reader->nth(1);
                } catch (JsonEncodingException $e) {
                    return response()->json(
                        Helper::formatStandardApiResponse(
                            'error',
                            null,
                            trans('admin/hardware/message.import.content_row_has_malformed_characters')
                        ),
                        422
                    );
                }

                $date = date('Y-m-d-his');
                $fixed_filename = str_slug($file->getClientOriginalName());
                try {
                    $file->move($path, $date.'-'.$fixed_filename);
                } catch (FileException $exception) {
                    $results['error'] = trans('admin/hardware/message.upload.error');
                    if (config('app.debug')) {
                        $results['error'] .= ' '.$exception->getMessage();
                    }

                    return response()->json(Helper::formatStandardApiResponse('error', null, $results['error']), 500);
                }
                $file_name = date('Y-m-d-his').'-'.$fixed_filename;
                $import->file_path = $file_name;
                $import->filesize = null;

                if (!file_exists($path.'/'.$file_name)) {
                    return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.file_not_found')), 500);
                }

                $import->filesize = filesize($path.'/'.$file_name);
                $import->created_by = auth()->id();
                $import->save();
                $results[] = $import;
            }
            $results = (new ImportsTransformer)->transformImports($results);

            return response()->json([
                'files' => $results,
            ]);
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.feature_disabled')), 422);
    }

    /**
     * Processes the specified Import.
     *
     * @param  int  $import_id
     */
    public function process(ItemImportRequest $request, $import_id) : JsonResponse
    {
        $this->authorize('import');

        // Run a backup immediately before processing
        if ($request->get('run-backup')) {
            Log::debug('Backup manually requested via importer');
            Artisan::call('snipeit:backup', ['--filename' => 'pre-import-backup-'.date('Y-m-d-H-i-s')]);
        } else {
            Log::debug('NO BACKUP requested via importer');
        }

        $import = Import::find($import_id);

        if(is_null($import)){
            $error[0][0] = trans("validation.exists", ["attribute" => "file"]);
            return response()->json(Helper::formatStandardApiResponse('import-errors', null, $error), 500);
        }

        $errors = $request->import($import);
        $redirectTo = 'hardware.index';
        switch ($request->get('import-type')) {
            case 'asset':
                $redirectTo = 'hardware.index';
                break;
            case 'assetModel':
                $redirectTo = 'models.index';
                break;
            case 'accessory':
                $redirectTo = 'accessories.index';
                break;
            case 'consumable':
                $redirectTo = 'consumables.index';
                break;
            case 'component':
                $redirectTo = 'components.index';
                break;
            case 'license':
                $redirectTo = 'licenses.index';
                break;
            case 'user':
                $redirectTo = 'users.index';
                break;
            case 'location':
                $redirectTo = 'locations.index';
                break;
            case 'supplier':
                $redirectTo = 'suppliers.index';
                break;
            case 'manufacturer':
                $redirectTo = 'manufacturers.index';
                break;
            case 'category':
                $redirectTo = 'categories.index';
                break;
        }

        if ($errors) { //Failure
            return response()->json(Helper::formatStandardApiResponse('import-errors', null, $errors), 500);
        }
        //Flash message before the redirect
        Session::flash('success', trans('admin/hardware/message.import.success'));

        return response()->json(Helper::formatStandardApiResponse('success', null, ['redirect_url' => route($redirectTo)]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $import_id
     */
    public function destroy($import_id) : JsonResponse
    {
        $this->authorize('create', Asset::class);

        if ($import = Import::find($import_id)) {
            try {
                // Try to delete the file
                Storage::delete('imports/'.$import->file_path);
                $import->delete();

                return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/hardware/message.import.file_delete_success')));
            } catch (\Exception $e) {
                // If the file delete didn't work, remove it from the database anyway and return a warning
                $import->delete();

                return response()->json(Helper::formatStandardApiResponse('warning', null, trans('admin/hardware/message.import.file_not_deleted_warning')));
            }

        }
        return response()->json(Helper::formatStandardApiResponse('warning', null, trans('admin/hardware/message.import.file_not_deleted_warning')));
    }
}
