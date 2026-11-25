<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\DeleteAccessoryTool;
use App\Mcp\Tools\DeleteAssetTool;
use App\Mcp\Tools\DeleteComponentTool;
use App\Mcp\Tools\DeleteConsumableTool;
use App\Mcp\Tools\DeleteLicenseTool;
use App\Mcp\Tools\DeleteUserTool;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\User;
use Laravel\Mcp\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DeletePreviewAuthorizationTest extends TestCase
{
    public static function toolAndFactoryProvider(): array
    {
        return [
            'accessory' => [DeleteAccessoryTool::class, Accessory::class],
            'asset' => [DeleteAssetTool::class, Asset::class],
            'component' => [DeleteComponentTool::class, Component::class],
            'consumable' => [DeleteConsumableTool::class, Consumable::class],
            'license' => [DeleteLicenseTool::class, License::class],
            'user' => [DeleteUserTool::class, User::class],
        ];
    }

    #[DataProvider('toolAndFactoryProvider')]
    public function test_caller_with_only_self_api_cannot_preview(string $toolClass, string $modelClass): void
    {
        $modelClass::factory()->create();
        $this->actingAs(User::factory()->create());

        $response = (new $toolClass)->handle(new Request(['search' => 'a']));

        $this->assertTrue($response->responses()->first()->isError());
    }
}
