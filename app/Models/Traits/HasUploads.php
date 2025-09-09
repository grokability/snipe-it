<?php

namespace App\Models\Traits;

use App\Models\Actionlog;

trait HasUploads
{

    public function uploads()
    {
        return $this->hasMany(Actionlog::class, 'item_id')
            ->where('item_type', self::class)
            ->where('action_type', '=', 'uploaded')
            ->whereNotNull('filename')
            ->whereNotIn('filename', function ($query) {
                $query->select('filename')
                    ->from('action_logs')
                    ->where('item_type', '=', self::class)
                    ->where('action_type', '=', 'upload deleted')
                    ->where('item_id', $this->id);
            });
    }


}