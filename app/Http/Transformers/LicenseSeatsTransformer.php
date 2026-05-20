<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\License;
use App\Models\LicenseSeat;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;

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
                'name' => e($seat->user->present()->fullName),
                'email' => e($seat->user->email),
                'department' => ($seat->user->department) ?
                        [
                            'id' => (int) $seat->user->department->id,
                            'name' => e($seat->user->department->name),
                            'tag_color' => $seat->user->department->tag_color ? e($seat->user->department->tag_color) : null,

                        ] : null,
                'companies' => $seat->user->companies->map(fn ($c) => [
                    'id' => (int) $c->id,
                    'name' => e($c->name),
                    'tag_color' => $c->tag_color ? e($c->tag_color) : null,
                ])->values(),
                'created_at' => Helper::getFormattedDateObject($seat->created_at, 'datetime'),
            ] : null,
            'assigned_asset' => ($seat->asset) ? [
                'id' => (int) $seat->asset->id,
                'name' => e($seat->asset->present()->fullName),
                'created_at' => Helper::getFormattedDateObject($seat->created_at, 'datetime'),
            ] : null,
            'location' => ($seat->location()) ? [
                'id' => (int) $seat->location()->id,
                'name' => e($seat->location()->display_name),
                'tag_color' => $seat->location()->tag_color ? e($seat->location()->tag_color) : null,
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
            'bulk_selectable' => [
                'checkin' => Gate::allows('checkin', License::class) && ($seat->assigned_to || $seat->asset_id),
            ],
        ];

        $array += $permissions_array;

        return $array;
    }
}
