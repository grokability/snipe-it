<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Actionlog;
use Illuminate\Database\Eloquent\Collection;

class ProfileTransformer
{
    public function transformFiles(Collection $files, $total)
    {
        $array = [];
        foreach ($files as $file) {
            $array[] = self::transformFile($file);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }


    public function transformFile(Actionlog $file)
    {
        $array = [
            'id' => (int) $file->id,
            'icon' => Helper::filetype_icon($file->filename),
            'item' => ($file->item) ? [
                'name' => $file->item->display_name ? e($file->item->display_name) : null,
                'type' => e($file->itemType()),
            ] : null,
            'filename' => e($file->filename),
            'signature_file'   => ($file->accept_signature) ? route('profile.signature.view', ['filename' => $file->accept_signature ]) : null,
            'note' => e($file->note),
            'url' => route('profile.storedeula.download', ['filename' => $file->filename]),
            'file' => route('profile.storedeula.download', ['filename' => $file->filename]),
            'created_at' => Helper::getFormattedDateObject($file->created_at, 'datetime'),
        ];

        return $array;
    }

}
