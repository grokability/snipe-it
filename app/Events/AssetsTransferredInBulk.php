<?php


namespace App\Events;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class AssetsTransferredInBulk
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Collection $transferable,
        public Model      $transferredTo,
        public Model      $transferredFrom,
        public User       $admin,
        public string     $transferred_at,
        public string     $expected_checkin,
        public string     $note,

        public array     $originalValues,
    )
    {
    }

}