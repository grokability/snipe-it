<?php

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Function form of TestCase::actingAsForApi() for helper functions, where $this isn't available.
 */
function actingAsForApi(Authenticatable $user): TestCase
{
    return test()->actingAsForApi($user);
}

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
