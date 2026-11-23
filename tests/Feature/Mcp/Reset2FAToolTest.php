<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\Reset2FATool;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class Reset2FAToolTest extends TestCase
{
    protected function enableSiteTwoFactor(): void
    {
        $this->settings->set(['two_factor_enabled' => 1]);
    }

    protected function makeEnrolledTarget(): User
    {
        return User::factory()->create([
            'activated' => 1,
            'two_factor_optin' => 1,
            'two_factor_enrolled' => 1,
            'two_factor_secret' => 'target-seed',
        ]);
    }

    private function handle(array $args): ResponseFactory
    {
        return (new Reset2FATool)->handle(new Request($args));
    }

    public function test_resets_two_factor_for_user()
    {
        $this->enableSiteTwoFactor();
        $actor = User::factory()->editUsers()->create();
        $this->actingAs($actor);

        $target = $this->makeEnrolledTarget();

        $content = $this->handle(['id' => $target->id])->getStructuredContent();

        $this->assertTrue($content['success']);

        $target->refresh();
        $this->assertEquals(0, $target->two_factor_enrolled);
        $this->assertEquals(0, $target->two_factor_optin);
        $this->assertNull($target->two_factor_secret);

        // Matches Users\UsersController::twoFactorReset: a 2FA reset
        // must write a '2FA reset' action log.
        $this->assertDatabaseHas('action_logs', [
            'item_type' => User::class,
            'item_id' => $target->id,
            'created_by' => $actor->id,
            'action_type' => '2FA reset',
        ]);
    }

    public function test_returns_error_when_user_not_found()
    {
        $this->actingAs(User::factory()->editUsers()->create());
        $this->assertTrue($this->handle(['id' => 999999])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->enableSiteTwoFactor();
        $this->actingAs(User::factory()->create());

        $target = $this->makeEnrolledTarget();

        $this->assertTrue($this->handle(['id' => $target->id])->responses()->first()->isError());

        $target->refresh();
        $this->assertEquals(1, $target->two_factor_enrolled);
    }

    public function test_non_admin_cannot_reset_two_factor_on_admin()
    {
        $this->enableSiteTwoFactor();
        $this->actingAs(User::factory()->editUsers()->create());

        $target = User::factory()->admin()->create([
            'activated' => 1,
            'two_factor_optin' => 1,
            'two_factor_enrolled' => 1,
            'two_factor_secret' => 'admin-seed',
        ]);

        $this->assertTrue($this->handle(['id' => $target->id])->responses()->first()->isError());
        $this->assertEquals('admin-seed', $target->fresh()->two_factor_secret);
        $this->assertDatabaseMissing('action_logs', [
            'item_id' => $target->id,
            'action_type' => '2FA reset',
        ]);
    }

    public function test_non_admin_cannot_reset_two_factor_on_superuser()
    {
        $this->enableSiteTwoFactor();
        $this->actingAs(User::factory()->editUsers()->create());

        $target = User::factory()->superuser()->create([
            'activated' => 1,
            'two_factor_optin' => 1,
            'two_factor_enrolled' => 1,
            'two_factor_secret' => 'super-seed',
        ]);

        $this->assertTrue($this->handle(['id' => $target->id])->responses()->first()->isError());
        $this->assertEquals('super-seed', $target->fresh()->two_factor_secret);
        $this->assertDatabaseMissing('action_logs', [
            'item_id' => $target->id,
            'action_type' => '2FA reset',
        ]);
    }

    public function test_returns_error_when_target_is_not_enrolled()
    {
        $this->enableSiteTwoFactor();
        $this->actingAs(User::factory()->editUsers()->create());

        $target = User::factory()->create([
            'activated' => 1,
            'two_factor_enrolled' => 0,
        ]);

        $this->assertTrue($this->handle(['id' => $target->id])->responses()->first()->isError());
    }

    public function test_returns_error_when_site_two_factor_is_off()
    {
        $this->settings->set(['two_factor_enabled' => null]);
        $this->actingAs(User::factory()->editUsers()->create());

        $target = $this->makeEnrolledTarget();

        $this->assertTrue($this->handle(['id' => $target->id])->responses()->first()->isError());
        $this->assertEquals('target-seed', $target->fresh()->two_factor_secret);
    }
}
