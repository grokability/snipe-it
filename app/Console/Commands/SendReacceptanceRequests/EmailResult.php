<?php

namespace App\Console\Commands\SendReacceptanceRequests;

use App\Models\User;
use Illuminate\Support\Collection;

final readonly class EmailResult
{
    /**
     * @param  Collection<int, User>  $failedUsers
     */
    public function __construct(
        public int $notified,
        public Collection $failedUsers,
    ) {}
}
