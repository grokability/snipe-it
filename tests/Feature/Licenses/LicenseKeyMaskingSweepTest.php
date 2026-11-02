<?php

namespace Tests\Feature\Licenses;

use App\Models\Asset;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Tests\TestCase;

/**
 * Covers every surface that displays a license product key, asserting
 * the mask lands for a viewer without licenses.keys. The accessor
 * on License::serial is the backstop. These tests pin the behavior
 * at each sink so a future refactor of a sink (new transformer, new
 * blade partial, new CSV writer) cannot silently drop the mask.
 */
class LicenseKeyMaskingSweepTest extends TestCase
{
    public function test_api_show_masks_product_key_for_viewer_without_viewkeys(): void
    {
        $license = License::factory()->create(['serial' => 'SHOW-API-SECRET-111']);

        $this->actingAsForApi(User::factory()->viewLicenses()->create())
            ->getJson(route('api.licenses.show', ['license_id' => $license->id]))
            ->assertOk()
            ->assertJsonFragment(['product_key' => License::PRODUCT_KEY_MASK])
            ->assertJsonMissing(['product_key' => 'SHOW-API-SECRET-111']);
    }

    public function test_api_user_licenselist_masks_serial_for_viewer_without_viewkeys(): void
    {
        $license = License::factory()->create(['serial' => 'USERLIST-SECRET-222']);
        $recipient = User::factory()->create();
        LicenseSeat::factory()->create([
            'license_id' => $license->id,
            'assigned_to' => $recipient->id,
        ]);

        $body = $this->actingAsForApi(User::factory()->viewLicenses()->viewUsers()->create())
            ->getJson(route('api.users.licenselist', ['user' => $recipient->id]))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('USERLIST-SECRET-222', $body);
    }

    public function test_api_asset_licenselist_masks_product_key_for_viewer_without_viewkeys(): void
    {
        $license = License::factory()->create(['serial' => 'ASSETLIST-SECRET-333']);
        $asset = Asset::factory()->create();
        LicenseSeat::factory()->create([
            'license_id' => $license->id,
            'assigned_to' => $asset->id,
            'asset_id' => $asset->id,
        ]);

        $body = $this->actingAsForApi(User::factory()->viewAssets()->viewLicenses()->create())
            ->getJson(route('api.assets.licenselist', ['asset' => $asset->id]))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('ASSETLIST-SECRET-333', $body);
    }

    public function test_license_checkout_form_masks_serial_for_viewer_without_viewkeys(): void
    {
        $license = License::factory()->create(['serial' => 'CHECKOUT-FORM-SECRET-444']);
        LicenseSeat::factory()->create(['license_id' => $license->id]);

        $this->actingAs(User::factory()->viewLicenses()->checkoutLicenses()->create())
            ->get(route('licenses.checkout', ['license' => $license->id]))
            ->assertOk()
            ->assertDontSee('CHECKOUT-FORM-SECRET-444');
    }

    public function test_license_checkin_form_masks_serial_for_viewer_without_viewkeys(): void
    {
        $license = License::factory()->create(['serial' => 'CHECKIN-FORM-SECRET-555']);
        $seat = LicenseSeat::factory()->create([
            'license_id' => $license->id,
            'assigned_to' => User::factory()->create()->id,
        ]);

        $this->actingAs(User::factory()->viewLicenses()->checkinLicenses()->create())
            ->get(route('licenses.checkin', ['licenseSeat' => $seat->id]))
            ->assertOk()
            ->assertDontSee('CHECKIN-FORM-SECRET-555');
    }

    public function test_user_print_page_masks_license_serial_for_viewer_without_viewkeys(): void
    {
        $license = License::factory()->create(['serial' => 'USER-PRINT-SECRET-666']);
        $recipient = User::factory()->create();
        LicenseSeat::factory()->create([
            'license_id' => $license->id,
            'assigned_to' => $recipient->id,
        ]);

        $this->actingAs(User::factory()->viewUsers()->viewLicenses()->create())
            ->get(route('users.print', ['userId' => $recipient->id]))
            ->assertOk()
            ->assertDontSee('USER-PRINT-SECRET-666');
    }

    public function test_licenses_csv_download_masks_serial_for_viewer_without_viewkeys(): void
    {
        $license = License::factory()->create([
            'name' => 'CsvDownloadLic-'.now()->timestamp,
            'serial' => 'LICENSES-CSV-SECRET-777',
        ]);

        $response = $this->actingAs(User::factory()->viewLicenses()->create())
            ->get(route('licenses.export'))
            ->assertOk();

        $body = $response->streamedContent();
        $this->assertStringContainsString($license->name, $body);
        $this->assertStringNotContainsString('LICENSES-CSV-SECRET-777', $body);
        $this->assertStringContainsString(License::PRODUCT_KEY_MASK, $body);
    }

    public function test_reports_export_licenses_csv_masks_serial_for_viewer_without_viewkeys(): void
    {
        $license = License::factory()->create([
            'name' => 'ReportLic-'.now()->timestamp,
            'serial' => 'REPORTS-LICENSES-SECRET-888',
        ]);

        $response = $this->actingAs(User::factory()->canViewReports()->viewLicenses()->create())
            ->get(url('reports/export/licenses'))
            ->assertOk();

        $body = $response->streamedContent();
        $this->assertStringNotContainsString('REPORTS-LICENSES-SECRET-888', $body);
    }
}
