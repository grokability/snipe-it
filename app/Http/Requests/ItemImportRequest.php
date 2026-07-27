<?php

namespace App\Http\Requests;

use App\Models\Import;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class ItemImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'import-type' => 'required',
        ];
    }

    public function import(Import $import)
    {
        ini_set('max_execution_time', env('IMPORT_TIME_LIMIT', 600)); // 600 seconds = 10 minutes
        ini_set('memory_limit', env('IMPORT_MEMORY_LIMIT', '500M'));

        $filename = config('app.private_uploads').'/imports/'.$import->file_path;
        $import->import_type = $this->input('import-type');
        $class = ucfirst($import->import_type);
        $classString = "App\\Importer\\{$class}Importer";
        $importer = new $classString($filename);
        $import->field_map = request('column-mappings');
        $import->created_by = $import->created_by ?? auth()->id();
        $import->save();
        $fieldMappings = [];

        if ($import->field_map) {
            foreach ($import->field_map as $field => $fieldValue) {
                $errorMessage = null;

                if (is_null($fieldValue)) {
                    $errorMessage = trans('validation.import_field_empty', ['fieldname' => $field]);
                    $this->errorCallback($import, $field, [$field => [$errorMessage]]);

                    return $this->errors;
                }
            }
            // We submit as csv field: column, but the importer is happier if we flip it here.
            $fieldMappings = array_change_key_case(array_flip($import->field_map), CASE_LOWER);
        }
        $importer->setCallbacks([$this, 'log'], [$this, 'progress'], [$this, 'errorCallback'])
            ->setCreatedBy(auth()->id())
            ->setUpdating($this->input('import-update'))
            ->setShouldNotify($this->input('send-welcome'))
            ->setUsernameFormat('firstname.lastname')
            ->setFieldMappings($fieldMappings);

        // Matcher options only apply to the asset history importer, which
        // resolves rows to existing users by name (not by creating new
        // users). Any other importer ignores these switches.
        if ($importer instanceof \App\Importer\AssetHistoryImporter) {
            $importer->setMatchUsername((bool) $this->input('match_username'))
                ->setMatchEmail((bool) $this->input('match_email'))
                ->setMatchFirstnameLastname((bool) $this->input('match_firstnamelastname'))
                ->setMatchFlastname((bool) $this->input('match_flastname'))
                ->setMatchFirstname((bool) $this->input('match_firstname'));
        }

        // Sliced import: caller passes offset+limit for chunked
        // processing (large-CSV timeout workaround). Each slice is its
        // own Importer::import() call and therefore its own DB
        // transaction, so a failure in one slice rolls back only that
        // slice. Both params null = original all-at-once behavior.
        $offset = $this->filled('offset') ? (int) $this->input('offset') : null;
        $limit = $this->filled('limit') ? (int) $this->input('limit') : null;

        $importer->import($offset, $limit);

        return $this->errors;
    }

    public function log($string)
    {
        Log::Info($string);
    }

    public function progress($count)
    {
        // Open for future
    }

    public function errorCallback($item, $field, $errorString)
    {
        $this->errors[$item->name][$field] = $errorString;
    }

    private $errors;
}
