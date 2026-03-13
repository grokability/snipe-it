<?php

namespace App\Presenters;

/**
 * Class LicensePresenter
 */
class LicenseSeatPresenter extends Presenter
{
    public function name()
    {
        return $this->model->license->name;
    }
}
