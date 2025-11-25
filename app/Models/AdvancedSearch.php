<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvancedSearch extends Model
{
    static function userHasViewPermission($user): bool {
        return $user->hasAccess('advancedsearch');
    }
}
