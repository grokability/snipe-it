<?php

namespace Tests\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Passport\Passport;

trait InteractsWithAuthentication
{
    public function actingAsForApi(Authenticatable $user)
    {
        Passport::actingAs($user);

        return $this;
    }
}
