<?php

return [
    "trace" => env("SCIM_TRACE",false),
    // below, if we ever get 'sure' that we can change this default to 'true' we should
    "omit_main_schema_in_return" => env('SCIM_STANDARDS_COMPLIANCE', true),
    "publish_routes" => false,
];
