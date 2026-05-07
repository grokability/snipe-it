<?php

namespace Tests\Feature\Assets\Ui;

use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('auditing')]
class AuditAssetTest extends TestCase
{
    public function test_permission_required_to_view_audit_create_page()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('asset.audit.create', Asset::factory()->create()))
            ->assertForbidden();
    }

    public function test_page_can_be_accessed(): void
    {
        $this->actingAs(User::factory()->auditAssets()->create())
            ->get(route('asset.audit.create', Asset::factory()->create()))
            ->assertStatus(200);
    }

    public function test_permission_required_to_audit_asset()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('asset.audit.store', Asset::factory()->create()))
            ->assertForbidden();
    }

    public function test_audit_page_is_given_todays_date_when_audit_interval_is_null()
    {
        $this->settings->setAuditInterval(null);

        $this->actingAs(User::factory()->auditAssets()->create())
            ->get(route('asset.audit.create', Asset::factory()->create()))
            ->assertViewIs('hardware.audit')
            ->assertViewHas('next_audit_date', Carbon::now()->toDateString());
    }

    public function test_audit_page_is_given_correct_date_when_audit_interval_is_set()
    {
        $this->settings->setAuditInterval(5);

        $this->actingAs(User::factory()->auditAssets()->create())
            ->get(route('asset.audit.create', Asset::factory()->create()))
            ->assertViewIs('hardware.audit')
            ->assertViewHas('next_audit_date', Carbon::now()->addMonths(5)->toDateString());
    }

    public function test_asset_can_be_audited()
    {
        $this->settings->setAuditInterval(2);

        [$originalLocation, $anotherLocation] = Location::factory()->count(2)->create();

        $asset = Asset::factory()->create([
            'location_id' => $originalLocation->id,
            'next_audit_date' => null,
        ]);

        $future = now()->addMonths(3)->toDateString();

        $this->actingAs(User::factory()->auditAssets()->create())
            ->post(route('asset.audit.store', $asset), [
                'location_id' => $anotherLocation->id,
                'next_audit_date' => $future,
                'note' => 'A note about the asset',
                'redirect_option' => 'index',
            ])
            ->assertRedirectToRoute('hardware.index');

        $this->assertHasTheseActionLogs($asset, ['create', 'audit']);

        $asset->refresh();
        $auditEntry = $asset->log->firstWhere('action_type', 'audit');

        $this->assertEquals($anotherLocation->id, $auditEntry?->location_id);
        $this->assertEquals('A note about the asset', $auditEntry?->note);
        $this->assertEquals($future, $asset->next_audit_date);
    }

    public function test_asset_location_can_be_updated_when_auditing()
    {
        [$originalLocation, $anotherLocation] = Location::factory()->count(2)->create();

        $asset = Asset::factory()->create([
            'location_id' => $originalLocation->id,
            'next_audit_date' => null,
        ]);

        $future = now()->addMonths(3)->toDateString();

        $this->actingAs(User::factory()->auditAssets()->create())
            ->post(route('asset.audit.store', $asset), [
                'location_id' => $anotherLocation->id,
                'update_location' => '1',
                'next_audit_date' => $future,
                'note' => 'A note about the asset',
                'redirect_option' => 'index',
            ])
            ->assertRedirectToRoute('hardware.index');

        $this->assertHasTheseActionLogs($asset, ['create', 'audit']);

        $asset->refresh();
        $this->assertEquals($anotherLocation->id, $asset->location_id);
    }

    public function test_asset_audit_post_is_redirected_to_asset_index_if_redirect_selection_is_index()
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

    public function test_asset_audit_post_is_redirected_to_asset_page_if_redirect_selection_is_asset()
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

    public function test_asset_audit_post_is_redirected_to_audit_due_page_if_redirect_selection_is_list()
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
