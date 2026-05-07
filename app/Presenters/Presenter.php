<?php

namespace App\Presenters;

use App\Models\Asset;
use App\Models\Setting;
use App\Models\SnipeModel;

abstract class Presenter
{
    /**
     * @var SnipeModel
     */
    protected $model;

    /**
     * Presenter constructor.
     */
    public function __construct(SnipeModel $model)
    {
        $this->model = $model;
    }

    public function displayAddress()
    {
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

    /**
     * Used to take user created URL and dynamically fill in the needed values per item
     *
     * @return string
     */
    public function dynamicUrl($dynamic_url)
    {
        $url = (str_replace('{LOCALE}', Setting::getSettings()->locale, $dynamic_url));

        if ($this->model instanceof Asset) {
            $url = (str_replace('{SERIAL}', urlencode($this->model->serial), $url));
            $url = (str_replace('{MODEL_NAME}', urlencode($this->model->model->name), $url));
            $url = (str_replace('{MODEL_NUMBER}', urlencode($this->model->model->model_number), $url));

            return $url;
        }

        $url = (str_replace('{SERIAL}', urlencode($this->serial), $url));
        $url = (str_replace('{MODEL_NAME}', urlencode($this->model_name), $url));
        $url = (str_replace('{MODEL_NUMBER}', urlencode($this->model_number), $url));

        return $url;

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
