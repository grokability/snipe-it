<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

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


    protected function displayName(): Attribute
    {
        return Attribute:: make(
            get: fn(mixed $value) => $this->name,
        );
    }


}
