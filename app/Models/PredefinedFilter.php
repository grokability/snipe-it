<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Validating\ValidatingTrait;
use Illuminate\Database\Eloquent\Builder;

class PredefinedFilter extends Model
{
    use HasFactory;
    use SoftDeletes;
    use ValidatingTrait;

    protected $casts = [
        "custom_fields"=> "array",
    ];

    protected $fillable = [
        'created_by',
        'name',
        // search attributes
        'company_id',
        'location_id',
        'rtd_location_id',
        'supplier_id',
        'model_id',
        'manufacturer_id',
        'category_id',
        'status_id',
        'created_start',
        'created_end',
        'purchase_start',
        'purchase_end',
        'checkout_start',
        'checkout_end',
        'checkin_start',
        'checkin_end',
        'expected_checkin_start',
        'expected_checkin_end',
        'asset_eol_date_start',
        'asset_eol_date_end',
        'last_audit_start',
        'last_audit_end',
        'next_audit_start',
        'next_audit_end',
        'last_updated_start',
        'last_updated_end',
        'asset_name',
        'asset_tag',
        'serial',
        'custom_fields',
    ];

    protected $rules = [
        'name'                    => ['required', 'string', 'max:255'],
        'created_by'              => ['required', 'string'],
        'company_id'              => ['nullable', 'integer', 'exists:companies,id'],
        'location_id'             => ['nullable', 'integer', 'exists:locations,id', 'fmcs_location'],
        'rtd_location_id'         => ['nullable', 'integer', 'exists:locations,id', 'fmcs_location'],
        'supplier_id'             => ['nullable', 'integer', 'exists:suppliers,id'],
        'model_id'                => ['nullable', 'integer', 'exists:models,id,deleted_at,NULL', 'not_array'],
        'manufacturer_id'         => ['nullable', 'integer', 'exists:manufacturers,id'],
        'category_id'             => ['nullable', 'integer', 'exists:categories,id'],
        'status_id'               => ['nullable', 'integer', 'exists:status_labels,id'],
        'created_start'           => ['nullable', 'date'],
        'created_end'             => ['nullable', 'date'],
        'purchase_start'          => ['nullable', 'date'],
        'purchase_end'            => ['nullable', 'date'],
        'checkout_start'          => ['nullable', 'date'],
        'checkout_end'            => ['nullable', 'date'],
        'checkin_start'           => ['nullable', 'date'],
        'checkin_end'             => ['nullable', 'date'],
        'expected_checkin_start'  => ['nullable', 'date'],
        'expected_checkin_end'    => ['nullable', 'date'],
        'asset_eol_date_start'    => ['nullable', 'date'],
        'asset_eol_date_end'      => ['nullable', 'date'],
        'last_audit_start'        => ['nullable', 'date'],
        'last_audit_end'          => ['nullable', 'date'],
        'next_audit_start'        => ['nullable', 'date'],
        'next_audit_end'          => ['nullable', 'date'],
        'last_updated_start'      => ['nullable', 'date'],
        'last_updated_end'        => ['nullable', 'date'],
        'asset_name'              => ['nullable', 'string'],
        'asset_tag'               => ['nullable', 'integer'],
        'serial'                  => ['nullable', 'unique_undeleted:assets,serial'],
        'custom_fields'           => ['nullable', 'array'],
    ];

    public function filterAssets(Builder $assets) {
        if (isset($this['company_id'])) {
            $assets->whereIn('assets.company_id', $this['company_id']);
        }
        if (isset($this['location_id'])) {
            $assets->byLocationId($this['location_id']);
        }
        if (isset($this['rtd_location_id'])) {
            $assets->whereIn('assets.rtd_location_id', $this['rtd_location_id'] );
        }
        // TODO: DEPARTMENT ?
        if (isset($this['supplier_id'])) {
            $assets->whereIn('assets.supplier_id', $this['supplier_id']);
        }
        // Model No. already included
        if (isset($this['model_id'])) {
            $assets->whereIn('assets.model_id', $this['model_id']);
        }
        if (isset($this['manufacturer_id'])) {
            $assets->byManufacturer($this['manufacturer_id']);
        }
        if (isset($this['category_id'])) {
            $assets->inCategory($this['category_id']);
        }
        if (isset($this['status_id'])) {
            $assets->whereIn('assets.status_id', $this['status_id']);
        }
        if (isset($this['created_start'])) {
            $assets->whereDate("assets.created_at", '>=', $this['created_start']);
        }
        if (isset($this['created_end'])) {
            $assets->whereDate("assets.created_at", '<=', $this['created_end']);
        }
        if (isset($this['purchase_start'])) {
            $assets->whereDate("assets.purchase_date", '>=', $this['purchase_start']);
        }
        if (isset($this['purchase_end'])) {
            $assets->whereDate("assets.purchase_date", '<=', $this['purchase_end']);
        }
        if (isset($this['checkout_start'])) {
            $assets->whereDate("assets.last_checkout", '>=', $this['checkout_start']);
        }
        if (isset($this['checkout_end'])) {
            $assets->whereDate("assets.last_checkout", '<=', $this['checkout_end']);
        }
        if (isset($this['checkin_start'])) {
            $assets->whereDate("assets.last_checkin", '>=', $this['checkin_start']);
        }
        if (isset($this['checkin_end'])) {
            $assets->whereDate("assets.last_checkin", '<=', $this['checkin_end']);
        }
        if (isset($this['expected_checkin_start'])) {
            $assets->whereDate("assets.expected_checkin", '>=', $this['expected_checkin_start']);
        }
        if (isset($this['expected_checkin_end'])) {
            $assets->whereDate("assets.expected_checkin", '<=', $this['expected_checkin_end']);
        }
        if (isset($this['asset_eol_date_start'])) {
            $assets->whereDate("assets.asset_eol_date", '>=', $this['asset_eol_date_start']);
        }
        if (isset($this['asset_eol_date_end'])) {
            $assets->whereDate("assets.asset_eol_date", '<=', $this['asset_eol_date_end']);
        }
        if (isset($this['last_audit_start'])) {
            $assets->whereDate("assets.last_audit_date", '>=', $this['last_audit_start']);
        }
        if (isset($this['last_audit_end'])) {
            $assets->whereDate("assets.last_audit_date", '<=', $this['last_audit_end']);
        }
        if (isset($this['next_audit_start'])) {
            $assets->whereDate("assets.next_audit_date", '>=', $this['next_audit_start']);
        }
        if (isset($this['next_audit_end'])) {
            $assets->whereDate("assets.next_audit_date", '<=', $this['next_audit_end']);
        }
        if (isset($this['last_updated_start'])) {
            $assets->whereDate("assets.updated_at", '>=', $this['last_updated_start']);
        }
        if (isset($this['last_updated_end'])) {
            $assets->whereDate("assets.updated_at", '<=', $this['last_updated_end']);
        }
        if (isset($this['asset_name'])) {
            $assets->whereLike('assets.name', '%' . $this['asset_name'] . '%', caseSensitive: false);
        }
        if (isset($this['asset_tag'])) {
            $assets->whereLike('assets.asset_tag', '%' . $this['asset_tag'] . '%', caseSensitive: false);
        }
        if (isset($this['serial'])) {
            $assets->whereLike('assets.serial', '%' . $this['serial'] . '%', caseSensitive: false);
        }

        if (isset($this['custom_fields'])) {
            foreach ($this['custom_fields'] as $key => $value) {
                // if in custom_fiels the key is set to the customfields db_column then:
                $assets->where($key , '=' , $value);
            }
        }
        return $assets;
    }
}
