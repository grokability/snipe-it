<?php

namespace App\Http\Requests;

use App\Enums\FileStorage;
use App\Models\Import;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
        ini_set('max_execution_time', config('importer.time_limit')); // 600 seconds = 10 minutes
        ini_set('memory_limit', config('importer.memory_limit'));

        // Pull the stored CSV through the Storage abstraction so S3-backed
        // private disks work the same as local filesystems. The Importer
        // constructor's local-vs-string branch would silently treat the
        // stored path string as CSV content when the file is on S3 (because
        // is_file() returns false), producing a zero-row import with no
        // error. Download once per slice to a local temp file and feed
        // that to the Importer.
        $storedPath = FileStorage::Imports->privateStorageKey().$import->file_path;
        if (! Storage::exists($storedPath)) {
            $this->errorCallback($import, 'file', [trans('admin/hardware/message.import.file_missing_on_disk')]);

            return $this->errors;
        }

        $filename = tempnam(sys_get_temp_dir(), 'snipeit-import-');
        $readStream = Storage::readStream($storedPath);
        if ($readStream === null) {
            $this->errorCallback($import, 'file', [trans('admin/hardware/message.import.file_missing_on_disk')]);

            return $this->errors;
        }
        file_put_contents($filename, $readStream);
        if (is_resource($readStream)) {
            fclose($readStream);
        }
        register_shutdown_function(static function () use ($filename) {
            if (is_file($filename)) {
                @unlink($filename);
            }
        });

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

        // "Skip updating fields with blank cells" opts out of the default
        // clear-DB-on-blank behavior so an empty cell in the CSV keeps
        // whatever value is already in the DB column. Only the update path
        // is affected. New-row inserts ignore the flag entirely. See
        // ItemImporter::$rejectEmptyOnUpdate.
        if ($importer instanceof \App\Importer\ItemImporter) {
            $importer->setRejectEmptyOnUpdate((bool) $this->input('import-preserve-blanks'));
        }

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
        $this->tally = $importer->getTally();

        return $this->errors;
    }

    public function getTally(): array
    {
        return $this->tally ?? ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errored' => 0];
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

    private array $tally;
}
