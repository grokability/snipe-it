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

//    public function name()
//    {
//        return $this->model->name;
//    }
//
//    public function display_name()
//    {
//        return $this->model->display_name;
//    }


//    protected function displayName(): Attribute
//    {
//        // This override should only kick in if the model has a display_name prope
//        if ($this->getRawOriginal('display_name')) {
//            return Attribute:: make (
//                get: fn(mixed $value) => 'Poop:'.$this->display_name
//            );
//        }
//
//        return Attribute:: make(
//            get: fn(mixed $value) => 'Fart: '.$this->name,
//        );
//    }

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
