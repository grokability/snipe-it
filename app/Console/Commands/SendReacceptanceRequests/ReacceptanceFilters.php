<?php

namespace App\Console\Commands\SendReacceptanceRequests;

use Illuminate\Support\Carbon;

/**
 * The resolved filter set for a re-acceptance run, built from CLI options and/or
 * interactive prompts. Carried as a typed object so call sites read by property
 * instead of guessing string keys.
 */
final readonly class ReacceptanceFilters
{
    /**
     * @param  string[]  $types  the morph class names to consider
     * @param  int[]  $categories  category ids to scope to (empty = no category filter)
     */
    public function __construct(
        public array $types,
        public array $categories,
        public ?int $company,
        public ?int $user,
        public ?Carbon $acceptedBefore,
    ) {}
}
