<?php

namespace Tests\Feature\Assets\Ui;

use App\Models\Asset;
use App\Models\User;
use Tests\TestCase;

class AuditAssetTest extends TestCase
{
    public function testPermissionRequiredToCreateAssetModel()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('clone/hardware', Asset::factory()->create()))
            ->assertForbidden();
    }

    public function testPageCanBeAccessed(): void
    {
        $this->actingAs(User::factory()->auditAssets()->create())
            ->get(route('asset.audit.create', Asset::factory()->create()))
            ->assertStatus(200);
    }

    public function testAssetAuditPostIsRedirectedToAssetIndexIfRedirectSelectionIsIndex()
    {
        $asset = Asset::factory()->create();

        $response = $this->actingAs(User::factory()->viewAssets()->editAssets()->auditAssets()->create())
            ->from(route('asset.audit.create', $asset))
            ->post(route('asset.audit.store', $asset),
                [
                    'redirect_option' => 'index',
                ])
            ->assertStatus(302)
            ->assertRedirect(route('hardware.index'));
        $this->followRedirects($response)->assertSee('success');

        $this->assertHasTheseActionLogs($asset, ['create', 'audit']);
    }

    public function testAssetAuditPostIsRedirectedToAssetPageIfRedirectSelectionIsAsset()
    {
        $asset = Asset::factory()->create();

        $response = $this->actingAs(User::factory()->viewAssets()->editAssets()->auditAssets()->create())
            ->from(route('asset.audit.create', $asset))
            ->post(route('asset.audit.store', $asset),
                [
                    'redirect_option' => 'item',
                ])
            ->assertStatus(302)
            ->assertRedirect(route('hardware.show', $asset));
        $this->followRedirects($response)->assertSee('success');
        $this->assertHasTheseActionLogs($asset, ['create', 'audit']); // WAT.
    }

    public function testAssetAuditPostIsRedirectedToAuditDuePageIfRedirectSelectionIsList()
    {
        $asset = Asset::factory()->create();

        $response = $this->actingAs(User::factory()->viewAssets()->editAssets()->auditAssets()->create())
            ->from(route('asset.audit.create', $asset))
            ->post(route('asset.audit.store', $asset),
                [
                    'redirect_option' => 'other_redirect',
                ])
            ->assertStatus(302)
            ->assertRedirect(route('assets.audit.due'));
        $this->followRedirects($response)->assertSee('success');
        $this->assertHasTheseActionLogs($asset, ['create', 'audit']);
    }

}
