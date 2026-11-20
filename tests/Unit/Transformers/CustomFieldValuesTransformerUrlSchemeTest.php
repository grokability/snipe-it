<?php

namespace Tests\Unit\Transformers;

use App\Http\Transformers\CustomFieldValuesTransformer;
use App\Models\AssetModel;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * GHSA-65hf-5j7r-2xvv: ensure the sink-side URL filter in
 * CustomFieldValuesTransformer neutralizes javascript: / data: /
 * vbscript: schemes stored while a URL-format custom field held an
 * earlier format (e.g. ANY) or arrived through a different write
 * path (importer, bulk edit, direct DB).
 */
#[Group('security')]
class CustomFieldValuesTransformerUrlSchemeTest extends TestCase
{
    private function assetWithUrlField(string $storedValue): \App\Models\Asset
    {
        $fieldset = CustomFieldset::factory()->create(['name' => 'xss-test-fieldset']);
        $field = CustomField::factory()->create([
            'name' => 'Notes URL',
            'format' => 'URL',
            'element' => 'text',
            'display_in_user_view' => 1,
        ]);
        $fieldset->fields()->attach($field->id, [
            'required' => 0,
            'order' => 1,
        ]);

        $model = AssetModel::factory()->create(['fieldset_id' => $fieldset->id]);
        $asset = \App\Models\Asset::factory()->create(['model_id' => $model->id]);
        $asset->setAttribute($field->db_column, $storedValue);
        $asset->save();

        return $asset->fresh();
    }

    public function test_javascript_scheme_is_stripped_on_output(): void
    {
        $asset = $this->assetWithUrlField('javascript:alert(document.domain)');

        $out = (new CustomFieldValuesTransformer)->transform($asset);
        $this->assertSame('', $out['Notes URL']['value']);
    }

    public function test_data_scheme_is_stripped_on_output(): void
    {
        $asset = $this->assetWithUrlField('data:text/html,<script>alert(1)</script>');

        $out = (new CustomFieldValuesTransformer)->transform($asset);
        $this->assertSame('', $out['Notes URL']['value']);
    }

    public function test_http_url_passes_through(): void
    {
        $asset = $this->assetWithUrlField('https://docs.example.com/x');

        $out = (new CustomFieldValuesTransformer)->transform($asset);
        $this->assertSame('https://docs.example.com/x', $out['Notes URL']['value']);
    }

    public function test_custom_app_scheme_passes_through(): void
    {
        // The whole point of using a deny-list: niche enterprise
        // protocols keep working.
        $asset = $this->assetWithUrlField('vnc://corp.example.com');

        $out = (new CustomFieldValuesTransformer)->transform($asset);
        $this->assertSame('vnc://corp.example.com', $out['Notes URL']['value']);
    }

    public function test_tabbed_javascript_scheme_is_stripped(): void
    {
        // Browser normalizes control chars out of the scheme
        // before parsing, so the helper must too.
        $asset = $this->assetWithUrlField("java\tscript:alert(1)");

        $out = (new CustomFieldValuesTransformer)->transform($asset);
        $this->assertSame('', $out['Notes URL']['value']);
    }
}
