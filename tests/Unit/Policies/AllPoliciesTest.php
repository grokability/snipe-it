<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Ejercita cada Policy del proyecto a traves del Gate (view), con superusuario
 * (permitido via before) y usuario regular sin permisos (denegado). Cubre las
 * clases de policy pequenas que solo declaran el modelo/columna.
 */
class AllPoliciesTest extends TestCase
{
    public static function modelProvider(): array
    {
        return [
            ['App\Models\Accessory'],
            ['App\Models\AssetModel'],
            ['App\Models\Asset'],
            ['App\Models\Category'],
            ['App\Models\Company'],
            ['App\Models\Component'],
            ['App\Models\Consumable'],
            ['App\Models\CustomField'],
            ['App\Models\CustomFieldset'],
            ['App\Models\Department'],
            ['App\Models\Depreciation'],
            ['App\Models\License'],
            ['App\Models\Location'],
            ['App\Models\Manufacturer'],
            ['App\Models\PredefinedKit'],
            ['App\Models\Statuslabel'],
            ['App\Models\Supplier'],
            ['App\Models\User'],
        ];
    }

    #[DataProvider('modelProvider')]
    public function test_view_policy_for_super_and_regular(string $modelClass): void
    {
        $super = User::factory()->superuser()->create();
        $regular = User::factory()->create();

        $this->assertTrue(Gate::forUser($super)->allows('view', $modelClass));
        $this->assertFalse(Gate::forUser($regular)->allows('view', $modelClass));
    }
}
