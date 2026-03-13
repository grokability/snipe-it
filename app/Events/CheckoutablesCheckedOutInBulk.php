<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CheckoutablesCheckedOutInBulk
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Collection $assets,
        public Model $target,
        public User $admin,
        public string $checkout_at,
        public string $expected_checkin,
        public string $note,
    ) {
    }
}
