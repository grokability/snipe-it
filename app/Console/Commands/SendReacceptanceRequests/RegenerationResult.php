<?php

namespace App\Console\Commands\SendReacceptanceRequests;

use App\Models\User;
use Illuminate\Support\Collection;

final readonly class RegenerationResult
{
    /**
     * @param  array<int, array{user: User, acceptances: Collection}>  $createdAcceptancesByUser
     * @param  Collection<int, User>  $failedUsers
     */
    public function __construct(
        public array $createdAcceptancesByUser,
        public Collection $failedUsers,
    ) {}
}
