<?php

namespace Tests\Unit\Models;

use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Component;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ActionlogLogicTest extends TestCase
{
    public static function itemTypeProvider(): array
    {
        return [
            [AssetModel::class, 'model'],
            [Asset::class, 'asset'],
            [Accessory::class, 'accessory'],
            [Component::class, 'component'],
            [User::class, 'user'],
        ];
    }

    #[DataProvider('itemTypeProvider')]
    public function test_item_type_resolves_basename(string $itemType, string $expected): void
    {
        $log = new Actionlog(['item_type' => $itemType]);

        $this->assertEquals($expected, $log->itemType());
    }

    public function test_target_type_resolves_basename(): void
    {
        $log = new Actionlog(['target_type' => User::class]);

        $this->assertEquals('user', $log->targetType());
    }

    public function test_scope_for_api_history_returns_builder(): void
    {
        Actionlog::factory()->count(2)->create();

        $this->assertNotNull(Actionlog::forApiHistory()->get());
    }
}
