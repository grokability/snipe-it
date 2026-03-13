<?php

namespace App\Presenters;

use App\Models\SnipeModel;
use Illuminate\Database\Eloquent\Casts\Attribute;

abstract class Presenter
{
    /**
     * @var SnipeModel
     */
    protected $model;

    /**
     * Presenter constructor.
     * @param SnipeModel $model
     */
    public function __construct(SnipeModel $model)
    {
        $this->model = $model;
    }

    public function displayAddress() {
        $address = '';
        if ($this->model->address) {
            $address .= e($this->model->address)."\n";
        }

        if ($this->model->address2) {
            $address .= e($this->model->address2)."\n";
        }

        if ($this->model->city) {
            $address .= e($this->model->city).', ';
        }

        if ($this->model->state) {
            $address .= e($this->model->state).' ';
        }

        if ($this->model->zip) {
            $address .= e($this->model->zip).' ';
        }

        if ($this->model->country) {
            $address .= e($this->model->country).' ';
        }

        return $address;
    }

    // Convenience functions for datatables stuff
    public function categoryUrl()
    {
        $model = $this->model;
        // Category of Asset belongs to model.
        if ($model->model) {
            $model = $this->model->model;
        }

        if ($model->category) {
            return $model->category->present()->nameUrl();
        }

        return '';
    }

    public function locationUrl()
    {
        if ($this->model->location) {
            return $this->model->location->present()->nameUrl();
        }

        return '';
    }

    public function companyUrl()
    {
        if ($this->model->company) {
            return $this->model->company->present()->nameUrl();
        }

        return '';
    }

    public function manufacturerUrl()
    {
        $model = $this->model;
        // Category of Asset belongs to model.
        if ($model->model) {
            $model = $this->model->model;
        }

        if ($model->manufacturer) {
            return $model->manufacturer->present()->nameUrl();
        }

        return '';
    }



    public function __get($property)
    {
        if (method_exists($this, $property)) {
            return $this->{$property}();
        }

        return $this->model->{$property};
    }

    public function __call($method, $args)
    {
        return $this->model->$method($args);
    }


}
