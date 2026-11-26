<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Bridge\Client as BridgeClient;
use Laravel\Passport\Bridge\User as BridgeUser;
use Tests\TestCase;
use TypeError;

class LegacyPassportSessionTest extends TestCase
{
    public function test_legacy_passport_session_is_replaced_without_reporting_an_error(): void
    {
        User::factory()->create();
        $session = app('session')->driver();
        $oldId = $session->getId();
        $session->getHandler()->write($oldId, $this->legacySessionPayload(new BridgeUser('123')));

        $this->withUnencryptedCookie($session->getName(), $oldId)
            ->get(route('home'))
            ->assertRedirect(route('login'));

        $this->assertNotSame($oldId, $session->getId());
        $this->assertSame('', $session->getHandler()->read($oldId));
        $this->assertFalse($session->has('authRequest'));
        $this->assertNotEmpty($session->token());

        $this->withUnencryptedCookie($session->getName(), $session->getId())
            ->get(route('login'))
            ->assertOk();
    }

    public function test_current_passport_session_is_preserved(): void
    {
        User::factory()->create();
        $session = app('session')->driver();
        $oldId = $session->getId();
        $session->getHandler()->write($oldId, serialize(['authRequest' => new BridgeUser('123')]));

        $this->withUnencryptedCookie($session->getName(), $oldId)
            ->get(route('login'))
            ->assertOk();

        $this->assertSame($oldId, $session->getId());
        $this->assertSame('123', $session->get('authRequest')->getIdentifier());
    }

    public function test_other_session_type_errors_are_not_suppressed(): void
    {
        User::factory()->create();
        $session = app('session')->driver();
        $session->getHandler()->write($session->getId(), $this->legacySessionPayload(new BridgeClient('123')));

        $this->withoutExceptionHandling();
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Cannot assign int to property Laravel\\Passport\\Bridge\\Client::$identifier of type string');

        $this->withUnencryptedCookie($session->getName(), $session->getId())->get(route('login'));
    }

    public function test_matching_error_from_application_code_is_not_suppressed(): void
    {
        User::factory()->create();
        Route::get('/test-application-type-error', function (): void {
            throw new TypeError('Cannot assign int to property Laravel\\Passport\\Bridge\\User::$identifier of type string');
        });

        $this->withoutExceptionHandling();
        $this->expectException(TypeError::class);

        $this->get('/test-application-type-error');
    }

    protected function legacySessionPayload(BridgeUser|BridgeClient $entity): string
    {
        return str_replace('s:3:"123";', 'i:123;', serialize(['authRequest' => $entity]));
    }
}
