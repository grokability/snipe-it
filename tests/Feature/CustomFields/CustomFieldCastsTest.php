<?php

namespace Tests\Feature\CustomFields;

use App\Casts\SnipeBoolean;
use App\Casts\SnipeCrypt;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CustomFieldCastsTest extends TestCase
{
    public function test_model_without_a_fieldset_keeps_its_normal_casts(): void
    {
        $asset = Asset::factory()->create([
            'model_id' => AssetModel::factory()->create(['fieldset_id' => null])->id,
        ]);

        $this->assertNull($asset->custom_fieldset);
        $this->assertSame('integer', $asset->getCasts()['model_id']);
        $this->assertTrue($asset->save());
    }

    public function test_custom_field_casts_preserve_legacy_encryption_and_flatten_arrays(): void
    {
        $field = CustomField::factory()->encrypt()->create();
        $asset = Asset::factory()->hasEncryptedCustomField($field)->create();
        $column = $field->db_column_name();
        $asset->{$column} = ['one', 'two'];
        $this->assertTrue($asset->save());

        $this->assertSame('one, two', $asset->fresh()->{$column});
        $this->assertSame('one, two', Crypt::decrypt($asset->getRawOriginal($column)));

        DB::table('assets')->where('id', $asset->id)->update([$column => Crypt::encrypt('legacy value')]);
        $this->assertSame('legacy value', $asset->fresh()->{$column});

        $asset = $asset->fresh();
        $original = $asset->getRawOriginal($column);
        $asset->{$column} = 'legacy value';
        $this->assertFalse($asset->isDirty($column));
        $this->assertSame($original, $asset->getAttributes()[$column]);
    }

    public function test_boolean_strings_are_normalized_before_validation_and_storage(): void
    {
        $field = CustomField::factory()->create(['format' => 'BOOLEAN', 'element' => 'text']);
        $asset = Asset::factory()->hasMultipleCustomFields([$field])->create();
        $column = $field->db_column_name();

        foreach (['false' => '0', 'true' => '1', 'off' => '0', 'on' => '1'] as $input => $expected) {
            $asset->{$column} = $input;
            $this->assertTrue($asset->save(), (string) $asset->getErrors());
            $this->assertSame($expected, (string) $asset->fresh()->getRawOriginal($column));
        }
    }

    public function test_encrypted_fields_can_be_cleared_and_omitted_fields_are_preserved(): void
    {
        $field = CustomField::factory()->encrypt()->create();
        $asset = Asset::factory()->hasEncryptedCustomField($field)->create();
        $column = $field->db_column_name();
        $asset->{$column} = 'keep this value';
        $this->assertTrue($asset->save());

        $this->actingAs(User::factory()->superuser()->create());
        $asset->custom_fill(new Request);
        $this->assertSame('keep this value', $asset->{$column});

        $asset->custom_fill(new Request([$column => null]));
        $this->assertTrue($asset->save());
        $this->assertNull($asset->fresh()->{$column});
    }

    public function test_custom_fill_preserves_encrypted_fields_when_the_actor_lacks_permission(): void
    {
        $field = CustomField::factory()->encrypt()->create();
        $asset = Asset::factory()->hasEncryptedCustomField($field)->create();
        $column = $field->db_column_name();
        $asset->{$column} = 'original secret';
        $this->assertTrue($asset->save());
        $original = $asset->getRawOriginal($column);

        $this->actingAs(User::factory()->editAssets()->create());
        $asset->custom_fill(new Request([$column => 'unauthorized replacement']));

        $this->assertTrue($asset->hasSkippedEncryptedCustomFields());
        $this->assertSame($original, $asset->getAttributes()[$column]);
        $this->assertFalse($asset->isDirty($column));
        $this->assertSame('********', $asset->{$column});
        $this->assertSame('********', $asset->toArray()[$column]);
    }

    public function test_changing_the_assignment_rebuilds_the_casts_and_fieldset(): void
    {
        $encrypted = CustomField::factory()->encrypt()->create();
        $boolean = CustomField::factory()->create(['format' => 'BOOLEAN', 'element' => 'text']);
        $first = AssetModel::factory()->hasMultipleCustomFields([$encrypted])->create();
        $second = AssetModel::factory()->hasMultipleCustomFields([$boolean])->create();
        $asset = Asset::factory()->create(['model_id' => $first->id]);

        $this->assertSame(SnipeCrypt::class, $asset->getCasts()[$encrypted->db_column_name()]);
        $this->assertSame($first->fieldset_id, $asset->custom_fieldset->id);

        $asset->model_id = $second->id;

        $this->assertArrayNotHasKey($encrypted->db_column_name(), $asset->getCasts());
        $this->assertSame(SnipeBoolean::class, $asset->getCasts()[$boolean->db_column_name()]);
        $this->assertSame($second->fieldset_id, $asset->custom_fieldset->id);

        $asset->model_id = null;
        $this->assertArrayNotHasKey($boolean->db_column_name(), $asset->getCasts());
        $this->assertNull($asset->custom_fieldset);
    }

    public function test_casts_work_when_a_new_model_receives_its_assignment_after_initialization(): void
    {
        $field = CustomField::factory()->encrypt()->create();
        $model = AssetModel::factory()->hasMultipleCustomFields([$field])->create();
        $asset = new Asset;
        $this->assertArrayNotHasKey($field->db_column_name(), $asset->getCasts());

        $asset->model()->associate($model);
        $asset->{$field->db_column_name()} = 'new value';

        $this->assertSame('new value', $asset->{$field->db_column_name()});
        $this->assertSame('new value', Crypt::decrypt($asset->getAttributes()[$field->db_column_name()]));
    }

    public function test_repeated_cast_lookups_do_not_query_and_preserve_merged_casts(): void
    {
        $field = CustomField::factory()->encrypt()->create();
        $asset = Asset::factory()->hasEncryptedCustomField($field)->create()->fresh();
        $asset->getCasts();

        DB::enableQueryLog();
        DB::flushQueryLog();
        try {
            $asset->mergeCasts(['extra_attribute' => 'boolean']);
            for ($i = 0; $i < 10; $i++) {
                $casts = $asset->getCasts();
                $this->assertSame(SnipeCrypt::class, $casts[$field->db_column_name()]);
                $this->assertSame('boolean', $casts['extra_attribute']);
            }
            $this->assertCount(0, DB::getQueryLog());
        } finally {
            DB::disableQueryLog();
        }
    }

    public function test_a_belongs_to_fieldset_dependency_is_discovered_without_asset_model_keys(): void
    {
        $field = CustomField::factory()->encrypt()->create();
        $fieldset = CustomFieldset::factory()->create();
        $fieldset->fields()->attach($field, ['order' => 1, 'required' => false]);
        $asset = new class extends Asset
        {
            public static function bootHasAndValidatesCustomFields(): void {}

            public function custom_fieldset(): Relation
            {
                return $this->belongsTo(CustomFieldset::class, 'direct_fieldset_id');
            }
        };

        $asset->setRawAttributes(['direct_fieldset_id' => $fieldset->id]);
        $this->assertSame(SnipeCrypt::class, $asset->getCasts()[$field->db_column_name()]);
        $this->assertSame($fieldset->id, $asset->custom_fieldset->id);
    }
}
