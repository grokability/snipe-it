<?php

namespace Tests\Feature\SyncAdapters;

use App\SyncAdapters\Addigy\AddigyAdapter;
use App\SyncAdapters\Jamf\JamfAdapter;
use App\SyncAdapters\Osctrl\OsctrlAdapter;
use App\SyncAdapters\PushableSyncAdapter;
use App\SyncAdapters\SyncAdapter;
use Tests\TestCase;

/**
 * Pins the direction key SyncAdapter::typeCatalog() derives from each
 * adapter class so the catalog UI in the help pane reflects reality
 * without a hand-maintained list that would drift away from the actual
 * code every time an adapter gained push support.
 */
class TypeCatalogDirectionTest extends TestCase
{
    public function test_catalog_entry_shape_includes_direction_key(): void
    {
        $catalog = SyncAdapter::typeCatalog();

        $this->assertNotEmpty($catalog);
        foreach ($catalog as $slug => $entry) {
            $this->assertArrayHasKey('label', $entry, "Missing label on {$slug}.");
            $this->assertArrayHasKey('docs_url', $entry, "Missing docs_url on {$slug}.");
            $this->assertArrayHasKey('direction', $entry, "Missing direction on {$slug}.");
            $this->assertContains($entry['direction'], ['pull', 'both'], "Unexpected direction value on {$slug}: {$entry['direction']}");
        }
    }

    public function test_direction_matches_pushable_sync_adapter_subclassing(): void
    {
        $catalog = SyncAdapter::typeCatalog();
        $types = SyncAdapter::allTypes();

        foreach ($catalog as $slug => $entry) {
            $class = $types[$slug];
            $expected = is_subclass_of($class, PushableSyncAdapter::class) ? 'both' : 'pull';
            $this->assertSame(
                $expected,
                $entry['direction'],
                "{$slug} direction derivation drifted from PushableSyncAdapter subclassing.",
            );
        }
    }

    public function test_pull_only_adapter_reports_pull(): void
    {
        // osctrl is pull-only. Guards against a regression where every
        // adapter would start reporting 'both' due to a derivation bug.
        $this->assertFalse(is_subclass_of(OsctrlAdapter::class, PushableSyncAdapter::class));
        $this->assertSame('pull', SyncAdapter::typeCatalog()['osctrl']['direction']);
    }

    public function test_push_capable_adapter_reports_both(): void
    {
        // Jamf extends PushableSyncAdapter. Guards against a regression
        // where push-capable adapters would silently be downgraded to
        // pull-only in the catalog UI.
        $this->assertTrue(is_subclass_of(JamfAdapter::class, PushableSyncAdapter::class));
        $this->assertSame('both', SyncAdapter::typeCatalog()['jamf']['direction']);
    }

    public function test_addigy_direction_reflects_current_capability(): void
    {
        // Addigy's direction tracks whatever PushableSyncAdapter state
        // the class is in. This test proves the catalog follows the
        // class without an explicit list in the catalog method.
        $expected = is_subclass_of(AddigyAdapter::class, PushableSyncAdapter::class) ? 'both' : 'pull';
        $this->assertSame($expected, SyncAdapter::typeCatalog()['addigy']['direction']);
    }
}
