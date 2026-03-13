<?php

namespace App\Http\Transformers;

use App\Models\Import;
use App\Models\Setting;

class ImportsTransformer
{
    public function transformImports($imports)
    {
        $array = [];
        foreach ($imports as $import) {
            $array[] = self::transformImport($import);
        }

        return $array;
    }

    public function transformImport(Import $import)
    {
        $array = [
            'id' => (int) $import->id,
            'file_path' => e($import->file_path),
            'filesize' => Setting::fileSizeConvert($import->filesize),
            'name' => e($import->name),
            'import_type' => e($import->import_type),
            'created_at' => $import->created_at->diffForHumans(),
            'header_row' => $import->header_row,
            'first_row'  => $import->first_row,
            'field_map'  => $import->field_map,
        ];

        return $array;
    }

    public function transformImportsDatatable($imports)
    {
        return (new DatatablesTransformer)->transformDatatables($imports);
    }
}
