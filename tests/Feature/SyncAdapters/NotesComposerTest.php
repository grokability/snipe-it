<?php

namespace Tests\Feature\SyncAdapters;

use App\Models\Asset;
use App\Models\CustomField;
use App\Models\Statuslabel;
use App\SyncAdapters\Support\NotesComposer;
use Tests\TestCase;

/**
 * Coverage for NotesComposer, the helper that renders composed
 * notes blobs from Snipe-IT assets using placeholder templates.
 * Adapters use this for push-to-notes-field flows (Intune, Kandji,
 * Jamf, Mosyle).
 */
class NotesComposerTest extends TestCase
{
    public function test_renders_native_placeholders()
    {
        $status = Statuslabel::factory()->rtd()->create(['name' => 'Deployable']);
        $asset = Asset::factory()->create([
            'asset_tag' => 'ABC-1234',
            'name' => 'workstation-01',
            'serial' => 'FW-ABC-123',
            'status_id' => $status->id,
        ]);

        $out = NotesComposer::compose(
            $asset,
            "Tag: {asset_tag}\nName: {name}\nSerial: {serial}\nStatus: {status}",
        );

        $this->assertSame("Tag: ABC-1234\nName: workstation-01\nSerial: FW-ABC-123\nStatus: Deployable", $out);
    }

    public function test_unknown_placeholders_render_blank()
    {
        $asset = Asset::factory()->create(['asset_tag' => 'ABC-1']);

        $out = NotesComposer::compose(
            $asset,
            'Tag: {asset_tag} | Unknown: {not_a_real_field} | End',
        );

        $this->assertSame('Tag: ABC-1 | Unknown:  | End', $out);
    }

    public function test_null_native_values_render_blank_not_literal()
    {
        $asset = Asset::factory()->create(['asset_tag' => 'X', 'notes' => null]);

        $out = NotesComposer::compose($asset, 'Notes: {notes}!');

        // Null asset->notes renders as empty string, not the literal {notes}.
        $this->assertSame('Notes: !', $out);
    }

    public function test_custom_field_placeholder_resolves_by_name()
    {
        $customField = CustomField::factory()->create([
            'element' => 'text',
            'name' => 'Cost Center',
        ]);
        $asset = Asset::factory()->create(['asset_tag' => 'CC-1']);
        // Custom fields are stored on the asset via db_column.
        $asset->setAttribute($customField->db_column, 'FINANCE-42');
        $asset->save();

        $out = NotesComposer::compose($asset, 'CC: {custom.Cost Center}');

        $this->assertSame('CC: FINANCE-42', $out);
    }

    public function test_custom_field_with_unknown_name_renders_blank()
    {
        $asset = Asset::factory()->create(['asset_tag' => 'X']);

        $out = NotesComposer::compose($asset, 'Missing: {custom.Nonexistent Field}!');

        $this->assertSame('Missing: !', $out);
    }

    public function test_blank_template_returns_empty_string()
    {
        $asset = Asset::factory()->create();

        $this->assertSame('', NotesComposer::compose($asset, ''));
        $this->assertSame('', NotesComposer::compose($asset, "  \n\t"));
    }

    public function test_composed_intune_style_template_matches_brady_script_shape()
    {
        // Sanity check that a template shaped like Brady Widener's
        // community PowerShell (asset tag + status + checkout info)
        // composes cleanly. That's the canonical Intune push use case.
        $status = Statuslabel::factory()->rtd()->create(['name' => 'Deployable']);
        $asset = Asset::factory()->create([
            'asset_tag' => 'IT-42',
            'status_id' => $status->id,
            'last_checkout' => '2026-01-15 10:00:00',
        ]);

        $template = <<<'TPL'
        [INFO FROM SNIPE-IT]
        Asset Tag: {asset_tag}
        Status: {status}
        Assigned to: {assigned_to}
        Last checkout: {last_checkout}
        TPL;

        $out = NotesComposer::compose($asset, $template);

        $this->assertStringContainsString('Asset Tag: IT-42', $out);
        $this->assertStringContainsString('Status: Deployable', $out);
        $this->assertStringContainsString('Last checkout: 2026-01-15', $out);
    }
}
