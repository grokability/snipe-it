<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Location;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class LocationsTransformer
{
    public function transformLocations(Collection $locations, $total)
    {
        $array = [];
        foreach ($locations as $location) {
            $array[] = self::transformLocation($location);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformLocation(Location $location = null)
    {
        if ($location) {
            $children_arr = [];
            if (! is_null($location->children)) {
                foreach ($location->children as $child) {
                    $children_arr[] = [
                        'id' => (int) $child->id,
                        'name' => $child->name,
                    ];
                }
            }

            $array = [
                'id' => (int) $location->id,
                'name' => e($location->name),
                'image' =>   ($location->image) ? Storage::disk('public')->url('locations/'.e($location->image)) : null,
                'address' =>  ($location->address) ? e($location->address) : null,
                'address2' =>  ($location->address2) ? e($location->address2) : null,
                'city' =>  ($location->city) ? e($location->city) : null,
                'state' =>  ($location->state) ? e($location->state) : null,
                'country' => ($location->country) ? e($location->country) : null,
                'zip' => ($location->zip) ? e($location->zip) : null,
                'phone' => ($location->phone!='') ? e($location->phone): null,
                'fax' => ($location->fax!='') ? e($location->fax): null,
                'accessories_count' => (int) $location->accessories_count,
                'assigned_accessories_count' => (int) $location->assigned_accessories_count,
                'assigned_assets_count' => (int) $location->assigned_assets_count,
                'assets_count'    => (int) $location->assets_count,
                'rtd_assets_count'    => (int) $location->rtd_assets_count,
                'users_count'    => (int) $location->users_count,
                'consumables_count'    => (int) $location->consumables_count,
                'components_count'    => (int) $location->components_count,
                'children_count'    => (int) $location->children_count,
                'currency' =>  ($location->currency) ? e($location->currency) : null,
                'ldap_ou' =>  ($location->ldap_ou) ? e($location->ldap_ou) : null,
                'notes' => Helper::parseEscapedMarkedownInline($location->notes),
                'created_at' => Helper::getFormattedDateObject($location->created_at, 'datetime'),
                'created_by' => $location->adminuser ? [
                    'id' => (int) $location->adminuser->id,
                    'name'=> e($location->adminuser->present()->fullName),
                ]: null,
                'updated_at' => Helper::getFormattedDateObject($location->updated_at, 'datetime'),
                'parent' => ($location->parent) ? [
                    'id' => (int) $location->parent->id,
                    'name'=> e($location->parent->name),
                ] : null,
                'manager' => ($location->manager) ? (new UsersTransformer)->transformUser($location->manager) : null,
                'company' => ($location->company) ? [
                    'id' => (int) $location->company->id,
                    'name'=> e($location->company->name)
                ] : null,

                'children' => $children_arr,
            ];

            $permissions_array['available_actions'] = [
                'update' => (Gate::allows('update', Location::class) && ($location->deleted_at == '')),
                'delete' => $location->isDeletable(),
                'bulk_selectable' => [
                    'delete' => $location->isDeletable()
                ],
                'clone' => (Gate::allows('create', Location::class) && ($location->deleted_at == '')),
                'restore' => (Gate::allows('create', Location::class) && ($location->deleted_at != '')),
            ];

            $array += $permissions_array;

            return $array;
        }
    }


    public function transformCheckedoutAccessories($accessory_checkouts, $total)
    {

        $array = [];
        foreach ($accessory_checkouts as $checkout) {
            $array[] = self::transformCheckedoutAccessory($checkout);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }


    public function transformCheckedoutAccessory(AccessoryCheckout $accessory_checkout)
    {

            $array = [
                'id' => $accessory_checkout->id,
                'assigned_to' => $accessory_checkout->assigned_to,
                'accessory' => $this->transformAccessory($accessory_checkout->accessory),
                'image' => ($accessory_checkout?->accessory?->image) ? Storage::disk('public')->url('accessories/' . e($accessory_checkout->accessory->image)) : null,
                'note' => $accessory_checkout->note ? e($accessory_checkout->note) : null,
                'created_by' => $accessory_checkout->adminuser ? [
                    'id' => (int) $accessory_checkout->adminuser->id,
                    'name'=> e($accessory_checkout->adminuser->present()->fullName),
                ]: null,
                'created_at' => Helper::getFormattedDateObject($accessory_checkout->created_at, 'datetime'),
            ];

            $permissions_array['available_actions'] = [
                'checkout' => false,
                'checkin' => Gate::allows('checkin', Accessory::class),
            ];

            $array += $permissions_array;
        return $array;
    }



    /**
     * This gives a compact view of the location data without any additional relational queries,
     * allowing us to 1) deliver a smaller payload and 2) avoid additional queries on relations that
     * have not been easy/lazy loaded already
     *
     * @param Location $location
     * @return array
     * @throws \Exception
     */
    public function transformLocationCompact(Location $location = null)
    {
        if ($location) {

            $array = [
                'id' => (int) $location->id,
                'image' =>   ($location->image) ? Storage::disk('public')->url('locations/'.e($location->image)) : null,
                'type' => "location",
                'name' => e($location->name),
                'created_by' => $location->adminuser ? [
                    'id' => (int) $location->adminuser->id,
                    'name'=> e($location->adminuser->present()->fullName),
                ]: null,
                'created_at' => Helper::getFormattedDateObject($location->created_at, 'datetime'),
            ];

            return $array;
        }
    }

    private function transformAccessory(?Accessory $accessory): ?array
    {
        if ($accessory) {
            return [
                'id' => $accessory->id,
                'name' => $accessory->name,
            ];
        }

        return null;
    }
}
