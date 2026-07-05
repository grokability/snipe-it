<?php

namespace App\Actions\StatusLabels;

use App\Exceptions\ItemStillHasAssets;
use App\Models\Statuslabel;

class DestroyStatuslabelAction
{
    /**
     * @throws ItemStillHasAssets
     */
    public static function run(Statuslabel $statuslabel): bool
    {
        $statuslabel->loadCount(['assets as assets_count']);

        if ($statuslabel->assets_count > 0) {
            throw new ItemStillHasAssets($statuslabel);
        }

        $statuslabel->delete();

        return true;
    }
}
