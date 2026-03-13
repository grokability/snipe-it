<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class UserMerged
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $from_user, User $to_user, ?User $admin)
    {
        $this->merged_from        = $from_user;
        $this->merged_to      = $to_user;
        $this->admin            = $admin;
    }
}
