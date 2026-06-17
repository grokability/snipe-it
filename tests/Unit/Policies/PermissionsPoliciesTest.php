<?php

namespace Tests\Unit\Policies;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Consumable;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Cubre SnipePermissionsPolicy (base de la mayoria de policies) y
 * CheckoutablePermissionsPolicy a traves del Gate.
 */
class PermissionsPoliciesTest extends TestCase
{
    public static function abilityProvider(): array
    {
        return [
            ['view'], ['create'], ['update'], ['delete'], ['checkout'], ['index'],
        ];
    }

    #[DataProvider('abilityProvider')]
    public function test_superuser_is_allowed_every_ability(string $ability): void
    {
        $super = User::factory()->superuser()->create();

        // before() concede todo al superusuario.
        $this->assertTrue(Gate::forUser($super)->allows($ability, Asset::class));
    }

    #[DataProvider('abilityProvider')]
    public function test_regular_user_without_permissions_is_denied(string $ability): void
    {
        $regular = User::factory()->create();

        $this->assertFalse(Gate::forUser($regular)->allows($ability, Asset::class));
    }

    public function test_user_with_specific_permission_is_allowed(): void
    {
        $user = User::factory()->create(['permissions' => json_encode(['assets.view' => '1'])]);

        $this->assertTrue(Gate::forUser($user)->allows('view', Asset::class));
        $this->assertFalse(Gate::forUser($user)->allows('delete', Asset::class));
    }

    public function test_checkoutable_checkin_and_checkout_for_superuser(): void
    {
        $super = User::factory()->superuser()->create();

        $this->assertTrue(Gate::forUser($super)->allows('checkin', Accessory::class));
        $this->assertTrue(Gate::forUser($super)->allows('checkout', Consumable::class));
    }

    public function test_checkoutable_denied_for_regular_user(): void
    {
        $regular = User::factory()->create();

        $this->assertFalse(Gate::forUser($regular)->allows('checkin', Accessory::class));
    }
}
