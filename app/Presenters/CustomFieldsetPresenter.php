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
            return (string)link_to_route('fieldsets.show', e($this->display_name), $this->id);
        } else {
            return e($this->display_name);
        }
    }


}
