<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\License;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;

class LicensesTransformer
{
    public function transformLicenses(Collection $licenses, $total)
    {
        $array = [];
        foreach ($licenses as $license) {
            $array[] = self::transformLicense($license);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformLicense(License $license)
    {
        $unreassignable = $license->reassignable ? 0 : (int) ($license->unreassignable_seats_count ?? License::unReassignableCount($license));

        $array = [
            'id' => (int) $license->id,
            'name' => e($license->name),
            'qr_code_url' => route('qr_code/common', ['object_type' => 'licenses', 'id' => $license->id]),
            'company' => ($license->company) ? ['id' => (int) $license->company->id, 'name' => e($license->company->name)] : null,
            'manufacturer' => ($license->manufacturer) ? [
                'id' => (int) $license->manufacturer->id,
                'name' => e($license->manufacturer->name),
                'tag_color' => ($license->manufacturer->tag_color) ? e($license->manufacturer->tag_color) : null,
            ] : null,
            'product_key' => (Gate::allows('viewKeys', $license)) ? e($license->serial) : License::PRODUCT_KEY_MASK,
            'order_number' => ($license->order_number) ? e($license->order_number) : null,
            'purchase_order' => ($license->purchase_order) ? e($license->purchase_order) : null,
            'purchase_date' => Helper::getFormattedDateObject($license->purchase_date, 'date'),
            'termination_date' => Helper::getFormattedDateObject($license->termination_date, 'date'),
            'expiration_date' => Helper::getFormattedDateObject($license->expiration_date, 'date'),
            'depreciation' => ($license->depreciation) ? ['id' => (int) $license->depreciation->id, 'name' => e($license->depreciation->name)] : null,
            'purchase_cost' => Helper::formatCurrencyOutput($license->purchase_cost),
            'purchase_cost_numeric' => $license->purchase_cost,
            'notes' => Helper::parseEscapedMarkedownInline($license->notes),
            'seats' => (int) $license->seats,
            'free_seats_count' => (int) $license->free_seats_count - $unreassignable,
            'remaining' => (int) $license->free_seats_count,
            'percent_remaining' => round($license->percentRemaining()),
            'min_amt' => ($license->min_amt) ? (int) ($license->min_amt) : null,
            'license_name' => ($license->license_name) ? e($license->license_name) : null,
            'license_email' => ($license->license_email) ? e($license->license_email) : null,
            'reassignable' => ($license->reassignable == 1) ? true : false,
            'maintained' => ($license->maintained == 1) ? true : false,
            'requestable' => (bool) $license->requestable,
            'supplier' => ($license->supplier) ? [
                'id' => (int) $license->supplier->id,
                'name' => e($license->supplier->name),
                'tag_color' => ($license->supplier->tag_color) ? e($license->supplier->tag_color) : null,
            ] : null,
            'category' => ($license->category) ? [
                'id' => (int) $license->category->id,
                'name' => e($license->category->name),
                'tag_color' => ($license->category->tag_color) ? e($license->category->tag_color) : null,
            ] : null,
            'created_by' => ($license->adminuser) ? [
                'id' => (int) $license->adminuser->id,
                'name' => e($license->adminuser->display_name),
            ] : null,
            'created_at' => Helper::getFormattedDateObject($license->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($license->updated_at, 'datetime'),
            'deleted_at' => Helper::getFormattedDateObject($license->deleted_at, 'datetime'),
            'disabled' => $license->isInactive(),
        ];

        // See AccessoriesTransformer for the assigned_to_self /
        // available_actions.request/cancel rationale (reads from the
        // eager-loaded `requests` relation so no N+1). Only populates
        // when the relation has been eager loaded - the standard
        // index endpoint doesn't preload requests, so gate on
        // relationLoaded to avoid a per-row query.
        $userHasOpenRequest = auth()->check() && $license->relationLoaded('requests') && $license->requests->contains(
            fn (\App\Models\CheckoutRequest $request) => $request->user_id === auth()->id() && $request->canceled_at === null
        );
        $permissions_array = [];
        $permissions_array['assigned_to_self'] = $userHasOpenRequest;

        $permissions_array['available_actions'] = [
            'checkout' => Gate::allows('checkout', $license),
            'checkin' => Gate::allows('checkin', $license),
            'clone' => Gate::allows('clone', $license),
            'update' => Gate::allows('update', $license),
            'delete' => $license->isDeletable(),
            'user_can_checkout' => (bool) (($license->free_seats_count - $unreassignable) > 0),
            'request' => (bool) $license->requestable && ! $userHasOpenRequest,
            'cancel' => (bool) $license->requestable && $userHasOpenRequest,
            'bulk_selectable' => [
                'delete' => $license->isDeletable(),
                'delete_with_checkin' => Gate::allows('delete', $license) && Gate::allows('checkin', $license) && ($license->deleted_at == ''),
            ],
        ];

        $array += $permissions_array;

        return $array;
    }

    public function transformAssetsDatatable($licenses)
    {
        return (new DatatablesTransformer)->transformDatatables($licenses);
    }

    public function transformRequestedLicenses(Collection $licenses, $total)
    {
        $array = [];
        foreach ($licenses as $license) {
            $array[] = self::transformRequestedLicense($license);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformRequestedLicense(License $license)
    {
        $unreassignable = $license->reassignable ? 0 : (int) ($license->unreassignable_seats_count ?? License::unReassignableCount($license));
        $userHasOpenRequest = auth()->check() && $license->relationLoaded('requests') && $license->requests->contains(
            fn (\App\Models\CheckoutRequest $request) => $request->user_id === auth()->id() && $request->canceled_at === null
        );

        return [
            'id' => (int) $license->id,
            'name' => e($license->name),
            'category' => ($license->category) ? [
                'id' => (int) $license->category->id,
                'name' => e($license->category->name),
                'tag_color' => ($license->category->tag_color) ? e($license->category->tag_color) : null,
            ] : null,
            'seats' => (int) $license->seats,
            'free_seats_count' => (int) $license->free_seats_count - $unreassignable,
            'remaining' => (int) $license->free_seats_count,
            'requestable' => (bool) $license->requestable,
            'assigned_to_self' => $userHasOpenRequest,
            'available_actions' => [
                'request' => (bool) $license->requestable && ! $userHasOpenRequest,
                'cancel' => (bool) $license->requestable && $userHasOpenRequest,
            ],
        ];
    }
}
