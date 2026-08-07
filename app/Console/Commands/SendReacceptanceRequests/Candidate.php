<?php

namespace App\Console\Commands\SendReacceptanceRequests;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * One (user, checkoutable) re-acceptance candidate: the still-assigned item, its
 * holder, the quantity to carry forward, and the prior accepted acceptance(s) to
 * supersede.
 */
final readonly class Candidate
{
    public function __construct(
        public User $user,
        public Model $checkoutable,
        public ?int $qty,
        public Collection $acceptances,
    ) {}
}
