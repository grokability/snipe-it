<?php

use Illuminate\Support\Facades\DB;

/**
 * An id no row in either table holds, so both can be created with it.
 * Taken above both maxima rather than read from the auto-increment
 * counters, which are engine-specific to query.
 */
function firstIdFreeInBoth(string $table, string $otherTable): int
{
    return max(
        (int) DB::table($table)->max('id'),
        (int) DB::table($otherTable)->max('id'),
    ) + 1;
}
