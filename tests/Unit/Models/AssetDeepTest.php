<?php

namespace Tests\Unit\Models;

use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

class AssetDeepTest extends TestCase
{
    public function test_assigned_type_returns_null_when_unassigned(): void
    {
        $asset = Asset::factory()->create(['assigned_type' => null]);

        $this->assertNull($asset->assignedType());
    }

    public function test_assigned_type_user(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create(['assigned_to' => $user->id, 'assigned_type' => User::class]);

        $this->assertEquals('user', $asset->assignedType());
    }

    public function test_target_show_route_maps_asset_to_hardware(): void
    {
        $asset = Asset::factory()->create(['assigned_type' => Asset::class]);
        $this->assertEquals('hardware', $asset->targetShowRoute());

        $userAsset = Asset::factory()->create(['assigned_type' => User::class]);
        $this->assertEquals('users', $userAsset->targetShowRoute());

        $locAsset = Asset::factory()->create(['assigned_type' => Location::class]);
        $this->assertEquals('locations', $locAsset->targetShowRoute());
    }

    public function test_get_image_url_false_without_image(): void
    {
        $asset = Asset::factory()->create(['image' => null]);

        $this->assertFalse($asset->getImageUrl());
    }

    public function test_validation_rules_returns_array(): void
    {
        $asset = Asset::factory()->create();

        $this->assertIsArray($asset->validationRules());
    }

    public function test_custom_field_validation_rules_returns_array(): void
    {
        $asset = Asset::factory()->create();

        $this->assertIsArray($asset->customFieldValidationRules());
    }

    public function test_next_auto_increment_returns_int_or_null(): void
    {
        Asset::factory()->count(3)->create();

        // Devuelve el siguiente entero, o null si no hay tags numericos.
        $result = Asset::nextAutoIncrement(Asset::all());
        $this->assertTrue(is_int($result) || is_null($result));
    }
}
