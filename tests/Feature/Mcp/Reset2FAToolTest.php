<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\Reset2FATool;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class Reset2FAToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->editUsers()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new Reset2FATool)->handle(new Request($args));
    }

    public function test_resets_two_factor_for_user()
    {
        $user = User::factory()->create([
            'two_factor_enrolled' => 1,
            'two_factor_optin' => 1,
            'two_factor_secret' => 'somesecret',
        ]);

        $content = $this->handle(['id' => $user->id])->getStructuredContent();

        $this->assertTrue($content['success']);

        $user->refresh();
        $this->assertEquals(0, $user->two_factor_enrolled);
        $this->assertEquals(0, $user->two_factor_optin);
        $this->assertNull($user->two_factor_secret);
    }

    public function test_returns_error_when_user_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $user = User::factory()->create(['two_factor_enrolled' => 1]);

        $this->assertTrue($this->handle(['id' => $user->id])->responses()->first()->isError());

        $user->refresh();
        $this->assertEquals(1, $user->two_factor_enrolled);
    }
}
