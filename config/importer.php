<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Slice size for CSV imports
    |--------------------------------------------------------------------------
    |
    | The Livewire importer breaks a large CSV into fixed-size chunks and
    | fires one HTTP request per chunk so no single request stays open long
    | enough to bump PHP's max_execution_time or an upstream proxy timeout.
    | Each chunk is processed inside its own DB::transaction, so a failure
    | in chunk K rolls back only chunk K - earlier chunks stay committed.
    |
    | 500 rows is a compromise: small enough to comfortably fit inside a
    | 60-second request budget on modest hardware even for asset imports
    | that touch categories / manufacturers / models / statuslabels /
    | actionlogs per row, large enough that the round-trip overhead
    | between slices doesn't dominate for imports of a few thousand rows.
    |
    | Lower this if your install hits per-request timeouts on complex
    | imports; raise it if a big import feels chatty because of network
    | round trips.
    */

    'slice_size' => env('IMPORT_SLICE_SIZE', 500),

];
