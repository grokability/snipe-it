<?php

namespace Tests\Feature\Auth;

use App\Auth\Passport\AuthorizationController;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PassportAuthorizationControllerBindingTest extends TestCase
{
    #[Test]
    public function override_controller_can_be_resolved_from_the_container(): void
    {
        $resolved = app(AuthorizationController::class);

        $this->assertInstanceOf(AuthorizationController::class, $resolved);
    }
}
