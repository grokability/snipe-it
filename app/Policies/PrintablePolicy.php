<?php

namespace App\Policies;

/**
 * Policy for Printable template management.
 *
 * Printable template management (create/edit/delete/view) is restricted
 * to admins via the inherited `SnipePermissionsPolicy` base which maps to
 * the 'categories' permission column.  Generating printables for a
 * specific asset is gated separately by the AssetPolicy.
 */
class PrintablePolicy extends SnipePermissionsPolicy
{
    protected function columnName(): string
    {
        return 'categories';
    }
}
