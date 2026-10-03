<?php

namespace Tests\Feature\Settings;

use App\Models\Labels\CustomUserLabel;
use App\Models\User;
use Tests\TestCase;

class CustomLabelCrudTest extends TestCase
{
    private function sheetPayload(array $overrides = []): array
    {
        return array_replace_recursive([
            'name' => 'My Sheet Label',
            'template' => 'DefaultLabel',
            'type' => 'sheet',
            'content' => [
                'tag_font' => 'freemono',
                'title_font' => 'freesans',
                'field_label_font' => 'freesans',
                'field_value_font' => 'freemono',
                'barcode_size' => 3.81,
                'barcode_margin' => 0.3,
            ],
            'supports' => [
                'asset_tag' => false,
                'barcode_1d' => true,
                'barcode_2d' => true,
                'fields' => 4,
                'logo' => true,
                'title' => true,
            ],
            'page' => [
                'width' => 215.9,
                'height' => 279.4,
                'margin_top' => 12.7,
                'margin_right' => 5.58,
                'margin_bottom' => 12.7,
                'margin_left' => 5.58,
            ],
            'grid' => [
                'columns' => 3,
                'rows' => 9,
                'column_spacing' => 1.27,
                'row_spacing' => 1.778,
            ],
            'label' => [
                'width' => 66.675,
                'height' => 25.4,
                'border' => 0,
                'padding_top' => 0,
                'padding_right' => 0,
                'padding_bottom' => 0,
                'padding_left' => 0,
            ],
        ], $overrides);
    }

    private function tapePayload(array $overrides = []): array
    {
        return array_replace_recursive([
            'name' => 'My Tape Label',
            'template' => 'StandardTape',
            'type' => 'tape',
            'content' => [
                'tag_font' => 'freemono',
                'title_font' => 'freesans',
                'field_label_font' => 'freesans',
                'field_value_font' => 'freemono',
                'barcode_size' => 6,
                'barcode_margin' => 1,
            ],
            'supports' => [
                'asset_tag' => true,
                'barcode_1d' => true,
                'barcode_2d' => true,
                'fields' => 5,
                'logo' => true,
                'title' => true,
            ],
            'dimensions' => [
                'width' => 50,
                'height' => 24,
                'label_gap' => 3,
            ],
        ], $overrides);
    }

    private function makeCustomTapeLabel(array $attributes = []): CustomUserLabel
    {
        return CustomUserLabel::create(array_replace([
            'name' => 'Existing Tape Label',
            'base_label' => 'StandardTape',
            'type' => 'tape',
            'overrides' => [],
            'config_snapshot' => [
                'unit' => 'mm',
                'template' => 'StandardTape',
                'type' => 'tape',
                'name' => 'Existing Tape Label',
                'dimensions' => ['width' => 50.0, 'height' => 24.0, 'label_gap' => 3.0],
                'content' => $this->tapePayload()['content'],
                'supports' => $this->tapePayload()['supports'],
            ],
            'is_default' => false,
        ], $attributes));
    }

    public function test_store_creates_sheet_label_with_submitted_grid(): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.labels.store'), $this->sheetPayload([
                'grid' => ['rows' => 7, 'columns' => 2],
            ]))
            ->assertRedirect(route('settings.labels.index'))
            ->assertSessionHas('success');

        $label = CustomUserLabel::where('name', 'My Sheet Label')->firstOrFail();

        $this->assertSame('sheet', $label->type);
        $this->assertSame('DefaultLabel', $label->base_label);
        $this->assertEquals(7, $label->config_snapshot['grid']['rows']);
        $this->assertEquals(2, $label->config_snapshot['grid']['columns']);
    }

    public function test_store_creates_tape_label_with_submitted_dimensions(): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.labels.store'), $this->tapePayload([
                'dimensions' => ['width' => 62, 'height' => 29, 'label_gap' => 2],
            ]))
            ->assertRedirect(route('settings.labels.index'))
            ->assertSessionHas('success');

        $label = CustomUserLabel::where('name', 'My Tape Label')->firstOrFail();

        $this->assertSame('tape', $label->type);
        $this->assertArrayHasKey('dimensions', $label->config_snapshot);
        $this->assertEquals(62, $label->config_snapshot['dimensions']['width']);
        $this->assertEquals(29, $label->config_snapshot['dimensions']['height']);
        $this->assertEquals(2, $label->config_snapshot['dimensions']['label_gap']);
    }

    public function test_store_rejects_zero_rows_and_columns_for_sheet(): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.labels.store'), $this->sheetPayload([
                'grid' => ['rows' => 0, 'columns' => 0],
            ]))
            ->assertSessionHasErrors(['grid.rows', 'grid.columns']);

        $this->assertDatabaseCount('custom_user_labels', 0);
    }

    public function test_store_rejects_non_positive_tape_dimensions(): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.labels.store'), $this->tapePayload([
                'dimensions' => ['width' => 0, 'height' => -5],
            ]))
            ->assertSessionHasErrors(['dimensions.width', 'dimensions.height']);

        $this->assertDatabaseCount('custom_user_labels', 0);
    }

    public function test_update_persists_new_sheet_grid(): void
    {
        $label = CustomUserLabel::create([
            'name' => 'Existing Sheet Label',
            'base_label' => 'DefaultLabel',
            'type' => 'sheet',
            'overrides' => [],
            'config_snapshot' => $this->sheetPayload(),
            'is_default' => false,
        ]);

        $this->actingAs(User::factory()->superuser()->create())
            ->put(route('settings.labels.update', $label), $this->sheetPayload([
                'name' => 'Existing Sheet Label',
                'grid' => ['rows' => 5, 'columns' => 4],
            ]))
            ->assertRedirect(route('settings.labels.index'))
            ->assertSessionHas('success');

        $label->refresh();

        $this->assertEquals(5, $label->config_snapshot['grid']['rows']);
        $this->assertEquals(4, $label->config_snapshot['grid']['columns']);
    }
    
    public function test_update_persists_new_tape_dimensions(): void
    {
        $label = $this->makeCustomTapeLabel();

        $this->actingAs(User::factory()->superuser()->create())
            ->put(route('settings.labels.update', $label), $this->tapePayload([
                'name' => 'Existing Tape Label',
                'dimensions' => ['width' => 80, 'height' => 30, 'label_gap' => 5],
            ]))
            ->assertRedirect(route('settings.labels.index'))
            ->assertSessionHas('success');

        $label->refresh();

        $this->assertArrayHasKey('dimensions', $label->config_snapshot);
        $this->assertEquals(80, $label->config_snapshot['dimensions']['width']);
        $this->assertEquals(30, $label->config_snapshot['dimensions']['height']);
        $this->assertEquals(5, $label->config_snapshot['dimensions']['label_gap']);
    }

    public function test_update_rejects_non_positive_tape_dimensions(): void
    {
        $label = $this->makeCustomTapeLabel();

        $this->actingAs(User::factory()->superuser()->create())
            ->put(route('settings.labels.update', $label), $this->tapePayload([
                'dimensions' => ['width' => 0, 'height' => 0],
            ]))
            ->assertSessionHasErrors(['dimensions.width', 'dimensions.height']);
    }

    // ---------------------------------------------------------------
    // destroy() guard
    // ---------------------------------------------------------------

    public function test_destroy_deletes_non_default_label(): void
    {
        $label = $this->makeCustomTapeLabel(['is_default' => false]);

        $this->actingAs(User::factory()->superuser()->create())
            ->delete(route('settings.labels.destroy', $label))
            ->assertRedirect(route('settings.labels.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted($label);
    }

    public function test_destroy_blocks_deleting_the_default_label(): void
    {
        $label = $this->makeCustomTapeLabel(['is_default' => true]);

        $this->actingAs(User::factory()->superuser()->create())
            ->delete(route('settings.labels.destroy', $label))
            ->assertRedirect(route('settings.labels.index'))
            ->assertSessionHas('warning');

        $this->assertNotSoftDeleted($label);
    }
}