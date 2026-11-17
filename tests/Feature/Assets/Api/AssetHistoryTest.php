<?php

namespace Tests\Feature\Assets\Api;

use App\Models\Asset;
use App\Models\CustomField;
use App\Models\User;
use Tests\TestCase;

class AssetHistoryTest extends TestCase
{
    private function findFieldEntry(array $rows, string $dbColumn): ?array
    {
        foreach ($rows as $row) {
            $entry = ($row['log_meta'] ?? [])[$dbColumn] ?? null;
            if ($entry !== null) {
                return $entry;
            }
        }

        return null;
    }

    private function updateAssetEncryptedField(Asset $asset, CustomField $field, string $value, User $actor): void
    {
        $this->actingAsForApi($actor)
            ->patchJson(route('api.assets.update', $asset->id), [
                $field->db_column_name() => $value,
            ])
            ->assertOk();
    }

    private function getUpdateRows(Asset $asset, User $viewer): array
    {
        return $this->actingAsForApi($viewer)
            ->getJson(route('api.assets.history', ['asset' => $asset->id, 'action_type' => 'update']))
            ->assertOk()
            ->json('rows');
    }

    public function test_encrypted_custom_field_values_are_visible_to_users_with_encrypted_field_permission()
    {
        $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

        $field = CustomField::factory()->testEncrypted()->create();
        $asset = Asset::factory()->hasEncryptedCustomField($field)->create([
            $field->db_column => 'safe initial value',
        ]);
        $superuser = User::factory()->superuser()->create();

        $this->updateAssetEncryptedField($asset, $field, '<img src=x onerror=alert(1)>', $superuser);

        $viewer = User::factory()->viewAssets()->viewAssetHistory()->viewEncryptedCustomFields()->create();

        $fieldEntry = $this->findFieldEntry($this->getUpdateRows($asset, $viewer), $field->db_column);
        $this->assertNotNull($fieldEntry, 'Encrypted field change should be visible to users with the encrypted fields permission');

        $newValue = $fieldEntry['new'];
        $this->assertStringNotContainsString('<img', $newValue, 'Raw HTML tag must not appear in history log_meta');
        $this->assertStringContainsString('&lt;', $newValue, 'Value should be HTML-encoded in history log_meta');
    }

    public function test_encrypted_custom_field_values_are_masked_for_users_without_encrypted_field_permission()
    {
        $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

        $field = CustomField::factory()->testEncrypted()->create();
        $asset = Asset::factory()->hasEncryptedCustomField($field)->create([
            $field->db_column => 'safe initial value',
        ]);
        $superuser = User::factory()->superuser()->create();

        $this->updateAssetEncryptedField($asset, $field, '<img src=x onerror=alert(1)>', $superuser);

        $viewer = User::factory()->viewAssets()->viewAssetHistory()->create();

        $fieldEntry = $this->findFieldEntry($this->getUpdateRows($asset, $viewer), $field->db_column);

        if ($fieldEntry !== null) {
            $this->assertEquals('************', $fieldEntry['new'], 'Users without encrypted field permission should see masked value');
            $this->assertStringNotContainsString('<img', $fieldEntry['new']);
        }
    }
}
