<?php

namespace Tests\Unit\Presenters;

use App\Models\PredefinedKit;
use App\Models\User;
use App\Presenters\PredefinedKitPresenter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PredefinedKitPresenterTest extends TestCase
{
    public static function layoutMethodProvider(): array
    {
        return [
            ['dataTableLayout'],
            ['dataTableModels'],
            ['dataTableLicenses'],
            ['dataTableAccessories'],
            ['dataTableConsumables'],
        ];
    }

    #[DataProvider('layoutMethodProvider')]
    public function test_layout_methods_return_valid_json(string $method): void
    {
        $json = PredefinedKitPresenter::$method();

        $decoded = json_decode($json, true);
        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertIsArray($decoded);
        $this->assertNotEmpty($decoded);
    }

    public function test_name_url_renders_link(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $kit = PredefinedKit::factory()->create();

        $this->assertStringContainsString('<a href=', $kit->present()->nameUrl());
        $this->assertStringContainsString('kits/'.$kit->id, $kit->present()->nameUrl());
    }

    public function test_full_name_returns_name(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $kit = PredefinedKit::factory()->create(['name' => 'Starter Kit']);

        $this->assertEquals('Starter Kit', $kit->present()->fullName());
    }

    public function test_view_url_points_to_kits_show(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $kit = PredefinedKit::factory()->create();

        $this->assertEquals(route('kits.show', $kit->id), $kit->present()->viewUrl());
    }
}
