<?php

namespace App\Models\Traits;

use App\Models\Actionlog;
use App\Models\CompanyableScope;

trait HasUploads
{
    public function uploads()
    {
        // Bypass FMCS company scoping: access is already gated by the policy on the
        // parent object. Objects like AssetModel and Company have no company_id, so
        // their upload logs always have company_id = null, which the scope would hide.
        return $this->hasMany(Actionlog::class, 'item_id')
            ->withoutGlobalScope(CompanyableScope::class)
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
