<?php

namespace App\Models\Labels;

use App\Models\Asset;
use Illuminate\Support\Collection;

class FieldOption
{
    protected string $label;
    public function getLabel()
    {
        return $this->label; 
    }

    protected string $dataSource;
    public function getDataSource()
    {
        return $this->dataSource; 
    }

    public function getValue(Asset $asset)
    {
        $dataPath = collect(explode('.', $this->dataSource));

        // assignedTo directly on the asset is a special case where
        // we want to avoid returning the property directly
        // and instead return the entity's presented name.
        if ($dataPath[0] === 'assignedTo') {
            if ($asset->relationLoaded('assignedTo')) {
                // If the "assignedTo" relationship was eager loaded then the way to get the
                // relationship changes from $asset->assignedTo to $asset->assigned.
                return $asset->assigned ? $asset->assigned->display_name : null;
            }

            return $asset->assignedTo ? $asset->assignedTo->display_name : null;
        }

        // Handle Laravel's stupid Carbon datetime casting
        if ($dataPath[0] === 'purchase_date') {
            return $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : null;
        }
        
        return $dataPath->reduce(
            function ($myValue, $path) {
                try { return $myValue ? $myValue->{$path} : ${$myValue}; 
                }
                catch (\Exception $e) { return $myValue; 
                }
            }, $asset
        );
    }

    public function toArray(Asset $asset=null)
    {
        return FieldOption::makeArray($this, $asset); 
    }
    public function toString()
    {
        return FieldOption::makeString($this); 
    }

    /* Statics */

    public static function makeArray(FieldOption $option, Asset $asset=null)
    {
        return [
            'label' => $option->getLabel(),
            'dataSource' => $option->getDataSource(),
            'value' => $asset ? $option->getValue($asset) : null
        ];
    }

    public static function makeString(FieldOption $option)
    {
        return $option->getLabel() . '=' . $option->getDataSource();
    }

    public static function fromString(string $theString)
    {
        $parts = explode('=', $theString);
        if (count($parts) == 2) {
            $option = new FieldOption();
            $option->label = $parts[0];
            $option->dataSource = $parts[1];
            return $option;
        }
    }
}
