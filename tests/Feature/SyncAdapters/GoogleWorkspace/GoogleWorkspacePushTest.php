<?php

namespace Tests\Feature\SyncAdapters\GoogleWorkspace;

use App\Models\Asset;
use App\Models\AssetExternalSource;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\GoogleWorkspace\GoogleWorkspaceAdapter;
use App\SyncAdapters\PushableSyncAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end coverage for Google Workspace (ChromeOS) push. Google
 * Admin exposes annotatedAssetId (asset tag) and notes (composed) as
 * the two writable fields Snipe-IT populates. Both PATCH against the
 * same Chrome device endpoint. Tests verify the PATCH lands with the
 * right shape, dry-run behaves, and the composed-notes template is
 * spliced onto the payload.
 */
class GoogleWorkspacePushTest extends TestCase
{
    private string $privateKeyPem = '';

    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();

        $key = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => 2048,
        ]);
        $pem = '';
        openssl_pkey_export($key, $pem);
        $this->privateKeyPem = $pem;
    }

    public function test_adapter_implements_pushable_interface(): void
    {
        $this->assertInstanceOf(PushableSyncAdapter::class, $this->configuredAdapter());
    }

    public function test_composed_notes_template_patches_google_chrome_device(): void
    {
        $adapter = $this->configuredAdapter(template: 'Owned by Snipe-IT: {asset_tag}');
        $asset = Asset::factory()->create(['asset_tag' => 'ACME-042']);
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => $adapter->name(),
            'external_id' => 'chrome-device-guid-42',
        ]);

        Http::fake(function ($request) {
            if (str_contains($request->url(), '/oauth2.googleapis.com/token') || str_contains($request->url(), '/token')) {
                return Http::response(['access_token' => 'stub-bearer', 'expires_in' => 3300]);
            }
            if ($request->method() === 'PATCH' && str_contains($request->url(), '/devices/chromeos/chrome-device-guid-42')) {
                return Http::response(['ok' => true]);
            }

            return Http::response(['unmatched' => true], 500);
        });

        $adapter->push($asset);

        Http::assertSent(function ($request) {
            if ($request->method() !== 'PATCH') {
                return false;
            }
            if (! str_contains($request->url(), '/devices/chromeos/chrome-device-guid-42')) {
                return false;
            }
            $notes = $request->data()['notes'] ?? '';

            return str_contains($notes, 'Owned by Snipe-IT: ACME-042');
        });
    }

    public function test_asset_tag_direction_push_writes_to_annotated_asset_id(): void
    {
        // Admin flags asset_tag with direction=push in the mapping UI.
        // buildPushPayload maps that to Google's annotatedAssetId key
        // on the Chrome device resource. configuredAdapter() is called
        // for the config-write side effect. The pushing adapter is
        // re-hydrated below so the fresh instance picks up the
        // direction key we set here.
        $this->configuredAdapter();
        $instance = SyncAdapterInstance::where('slug', 'google_workspace')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'direction.asset_tag', 'push');

        $asset = Asset::factory()->create(['asset_tag' => 'TAGGED-1']);
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => 'google_workspace',
            'external_id' => 'chrome-tag-guid-1',
        ]);

        Http::fake(function () {
            return Http::response(['access_token' => 'stub', 'expires_in' => 3300]);
        });

        $freshAdapter = new GoogleWorkspaceAdapter($instance->fresh());
        $freshAdapter->push($asset);

        Http::assertSent(function ($request) {
            if ($request->method() !== 'PATCH') {
                return false;
            }

            return ($request->data()['annotatedAssetId'] ?? null) === 'TAGGED-1';
        });
    }

    public function test_dry_run_skips_the_http_call_and_logs_payload(): void
    {
        // Same re-hydrate shape as the asset_tag test above.
        $this->configuredAdapter(template: 'Tag: {asset_tag}');
        $instance = SyncAdapterInstance::where('slug', 'google_workspace')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'push_dry_run', '1');

        $asset = Asset::factory()->create(['asset_tag' => 'DRY-1']);
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => 'google_workspace',
            'external_id' => 'chrome-dry-guid',
        ]);

        Http::fake();

        $freshAdapter = new GoogleWorkspaceAdapter($instance->fresh());
        $result = $freshAdapter->push($asset);

        $this->assertTrue($result);
        // No HTTP call goes out in dry-run mode. Everything logs
        // through the sync-adapters channel so admins can inspect the
        // payload before flipping the toggle off.
        Http::assertNothingSent();
    }

    public function test_annotated_user_push_writes_assigned_users_email(): void
    {
        // Admin flags google_workspace_annotated_user with
        // direction=push. buildPushPayload maps that extras key to
        // Google's annotatedUser field on the Chrome device resource
        // and reads the value from asset->assignedTo->email.
        $adapter = $this->configuredAdapter();
        $instance = SyncAdapterInstance::where('slug', 'google_workspace')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'direction.google_workspace_annotated_user', 'push');

        $assignee = \App\Models\User::factory()->create([
            'email' => 'carol@example.test',
            'first_name' => 'Carol',
            'last_name' => 'Danvers',
        ]);
        $asset = Asset::factory()->create();
        $asset->assigned_to = $assignee->id;
        $asset->assigned_type = \App\Models\User::class;
        $asset->save();

        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => 'google_workspace',
            'external_id' => 'chrome-user-guid-42',
        ]);

        Http::fake(function () {
            return Http::response(['access_token' => 'stub', 'expires_in' => 3300]);
        });

        $freshAdapter = new GoogleWorkspaceAdapter($instance->fresh());
        $freshAdapter->push($asset);

        Http::assertSent(function ($request) {
            if ($request->method() !== 'PATCH') {
                return false;
            }

            return ($request->data()['annotatedUser'] ?? null) === 'carol@example.test';
        });
    }

    public function test_annotated_user_push_falls_back_to_display_name_when_email_is_blank(): void
    {
        $adapter = $this->configuredAdapter();
        $instance = SyncAdapterInstance::where('slug', 'google_workspace')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'direction.google_workspace_annotated_user', 'push');

        $assignee = \App\Models\User::factory()->create([
            'email' => '',
            'first_name' => 'No',
            'last_name' => 'Email',
        ]);
        $asset = Asset::factory()->create();
        $asset->assigned_to = $assignee->id;
        $asset->assigned_type = \App\Models\User::class;
        $asset->save();

        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => 'google_workspace',
            'external_id' => 'chrome-noemail-guid',
        ]);

        Http::fake(fn () => Http::response(['access_token' => 'stub', 'expires_in' => 3300]));

        $freshAdapter = new GoogleWorkspaceAdapter($instance->fresh());
        $freshAdapter->push($asset);

        Http::assertSent(function ($request) use ($assignee) {
            if ($request->method() !== 'PATCH') {
                return false;
            }

            // display_name accessor produces "No Email" for the
            // first_name / last_name pair above.
            return ($request->data()['annotatedUser'] ?? null) === $assignee->display_name;
        });
    }

    public function test_annotated_user_push_sends_null_when_asset_is_unassigned(): void
    {
        // A checkin on the Snipe-IT side should clear Google's
        // annotatedUser so both systems reflect that nobody currently
        // owns the device. Google's PATCH semantics treat a null value
        // as a clear, so we let it through unmodified.
        $adapter = $this->configuredAdapter();
        $instance = SyncAdapterInstance::where('slug', 'google_workspace')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'direction.google_workspace_annotated_user', 'push');

        $asset = Asset::factory()->create();
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => 'google_workspace',
            'external_id' => 'chrome-unassigned-guid',
        ]);

        Http::fake(fn () => Http::response(['access_token' => 'stub', 'expires_in' => 3300]));

        $freshAdapter = new GoogleWorkspaceAdapter($instance->fresh());
        $freshAdapter->push($asset);

        Http::assertSent(function ($request) {
            if ($request->method() !== 'PATCH') {
                return false;
            }

            // Key IS present in payload (so Google clears its side)
            // and the value is null.
            return array_key_exists('annotatedUser', $request->data())
                && $request->data()['annotatedUser'] === null;
        });
    }

    public function test_annotated_user_push_skipped_when_asset_is_checked_out_to_a_location(): void
    {
        // assignedTo is polymorphic. Checkout to a Location, another
        // Asset, or anything other than a User has no email to write,
        // so the push skips the field. Key IS still present in the
        // PATCH so Google's side stays in sync with "no user owns
        // this right now".
        $adapter = $this->configuredAdapter();
        $instance = SyncAdapterInstance::where('slug', 'google_workspace')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'direction.google_workspace_annotated_user', 'push');

        $location = \App\Models\Location::factory()->create();
        $asset = Asset::factory()->create();
        $asset->assigned_to = $location->id;
        $asset->assigned_type = \App\Models\Location::class;
        $asset->save();

        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => 'google_workspace',
            'external_id' => 'chrome-loc-guid',
        ]);

        Http::fake(fn () => Http::response(['access_token' => 'stub', 'expires_in' => 3300]));

        $freshAdapter = new GoogleWorkspaceAdapter($instance->fresh());
        $freshAdapter->push($asset);

        Http::assertSent(function ($request) {
            if ($request->method() !== 'PATCH') {
                return false;
            }

            return array_key_exists('annotatedUser', $request->data())
                && $request->data()['annotatedUser'] === null;
        });
    }

    public function test_blank_template_and_no_directed_fields_skips_push(): void
    {
        $adapter = $this->configuredAdapter(template: '');
        $asset = Asset::factory()->create(['asset_tag' => 'NOTPL']);
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => 'google_workspace',
            'external_id' => 'chrome-blank-guid',
        ]);

        Http::fake();

        $result = $adapter->push($asset);

        $this->assertFalse($result);
        Http::assertNothingSent();
    }

    private function configuredAdapter(?string $template = null): GoogleWorkspaceAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'google_workspace')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'service_account_email', 'sa@example.iam.gserviceaccount.com');
        SyncAdapterConfig::put($instance->id, 'impersonate_email', 'admin@example.test');
        SyncAdapterConfig::put($instance->id, 'customer_id', 'my_customer');
        SyncAdapterConfig::put($instance->id, 'private_key', Crypt::encrypt($this->privateKeyPem));
        if ($template !== null) {
            SyncAdapterConfig::put($instance->id, 'push_notes_template', $template);
        }

        return new GoogleWorkspaceAdapter($instance->fresh());
    }
}
