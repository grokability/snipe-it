<?php

namespace Tests\Feature\Licenses\Api;

use App\Models\License;
use App\Models\User;
use Tests\TestCase;

class LicenseIndexSerialMaskTest extends TestCase
{
    public function test_license_index_api_masks_product_key_for_viewer_without_viewkeys(): void
    {
        $license = License::factory()->create([
            'name' => 'IndexApiLic-'.now()->timestamp,
            'serial' => 'INDEX-API-SECRET-999',
        ]);

        $viewer = User::factory()->viewLicenses()->create();

        $this->actingAsForApi($viewer)
            ->getJson(route('api.licenses.index', ['search' => $license->name]))
            ->assertOk()
            ->assertJsonFragment(['product_key' => License::PRODUCT_KEY_MASK])
            ->assertJsonMissing(['product_key' => 'INDEX-API-SECRET-999']);
    }

    public function test_license_index_api_returns_real_product_key_for_viewkeys_holder(): void
    {
        $license = License::factory()->create([
            'name' => 'IndexApiLic-'.now()->timestamp,
            'serial' => 'INDEX-API-REAL-888',
        ]);

        $viewer = User::factory()->viewLicenses()->viewKeysLicenses()->create();

        $this->actingAsForApi($viewer)
            ->getJson(route('api.licenses.index', ['search' => $license->name]))
            ->assertOk()
            ->assertJsonFragment(['product_key' => 'INDEX-API-REAL-888']);
    }

    public function test_explicit_deny_on_licenses_keys_beats_implicit_grant_from_create_or_edit(): void
    {
        // viewKeys policy allows licenses.keys OR licenses.create OR
        // licenses.edit by default (so the admin who created the
        // license can still see the key to edit it). An operator who
        // sets licenses.keys to -1 is explicitly revoking that
        // visibility, and the deny must beat the implicit grant.
        $license = License::factory()->create([
            'name' => 'IndexApiLic-'.now()->timestamp,
            'serial' => 'EXPLICIT-DENY-SECRET-444',
        ]);

        $viewer = User::factory()
            ->viewLicenses()
            ->state(fn ($state) => [
                'permissions' => json_encode(array_merge(
                    json_decode($state['permissions'], true),
                    [
                        'licenses.create' => '1',
                        'licenses.edit' => '1',
                        'licenses.keys' => '-1',
                    ],
                )),
            ])
            ->create();

        $this->actingAsForApi($viewer)
            ->getJson(route('api.licenses.index', ['search' => $license->name]))
            ->assertOk()
            ->assertJsonFragment(['product_key' => License::PRODUCT_KEY_MASK])
            ->assertJsonMissing(['product_key' => 'EXPLICIT-DENY-SECRET-444']);
    }
}
