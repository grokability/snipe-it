<?php

namespace Tests\Unit\Presenters;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ActionlogPresenterTest extends TestCase
{
    public static function iconProvider(): array
    {
        return [
            // item_type, action_type
            'user 2fa reset'      => [User::class, '2fa reset'],
            'user create'         => [User::class, 'create'],
            'user merged'         => [User::class, 'merged'],
            'user delete'         => [User::class, 'delete'],
            'user upload deleted' => [User::class, 'upload deleted'],
            'user update'         => [User::class, 'update'],
            'user default'        => [User::class, 'whatever'],
            'item create'         => [Asset::class, 'create'],
            'item delete'         => [Asset::class, 'delete'],
            'item update'         => [Asset::class, 'update'],
            'item restore'        => [Asset::class, 'restore'],
            'item upload'         => [Asset::class, 'upload'],
            'item checkout'       => [Asset::class, 'checkout'],
            'item checkin from'   => [Asset::class, 'checkin from'],
            'item note added'     => [Asset::class, 'note added'],
            'item audit'          => [Asset::class, 'audit'],
            'item default'        => [Asset::class, 'something else'],
        ];
    }

    #[DataProvider('iconProvider')]
    public function test_icon_returns_a_fontawesome_class(string $itemType, string $actionType): void
    {
        $log = new Actionlog(['item_type' => $itemType, 'action_type' => $actionType]);

        $icon = $log->present()->icon();

        $this->assertIsString($icon);
        $this->assertStringContainsString('fa', $icon);
    }

    public function test_action_type_is_translated_and_lowercased(): void
    {
        $log = new Actionlog(['action_type' => 'checkout']);

        $this->assertEquals('checkout', $log->present()->actionType());
    }

    public function test_adminuser_returns_empty_string_without_user(): void
    {
        $log = new Actionlog([]);

        $this->assertSame('', $log->present()->adminuser());
    }

    public function test_adminuser_links_to_user(): void
    {
        $this->actingAs(User::factory()->superuser()->create());

        $user = User::factory()->create();
        $asset = Asset::factory()->for(AssetModel::factory(), 'model')->create();
        $log = new Actionlog([
            'action_type' => 'update',
            'item_id' => $asset->id,
            'item_type' => Asset::class,
            'target_id' => $user->id,
            'target_type' => User::class,
        ]);
        $log->save();
        $log->refresh();

        $this->assertStringContainsString('<a href=', $log->present()->adminuser());
    }

    public function test_item_returns_empty_string_without_item(): void
    {
        $log = new Actionlog([]);

        $this->assertSame('', $log->present()->item());
    }
}
