<?php

namespace App\Policies;

class StatuslabelPolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'statuslabels';
    }
}
