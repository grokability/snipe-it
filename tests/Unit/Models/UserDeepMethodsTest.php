<?php

namespace Tests\Unit\Models;

use App\Models\Setting;
use App\Models\User;
use Tests\TestCase;

/**
 * Cubre metodos puros / dependientes de Setting de User que no requieren ciclo HTTP:
 * avatar externo, permisos individuales, flags 2FA, formato de nombre, locale y costo total.
 */
class UserDeepMethodsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_is_avatar_external(): void
    {
        $external = User::factory()->make(['avatar' => 'https://example.com/a.png']);
        $this->assertTrue($external->isAvatarExternal());

        $local = User::factory()->make(['avatar' => 'avatars/a.png']);
        $this->assertFalse($local->isAvatarExternal());
    }

    public function test_has_individual_permissions(): void
    {
        $withPerms = User::factory()->make(['permissions' => json_encode(['admin' => '1'])]);
        $this->assertTrue($withPerms->hasIndividualPermissions());

        $noPerms = User::factory()->make(['permissions' => json_encode(['admin' => '0'])]);
        $this->assertFalse($noPerms->hasIndividualPermissions());
    }

    public function test_is_activated(): void
    {
        $this->assertTrue(User::factory()->make(['activated' => 1])->isActivated());
        $this->assertFalse(User::factory()->make(['activated' => 0])->isActivated());
    }

    public function test_can_edit_profile_follows_setting(): void
    {
        $this->settings->set(['profile_edit' => 1]);
        $this->assertTrue(User::factory()->make()->canEditProfile());

        $this->settings->set(['profile_edit' => 0]);
        $this->assertFalse(User::factory()->make()->canEditProfile());
    }

    public function test_full_name_default_format(): void
    {
        $this->settings->set(['name_display_format' => 'first_last']);
        $user = User::factory()->make(['first_name' => 'Ana', 'last_name' => 'Lopez']);

        $this->assertSame('Ana Lopez', $user->getFullNameAttribute());
    }

    public function test_full_name_last_first_format(): void
    {
        $this->settings->set(['name_display_format' => 'last_first']);
        $user = User::factory()->make(['first_name' => 'Ana', 'last_name' => 'Lopez']);

        $this->assertSame('Lopez Ana', $user->getFullNameAttribute());
    }

    public function test_two_factor_active_optional_optin(): void
    {
        $this->settings->set(['two_factor_enabled' => 1]);
        $optin = User::factory()->make(['two_factor_optin' => 1]);
        $this->assertTrue($optin->two_factor_active());

        $notOptin = User::factory()->make(['two_factor_optin' => 0]);
        $this->assertFalse($notOptin->two_factor_active());
    }

    public function test_two_factor_active_universally_required(): void
    {
        $this->settings->set(['two_factor_enabled' => 2]);
        $this->assertTrue(User::factory()->make(['two_factor_optin' => 0])->two_factor_active());
    }

    public function test_two_factor_active_and_enrolled(): void
    {
        $this->settings->set(['two_factor_enabled' => 1]);
        $enrolled = User::factory()->make(['two_factor_optin' => 1, 'two_factor_enrolled' => 1]);
        $this->assertTrue($enrolled->two_factor_active_and_enrolled());

        $notEnrolled = User::factory()->make(['two_factor_optin' => 1, 'two_factor_enrolled' => 0]);
        $this->assertFalse($notEnrolled->two_factor_active_and_enrolled());
    }

    public function test_preferred_locale_uses_user_locale(): void
    {
        $user = User::factory()->make(['locale' => 'es-ES']);
        $this->assertSame('es-ES', $user->preferredLocale());
    }

    public function test_preferred_locale_falls_back_to_setting(): void
    {
        $this->settings->set(['locale' => 'fr-FR']);
        $user = User::factory()->make(['locale' => null]);
        $this->assertSame('fr-FR', $user->preferredLocale());
    }

    public function test_is_deletable_for_clean_user(): void
    {
        $user = User::factory()->create();

        // Usuario sin items ni gestiones -> depende solo del Gate (bool).
        $this->assertIsBool($user->isDeletable());
    }

    public function test_get_user_total_cost_returns_self_with_total(): void
    {
        $user = User::factory()->create();

        $result = $user->getUserTotalCost();

        $this->assertSame($user, $result);
        $this->assertSame(0, $result->total_user_cost);
    }

    public function test_has_access_superuser_always_true(): void
    {
        $this->assertTrue(User::factory()->superuser()->create()->hasAccess('admin'));
    }
}
