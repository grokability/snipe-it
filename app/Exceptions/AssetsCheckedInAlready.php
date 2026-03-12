<?php

namespace App\Exceptions;

class AssetsCheckedInAlready extends \Exception
{
    public function __construct()
    {
        parent::__construct(trans('admin/hardware/message.checkin.already_checked_in'));
    }
}