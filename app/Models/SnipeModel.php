<?php

namespace App\Models;

use App\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class SnipeModel extends Model
{
    // Setters that are appropriate across multiple models.
    public function setPurchaseDateAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['purchase_date'] = $value;
    }


    protected function purchaseDateFormatted(): Attribute
    {
        return Attribute:: make(
            get: fn(mixed $value, array $attributes) => $attributes['purchase_date']  ? Helper::getFormattedDateObject(Carbon::parse($attributes['purchase_date']), 'date', false) : null,
        );
    }


    protected function expiresDiffInDays(): Attribute
    {
        return Attribute:: make(
            get: fn(mixed $value, array $attributes) => array_key_exists('expiration_date', $attributes) ? Carbon::now()->diffInDays($attributes['expiration_date']) : null,
        );
    }


    protected function expiresDiffForHumans(): Attribute
    {
        return Attribute:: make(
            get: fn(mixed $value, array $attributes) => array_key_exists('expiration_date', $attributes) ? Carbon::parse($attributes['expiration_date'])->diffForHumans() : null,
        );
    }

    protected function expiresFormattedDate(): Attribute
    {
        return Attribute:: make(
            get: fn(mixed $value, array $attributes) => array_key_exists('expiration_date', $attributes) ? Helper::getFormattedDateObject($attributes['expiration_date'], 'date', false) : null,
        );
    }

    /**
     * @param $value
     */
    public function setPurchaseCostAttribute($value)
    {
        if (is_numeric($value)) {
            //value is *already* a floating-point number. Just assign it directly
            $this->attributes['purchase_cost'] = $value;
            return;
        }
        $value = Helper::ParseCurrency($value);

        if ($value == 0) {
            $value = null;
        }
        $this->attributes['purchase_cost'] = $value;
    }

    public function setLocationIdAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['location_id'] = $value;
    }

    public function setCategoryIdAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['category_id'] = $value;
        // dd($this->attributes);
    }

    public function setSupplierIdAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['supplier_id'] = $value;
    }

    public function setDepreciationIdAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['depreciation_id'] = $value;
    }

    public function setManufacturerIdAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['manufacturer_id'] = $value;
    }

    public function setMinAmtAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['min_amt'] = $value;
    }

    public function setParentIdAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['parent_id'] = $value;
    }

    public function setFieldSetIdAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['fieldset_id'] = $value;
    }

    public function setCompanyIdAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['company_id'] = $value;
    }

    public function setWarrantyMonthsAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['warranty_months'] = $value;
    }

    public function setRtdLocationIdAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['rtd_location_id'] = $value;
    }

    public function setDepartmentIdAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['department_id'] = $value;
    }

    public function setManagerIdAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['manager_id'] = $value;
    }

    public function setModelIdAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['model_id'] = $value;
    }

    public function setStatusIdAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['status_id'] = $value;
    }

    /**
     * Applies offset (from request) and limit to query.
     *
     * @param  Builder  $query
     * @param  int  $total
     * @return void
     */
    public function scopeApplyOffsetAndLimit(Builder $query, int $total)
    {
        $offset = (Request::input('offset') > $total) ? $total : app('api_offset_value');
        $limit = app('api_limit_value');

        $query->skip($offset)->take($limit);
    }

    protected function displayName(): Attribute
    {
        return Attribute:: make(
            get: fn(mixed $value) => $this->name,
        );
    }


    public function getEula()
    {

        // This is - for now - only for assets, where the asset model is the thing tied to the category
        if (($this->model) && ($this->model->category)) {
            if (($this->model->category->eula_text) && ($this->model->category->use_default_eula == 0)) {
                return $this->model->category->eula_text;
            } elseif ($this->model->category->use_default_eula == 1) {
                return Setting::getSettings()->default_eula_text;
            } else {
                return false;
            }
        // For everything else, just check the category for EULA info
        } elseif (($this->category) && ($this->category->eula_text)) {
            return $this->category->eula_text;
        } elseif ((Setting::getSettings()->default_eula_text) && (($this->category) && ($this->category->use_default_eula == '1'))) {
            return Setting::getSettings()->default_eula_text;
        }

        return null;
    }


}
