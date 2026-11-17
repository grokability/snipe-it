<?php

namespace Tests\Feature\SyncAdapters;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\AssetExternalSource;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\Fleet\FleetAdapter;
use Tests\TestCase;

class ArchiveMissingLinksTest extends TestCase
{
    public function test_disabled_feature_is_a_no_op(): void
    {
        $instance = $this->makeInstance();
        $status = $this->archiveStatus();
        $asset = $this->linkedAsset($instance, 'vendor-1');

        // Feature not enabled. Even with a status configured and a
        // missing record, the reconcile pass does nothing.
        SyncAdapterConfig::put($instance->id, 'archive_missing_status_id', (string) $status->id);
        SyncAdapterConfig::put($instance->id, 'archive_missing_threshold', '1');

        $adapter = new FleetAdapter($instance->fresh());
        $result = $adapter->reconcileMissingLinks([]);

        $this->assertSame(['reconciled' => 0, 'archived' => 0, 'flagged_for_review' => 0, 'skipped_confirm' => 0, 'skipped_checked_out' => 0], $result);
        $link = AssetExternalSource::where('asset_id', $asset->id)->first();
        $this->assertSame(0, (int) $link->consecutive_missing_pulls);
    }

    public function test_enabled_feature_with_no_status_configured_is_a_no_op(): void
    {
        $instance = $this->makeInstance();
        SyncAdapterConfig::put($instance->id, 'archive_missing_enabled', '1');
        $this->linkedAsset($instance, 'vendor-1');

        $adapter = new FleetAdapter($instance->fresh());
        $result = $adapter->reconcileMissingLinks([]);

        $this->assertSame(['reconciled' => 0, 'archived' => 0, 'flagged_for_review' => 0, 'skipped_confirm' => 0, 'skipped_checked_out' => 0], $result);
    }

    public function test_seen_record_resets_counter_and_archive_timestamp(): void
    {
        $instance = $this->makeInstance();
        $status = $this->archiveStatus();
        $this->enable($instance, $status, threshold: 2);
        $asset = $this->linkedAsset($instance, 'vendor-1');

        // Pretend a previous reconcile already bumped the counter and
        // even archived the asset. A later pull that sees it again
        // must wipe both so a new disappearance starts fresh.
        $link = $asset->externalSource;
        $link->consecutive_missing_pulls = 5;
        $link->missing_archived_at = now();
        $link->save();

        $adapter = new FleetAdapter($instance->fresh());
        $result = $adapter->reconcileMissingLinks(['vendor-1']);

        $this->assertSame(1, $result['reconciled']);
        $this->assertSame(0, $result['archived']);
        $link = $link->fresh();
        $this->assertSame(0, (int) $link->consecutive_missing_pulls);
        $this->assertNull($link->missing_archived_at);
    }

    public function test_missing_record_under_threshold_increments_only(): void
    {
        $instance = $this->makeInstance();
        $status = $this->archiveStatus();
        $this->enable($instance, $status, threshold: 3);
        $asset = $this->linkedAsset($instance, 'vendor-1');
        $originalStatusId = $asset->status_id;

        $adapter = new FleetAdapter($instance->fresh());
        $adapter->reconcileMissingLinks([]);
        $adapter->reconcileMissingLinks([]);

        $link = $asset->externalSource->fresh();
        $this->assertSame(2, (int) $link->consecutive_missing_pulls);
        $this->assertNull($link->missing_archived_at);
        $this->assertSame($originalStatusId, (int) $asset->fresh()->status_id);
    }

    public function test_missing_record_at_threshold_archives_and_logs(): void
    {
        $instance = $this->makeInstance();
        $status = $this->archiveStatus();
        $this->enable($instance, $status, threshold: 2);
        $asset = $this->linkedAsset($instance, 'vendor-1');

        $adapter = new FleetAdapter($instance->fresh());

        // First missing pull: counter -> 1, no archive.
        $first = $adapter->reconcileMissingLinks([]);
        $this->assertSame(0, $first['archived']);

        // Second missing pull: counter -> 2, threshold met, archive.
        $second = $adapter->reconcileMissingLinks([]);
        $this->assertSame(1, $second['archived']);

        $link = $asset->externalSource->fresh();
        $this->assertSame(2, (int) $link->consecutive_missing_pulls);
        $this->assertNotNull($link->missing_archived_at);
        $this->assertSame($status->id, (int) $asset->fresh()->status_id);

        // Actionlog entry carries the adapter label and the chosen
        // status name so the asset history reads sensibly.
        $log = Actionlog::query()
            ->where('item_id', $asset->id)
            ->where('item_type', Asset::class)
            ->orderByDesc('id')
            ->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString($instance->label, (string) $log->note);
        $this->assertStringContainsString($status->name, (string) $log->note);

        // No double-logging: applyArchiveStatus uses saveQuietly so
        // AssetObserver::updating does not tack on a second generic
        // `update` row for the same status change. Exactly one update
        // entry for this asset.
        $updateLogCount = Actionlog::query()
            ->where('item_id', $asset->id)
            ->where('item_type', Asset::class)
            ->where('action_type', 'update')
            ->count();
        $this->assertSame(1, $updateLogCount);
    }

    public function test_already_archived_link_is_not_archived_twice(): void
    {
        $instance = $this->makeInstance();
        $status = $this->archiveStatus();
        $this->enable($instance, $status, threshold: 1);
        $asset = $this->linkedAsset($instance, 'vendor-1');

        $adapter = new FleetAdapter($instance->fresh());
        $adapter->reconcileMissingLinks([]); // threshold hit, archived.
        $logsAfterFirst = Actionlog::query()->where('item_id', $asset->id)->count();

        $adapter->reconcileMissingLinks([]);
        $adapter->reconcileMissingLinks([]);
        $logsAfterRepeats = Actionlog::query()->where('item_id', $asset->id)->count();

        // Counter keeps climbing for observability, but no new log
        // entries and no further status changes.
        $this->assertSame($logsAfterFirst, $logsAfterRepeats);
        $link = $asset->externalSource->fresh();
        $this->assertSame(3, (int) $link->consecutive_missing_pulls);
    }

    public function test_reappearance_after_archive_resets_state(): void
    {
        $instance = $this->makeInstance();
        $status = $this->archiveStatus();
        $this->enable($instance, $status, threshold: 1);
        $asset = $this->linkedAsset($instance, 'vendor-1');

        $adapter = new FleetAdapter($instance->fresh());
        $adapter->reconcileMissingLinks([]); // archived

        $adapter->reconcileMissingLinks(['vendor-1']);

        $link = $asset->externalSource->fresh();
        $this->assertSame(0, (int) $link->consecutive_missing_pulls);
        $this->assertNull($link->missing_archived_at);
    }

    public function test_confirmation_hook_can_hold_off_archive(): void
    {
        $instance = $this->makeInstance();
        $status = $this->archiveStatus();
        $this->enable($instance, $status, threshold: 1);
        $asset = $this->linkedAsset($instance, 'vendor-1');

        $originalStatusId = $asset->status_id;
        $adapter = new class($instance->fresh()) extends FleetAdapter
        {
            public function confirmRecordRemoved(string $externalId): bool
            {
                unset($externalId);

                return false;
            }
        };

        $result = $adapter->reconcileMissingLinks([]);

        $this->assertSame(1, $result['skipped_confirm']);
        $this->assertSame(0, $result['archived']);
        $link = $asset->externalSource->fresh();
        $this->assertSame(1, (int) $link->consecutive_missing_pulls);
        $this->assertNull($link->missing_archived_at);
        $this->assertSame($originalStatusId, (int) $asset->fresh()->status_id);
    }

    public function test_threshold_floors_at_one(): void
    {
        $instance = $this->makeInstance();
        SyncAdapterConfig::put($instance->id, 'archive_missing_threshold', '0');
        $adapter = new FleetAdapter($instance->fresh());

        $this->assertSame(1, $adapter->archiveMissingThreshold());
    }

    public function test_default_threshold_when_unset(): void
    {
        $instance = $this->makeInstance();
        $adapter = new FleetAdapter($instance->fresh());

        $this->assertSame(3, $adapter->archiveMissingThreshold());
    }

    public function test_validation_requires_threshold_and_status_when_enabled(): void
    {
        $instance = $this->makeInstance();
        $adapter = new FleetAdapter($instance);
        $slug = $instance->slug;

        $validator = \Illuminate\Support\Facades\Validator::make([
            $slug.'_archive_missing_enabled' => '1',
            // Threshold and status deliberately absent.
        ], $adapter->validationRules());

        $validator->passes();

        $this->assertArrayHasKey($slug.'_archive_missing_threshold', $validator->failed());
        $this->assertArrayHasKey($slug.'_archive_missing_status_id', $validator->failed());
        $this->assertArrayHasKey('RequiredIf', $validator->failed()[$slug.'_archive_missing_threshold']);
        $this->assertArrayHasKey('RequiredIf', $validator->failed()[$slug.'_archive_missing_status_id']);
    }

    public function test_validation_accepts_absent_fields_when_disabled(): void
    {
        $instance = $this->makeInstance();
        $adapter = new FleetAdapter($instance);
        $slug = $instance->slug;

        // Supply the other required-by-default keys the base rules
        // demand, then leave the auto-archive trio out entirely. The
        // feature stays off and validation does not force the admin to
        // pick a status or threshold just to save an unrelated change.
        $status = Statuslabel::factory()->create();
        $validator = \Illuminate\Support\Facades\Validator::make([
            $slug.'_default_status_id' => $status->id,
        ], $adapter->validationRules());

        // The specific auto-archive fields must NOT be among the
        // failures. Other base-required fields may still fail, we only
        // care that the three new ones do not.
        $failed = array_keys($validator->failed());
        $this->assertNotContains($slug.'_archive_missing_enabled', $failed);
        $this->assertNotContains($slug.'_archive_missing_threshold', $failed);
        $this->assertNotContains($slug.'_archive_missing_status_id', $failed);
    }

    public function test_validation_rejects_deployable_status_for_archive(): void
    {
        $instance = $this->makeInstance();
        $adapter = new FleetAdapter($instance);
        $slug = $instance->slug;

        $deployable = Statuslabel::factory()->rtd()->create();

        $validator = \Illuminate\Support\Facades\Validator::make([
            $slug.'_archive_missing_enabled' => '1',
            $slug.'_archive_missing_threshold' => '3',
            $slug.'_archive_missing_status_id' => $deployable->id,
        ], $adapter->validationRules());

        $validator->passes();

        $this->assertArrayHasKey($slug.'_archive_missing_status_id', $validator->failed());
        $this->assertArrayHasKey('Exists', $validator->failed()[$slug.'_archive_missing_status_id']);
    }

    public function test_record_reports_removed_hook_defaults_false(): void
    {
        $instance = $this->makeInstance();
        $adapter = new FleetAdapter($instance);
        $record = $this->buildRecord($instance, 'vendor-1');

        $this->assertFalse($adapter->recordReportsRemoved($record));
    }

    public function test_kandji_record_reports_removed_when_is_missing_is_true(): void
    {
        $instance = SyncAdapterInstance::where('slug', 'kandji')->firstOrFail();
        $adapter = new \App\SyncAdapters\Kandji\KandjiAdapter($instance);

        $gone = $this->buildRecord($instance, 'vendor-1', ['kandji_is_missing' => true]);
        $present = $this->buildRecord($instance, 'vendor-2', ['kandji_is_missing' => false]);
        $absentFlag = $this->buildRecord($instance, 'vendor-3');

        $this->assertTrue($adapter->recordReportsRemoved($gone));
        $this->assertFalse($adapter->recordReportsRemoved($present));
        $this->assertFalse($adapter->recordReportsRemoved($absentFlag));
    }

    public function test_abm_record_reports_removed_when_released_from_org_set(): void
    {
        $instance = SyncAdapterInstance::where('slug', 'abm')->firstOrFail();
        $adapter = new \App\SyncAdapters\AppleBusinessManager\AppleBusinessManagerAdapter($instance);

        $released = $this->buildRecord($instance, 'vendor-1', ['abm_released_from_org' => '2026-09-28']);
        $active = $this->buildRecord($instance, 'vendor-2', ['abm_released_from_org' => null]);
        $absent = $this->buildRecord($instance, 'vendor-3');

        $this->assertTrue($adapter->recordReportsRemoved($released));
        $this->assertFalse($adapter->recordReportsRemoved($active));
        $this->assertFalse($adapter->recordReportsRemoved($absent));
    }

    public function test_checked_out_asset_without_review_status_is_skipped_and_warned(): void
    {
        $instance = $this->makeInstance();
        $status = $this->archiveStatus();
        $this->enable($instance, $status, threshold: 1);
        $asset = $this->linkedAsset($instance, 'vendor-1');

        // Checkout the asset to a user. Review status NOT configured
        // for this instance. Expect skip-and-warn.
        $user = \App\Models\User::factory()->create();
        $asset->assigned_to = $user->id;
        $asset->assigned_type = \App\Models\User::class;
        $asset->save();
        $originalStatusId = (int) $asset->fresh()->status_id;

        $adapter = new FleetAdapter($instance->fresh());
        $result = $adapter->reconcileMissingLinks([]);

        $asset = $asset->fresh();
        $this->assertSame($user->id, (int) $asset->assigned_to);
        $this->assertSame($originalStatusId, (int) $asset->status_id);
        $this->assertSame(1, $result['skipped_checked_out']);
        $this->assertSame(0, $result['archived']);
        $this->assertSame(0, $result['flagged_for_review']);
    }

    public function test_checked_out_asset_with_review_status_is_flagged_for_review(): void
    {
        $instance = $this->makeInstance();
        $status = $this->archiveStatus();
        $reviewStatus = Statuslabel::factory()->pending()->create([
            'name' => 'Pending Review Test '.uniqid('', true),
        ]);
        $this->enable($instance, $status, threshold: 1);
        SyncAdapterConfig::put($instance->id, 'archive_missing_review_status_id', (string) $reviewStatus->id);

        $asset = $this->linkedAsset($instance, 'vendor-1');
        $user = \App\Models\User::factory()->create();
        $asset->assigned_to = $user->id;
        $asset->assigned_type = \App\Models\User::class;
        $asset->save();

        $adapter = new FleetAdapter($instance->fresh());
        $result = $adapter->reconcileMissingLinks([]);

        $asset = $asset->fresh();
        // Checkout stays intact so an admin can see the Pending Review
        // asset in context next to its current holder. Status flipped
        // to the review label.
        $this->assertSame($user->id, (int) $asset->assigned_to);
        $this->assertSame($reviewStatus->id, (int) $asset->status_id);

        $this->assertSame(1, $result['flagged_for_review']);
        $this->assertSame(0, $result['archived']);
        $this->assertSame(0, $result['skipped_checked_out']);

        $link = AssetExternalSource::where('asset_id', $asset->id)->first();
        $this->assertNotNull($link->missing_archived_at);
    }

    public function test_checked_out_to_non_user_target_is_still_covered(): void
    {
        // assigned_to is polymorphic (user, asset, location). The
        // reconciler's checked-out gate must treat all three the same,
        // so a device assigned to a location also gets the review /
        // skip-and-warn treatment.
        $instance = $this->makeInstance();
        $status = $this->archiveStatus();
        $this->enable($instance, $status, threshold: 1);

        $asset = $this->linkedAsset($instance, 'vendor-1');
        $location = \App\Models\Location::factory()->create();
        $asset->assigned_to = $location->id;
        $asset->assigned_type = \App\Models\Location::class;
        $asset->save();

        $adapter = new FleetAdapter($instance->fresh());
        $result = $adapter->reconcileMissingLinks([]);

        $this->assertSame(1, $result['skipped_checked_out']);
        $this->assertSame(0, $result['archived']);
        // Checkout still intact.
        $asset = $asset->fresh();
        $this->assertSame($location->id, (int) $asset->assigned_to);
        $this->assertSame(\App\Models\Location::class, $asset->assigned_type);
    }

    public function test_validation_rejects_deployable_review_status(): void
    {
        $instance = $this->makeInstance();
        $adapter = new FleetAdapter($instance);
        $slug = $instance->slug;

        $deployable = Statuslabel::factory()->rtd()->create();

        $validator = \Illuminate\Support\Facades\Validator::make([
            $slug.'_archive_missing_review_status_id' => $deployable->id,
        ], $adapter->validationRules());

        $validator->passes();

        $this->assertArrayHasKey($slug.'_archive_missing_review_status_id', $validator->failed());
        $this->assertArrayHasKey('Exists', $validator->failed()[$slug.'_archive_missing_review_status_id']);
    }

    private function makeInstance(): SyncAdapterInstance
    {
        // Slug deliberately uses only alphanumerics + hyphens: Laravel's
        // required_if and dot-notation parser treat `.` in a field name
        // as nesting, and SyncAdapterInstance::generateUniqueSlug uses
        // Str::slug in real life, so test slugs should too.
        $suffix = strtolower(bin2hex(random_bytes(6)));

        return SyncAdapterInstance::create([
            'adapter_type' => 'fleet',
            'label' => 'Fleet Test '.$suffix,
            'active' => true,
            'slug' => 'fleet-test-'.$suffix,
        ]);
    }

    private function archiveStatus(): Statuslabel
    {
        return Statuslabel::factory()->archived()->create([
            'name' => 'Archived Test '.uniqid('', true),
        ]);
    }

    private function enable(SyncAdapterInstance $instance, Statuslabel $status, int $threshold): void
    {
        SyncAdapterConfig::put($instance->id, 'archive_missing_enabled', '1');
        SyncAdapterConfig::put($instance->id, 'archive_missing_status_id', (string) $status->id);
        SyncAdapterConfig::put($instance->id, 'archive_missing_threshold', (string) $threshold);
    }

    private function linkedAsset(SyncAdapterInstance $instance, string $externalId): Asset
    {
        $asset = Asset::factory()->create();
        AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => $instance->slug,
            'external_id' => $externalId,
        ]);

        return $asset;
    }

    private function buildRecord(SyncAdapterInstance $instance, string $externalId, array $extra = []): \App\SyncAdapters\HostInventoryRecord
    {
        return new \App\SyncAdapters\HostInventoryRecord(
            sourceKey: $instance->slug,
            sourceId: $externalId,
            hardwareSerial: 'SERIAL-'.$externalId,
            extra: $extra,
        );
    }
}
