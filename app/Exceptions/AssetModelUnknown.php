<?php

namespace App\Exceptions;

class AssetModelUnknown extends \Exception
{
    public function __construct()
    {
        parent::__construct(trans('admin/hardware/general.model_invalid_fix'));
    }

}