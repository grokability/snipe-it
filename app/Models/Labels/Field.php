<?php

namespace App\Models\Labels;

use App\Models\Asset;
use Illuminate\Support\Collection;

class Field
{
    protected Collection $options;
    public function getOptions()
    {
        return $this->options; 
    }
    public function setOptions($options)
    {
        $tempCollect = collect($options);
        if (!$tempCollect->contains(fn($o) => !is_subclass_of($o, FieldOption::class))) {
            $this->options = $options;
        }
    }
    
    public function toArray(Asset $asset)
    {
        return Field::makeArray($this, $asset); 
    }

    /* Statics */

    public static function makeArray(Field $field, Asset $asset)
    {
        return $field->getOptions()
            // filter out any FieldOptions that are accidentally null
            ->filter()
            ->map(fn($option) => $option->toArray($asset))
            ->filter(fn($result) => $result['value'] != null);
    }

    public static function makeString(Field $option)
    {
        return implode('|', $option->getOptions());
    }

    public static function fromString(string $theString)
    {
        $field = new Field();
        $field->options = collect(explode('|', $theString))
            ->filter(fn($optionString) => !empty($optionString))
            ->map(fn($optionString) => FieldOption::fromString($optionString));
        return $field;
    }
}
