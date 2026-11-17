<?php

namespace Tests\Feature\SyncAdapters;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\CustomField;
use App\Models\Manufacturer;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\Jamf\JamfAdapter;
use Illuminate\Support\Facades\Crypt;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Coverage for SyncAdapter::composeNotesForPush(), the folded-onto-
 * base implementation of the composed-notes push template. Previously
 * lived as its own NotesComposer helper class. Test shape mirrors
 * the old NotesComposerTest but hits the base class directly via a
 * concrete adapter, and asserts the new tuple return shape
 * (target + value) instead of a bare string.
 */
class ComposedNotesForPushTest extends TestCase
{
    #[Test]
    public function returns_null_when_no_template_configured(): void
    {
        $adapter = $this->configuredJamfAdapter();
        $asset = $this->makeAsset(['asset_tag' => 'ABC-123']);

        $this->assertNull($adapter->composeNotesForPush($asset));
    }

    #[Test]
    public function returns_null_when_template_is_whitespace_only(): void
    {
        $adapter = $this->configuredJamfAdapter();
        SyncAdapterConfig::put($adapter->getInstanceId(), 'push_notes_template', "  \n\t");
        $asset = $this->makeAsset(['asset_tag' => 'ABC-123']);

        $this->assertNull($adapter->composeNotesForPush($asset));
    }

    #[Test]
    public function returns_null_when_no_target_can_be_resolved(): void
    {
        $adapter = $this->configuredJamfAdapter();
        SyncAdapterConfig::put($adapter->getInstanceId(), 'push_notes_template', 'Tag: {asset_tag}');
        $asset = $this->makeAsset(['asset_tag' => 'ABC-123']);

        $this->assertNull($adapter->composeNotesForPush($asset));
    }

    #[Test]
    public function substitutes_native_placeholders_from_asset_columns(): void
    {
        $adapter = $this->configuredJamfAdapterWithNotesTarget();
        SyncAdapterConfig::put(
            $adapter->getInstanceId(),
            'push_notes_template',
            "Tag: {asset_tag}\nName: {name}\nSerial: {serial}\nStatus: {status}",
        );
        $status = Statuslabel::factory()->rtd()->create(['name' => 'Deployable']);
        $asset = $this->makeAsset([
            'asset_tag' => 'ABC-1234',
            'name' => 'workstation-01',
            'serial' => 'FW-ABC-123',
            'status_id' => $status->id,
        ]);

        $result = $adapter->composeNotesForPush($asset);

        $this->assertNotNull($result);
        $this->assertSame('notes', $result['target']);
        $this->assertSame(
            "Tag: ABC-1234\nName: workstation-01\nSerial: FW-ABC-123\nStatus: Deployable",
            $result['value'],
        );
    }

    #[Test]
    public function admin_target_override_wins_over_adapter_default(): void
    {
        $adapter = $this->configuredJamfAdapter();
        SyncAdapterConfig::put($adapter->getInstanceId(), 'push_notes_template', 'Tag: {asset_tag}');
        SyncAdapterConfig::put($adapter->getInstanceId(), 'push_notes_target', 'custom.notes_field');
        $asset = $this->makeAsset(['asset_tag' => 'ABC-123']);

        $result = $adapter->composeNotesForPush($asset);

        $this->assertNotNull($result);
        $this->assertSame('custom.notes_field', $result['target']);
        $this->assertSame('Tag: ABC-123', $result['value']);
    }

    #[Test]
    public function unknown_placeholders_render_blank(): void
    {
        $adapter = $this->configuredJamfAdapterWithNotesTarget();
        SyncAdapterConfig::put(
            $adapter->getInstanceId(),
            'push_notes_template',
            'Tag: {asset_tag} | Unknown: {not_a_real_field} | End',
        );
        $asset = $this->makeAsset(['asset_tag' => 'ABC-1']);

        $result = $adapter->composeNotesForPush($asset);

        $this->assertNotNull($result);
        $this->assertSame('Tag: ABC-1 | Unknown:  | End', $result['value']);
    }

    #[Test]
    public function null_native_values_render_blank_not_literal(): void
    {
        $adapter = $this->configuredJamfAdapterWithNotesTarget();
        SyncAdapterConfig::put(
            $adapter->getInstanceId(),
            'push_notes_template',
            'Notes: {notes}!',
        );
        $asset = $this->makeAsset(['asset_tag' => 'X', 'notes' => null]);

        $result = $adapter->composeNotesForPush($asset);

        $this->assertNotNull($result);
        // Null asset->notes renders as empty string, not the literal {notes}.
        $this->assertSame('Notes: !', $result['value']);
    }

    #[Test]
    public function custom_field_placeholder_resolves_by_display_name(): void
    {
        $adapter = $this->configuredJamfAdapterWithNotesTarget();

        $field = CustomField::factory()->create([
            'element' => 'text',
            'name' => 'Cost Center',
        ]);
        $asset = $this->makeAsset(['asset_tag' => 'CC-1']);
        $asset->setAttribute($field->db_column, 'FINANCE-42');
        $asset->save();

        SyncAdapterConfig::put(
            $adapter->getInstanceId(),
            'push_notes_template',
            'CC: {custom.Cost Center}',
        );

        $result = $adapter->composeNotesForPush($asset);

        $this->assertNotNull($result);
        $this->assertSame('CC: FINANCE-42', $result['value']);
    }

    #[Test]
    public function custom_field_with_unknown_name_renders_blank(): void
    {
        $adapter = $this->configuredJamfAdapterWithNotesTarget();
        SyncAdapterConfig::put(
            $adapter->getInstanceId(),
            'push_notes_template',
            'Missing: {custom.Nonexistent Field}!',
        );
        $asset = $this->makeAsset(['asset_tag' => 'X']);

        $result = $adapter->composeNotesForPush($asset);

        $this->assertNotNull($result);
        $this->assertSame('Missing: !', $result['value']);
    }

    // ------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeAsset(array $overrides = []): Asset
    {
        $status = Statuslabel::factory()->rtd()->create();
        $manufacturer = Manufacturer::factory()->create();
        $category = Category::factory()->assetLaptopCategory()->create();
        $model = AssetModel::factory()->create([
            'category_id' => $category->id,
            'manufacturer_id' => $manufacturer->id,
        ]);

        return Asset::factory()->create(array_merge([
            'model_id' => $model->id,
            'status_id' => $status->id,
        ], $overrides));
    }

    private function configuredJamfAdapter(): JamfAdapter
    {
        $instance = SyncAdapterInstance::create([
            'adapter_type' => 'jamf',
            'label' => 'Test Jamf ' . uniqid(),
            'active' => true,
        ]);
        SyncAdapterConfig::put($instance->id, 'url', 'https://jamf.example.test');
        SyncAdapterConfig::put($instance->id, 'token', Crypt::encrypt('fake-token'));

        // Jamf *does* ship a notesFieldTarget default, so we need to disable it
        return new class($instance) extends JamfAdapter
        {
            public function notesFieldTarget(): ?string
            {
                return null;
            }

            public function getInstanceId(): int
            {
                return $this->instance->id;
            }
        };
    }

    /**
     * Jamf with an admin-set notes target so composeNotesForPush()
     * can resolve `target` and actually build a payload.
     */
    private function configuredJamfAdapterWithNotesTarget(): JamfAdapter
    {
        $adapter = $this->configuredJamfAdapter();
        SyncAdapterConfig::put($adapter->getInstanceId(), 'push_notes_target', 'notes');

        return $adapter;
    }
}
