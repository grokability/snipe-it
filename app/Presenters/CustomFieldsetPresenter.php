<?php

namespace App\Presenters;

/**
 * Class CustomFieldsetPresenter
 */
class CustomFieldsetPresenter extends Presenter
{
    public function nameUrl()
    {
        if (auth()->user()->can('view', ['\App\Models\CustomFieldset', $this])) {
            return '<a href="'.route('fieldsets.show', $this->id).'">'.e($this->display_name).'</a>';
        } else {
            return e($this->display_name);
        }
    }
}
