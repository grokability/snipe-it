<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\License;
use App\Models\LicenseSeat;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Collection;
class LicenseSeatsTransformer
{
    public function transformLicenseSeats(Collection $seats, $total)
    {
        $array = [];

        foreach ($seats as $seat) {
            $array[] = self::transformLicenseSeat($seat);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformLicenseSeat(LicenseSeat $seat)
    {
        $array = [
            'id' => (int) $seat->id,
            'license_id' => (int) $seat->license->id,
            'updated_at' => Helper::getFormattedDateObject($seat->updated_at, 'datetime'), // we use updated_at here because the record gets updated when it's checked in or out
            'assigned_user' => ($seat->user) ? [
                'id' => (int) $seat->user->id,
                'name'=> e($seat->user->present()->fullName),
                'email' => e($seat->user->email),
                'department'=> ($seat->user->department) ?
                        [
                            'id' => (int) $seat->user->department->id,
                            'name' => e($seat->user->department->name),

                        ] : null,
                'created_at' => Helper::getFormattedDateObject($seat->created_at, 'datetime'),
            ] : null,
            'assigned_asset' => ($seat->asset) ? [
                'id' => (int) $seat->asset->id,
                'name'=> e($seat->asset->present()->fullName),
                'created_at' => Helper::getFormattedDateObject($seat->created_at, 'datetime'),
            ] : null,
            'location' => ($seat->location()) ? [
                'id' => (int) $seat->location()->id,
                'name'=> e($seat->location()->name),
                'created_at' => Helper::getFormattedDateObject($seat->created_at, 'datetime'),
            ] : null,
            'reassignable' => (bool) $seat->license->reassignable,
            'notes' => e($seat->notes),
            'user_can_checkout' => (($seat->assigned_to == '') && ($seat->asset_id == '')),
            'disabled' => $seat->unreassignable_seat || $seat->license->isInactive(),
        ];

        $permissions_array['available_actions'] = [
            'checkout' => Gate::allows('checkout', License::class),
            'checkin' => Gate::allows('checkin', License::class),
            'clone' => Gate::allows('create', License::class),
            'update' => Gate::allows('update', License::class),
            'delete' => Gate::allows('delete', License::class),
        ];

        $array += $permissions_array;

        return $array;
    }
}
