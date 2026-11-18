<?php

use App\Models\Asset;
use App\Models\CustomField;
use App\Models\User;

function findFieldEntry(array $rows, string $dbColumn): ?array
{
    foreach ($rows as $row) {
        $entry = ($row['log_meta'] ?? [])[$dbColumn] ?? null;
        if ($entry !== null) {
            return $entry;
        }
    }

    return null;
}

function updateAssetEncryptedField(Asset $asset, CustomField $field, string $value, User $actor): void
{
    test()->actingAsForApi($actor)
        ->patchJson(route('api.assets.update', $asset->id), [
            $field->db_column_name() => $value,
        ])
        ->assertOk();
}

function getUpdateRows(Asset $asset, User $viewer): array
{
    return test()->actingAsForApi($viewer)
        ->getJson(route('api.assets.history', ['asset' => $asset->id, 'action_type' => 'update']))
        ->assertOk()
        ->json('rows');
}

test('encrypted custom field values are visible to users with encrypted field permission', function () {
    $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

    $field = CustomField::factory()->testEncrypted()->create();
    $asset = Asset::factory()->hasEncryptedCustomField($field)->create([
        $field->db_column => 'safe initial value',
    ]);
    $superuser = User::factory()->superuser()->create();

    updateAssetEncryptedField($asset, $field, '<img src=x onerror=alert(1)>', $superuser);

    $viewer = User::factory()->viewAssets()->viewAssetHistory()->viewEncryptedCustomFields()->create();

    $fieldEntry = findFieldEntry(getUpdateRows($asset, $viewer), $field->db_column);
    expect($fieldEntry)->not->toBeNull('Encrypted field change should be visible to users with the encrypted fields permission');

    $newValue = $fieldEntry['new'];
    $this->assertStringNotContainsString('<img', $newValue, 'Raw HTML tag must not appear in history log_meta');
    $this->assertStringContainsString('&lt;', $newValue, 'Value should be HTML-encoded in history log_meta');
});

test('encrypted custom field values are masked for users without encrypted field permission', function () {
    $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

    $field = CustomField::factory()->testEncrypted()->create();
    $asset = Asset::factory()->hasEncryptedCustomField($field)->create([
        $field->db_column => 'safe initial value',
    ]);
    $superuser = User::factory()->superuser()->create();

    updateAssetEncryptedField($asset, $field, '<img src=x onerror=alert(1)>', $superuser);

    $viewer = User::factory()->viewAssets()->viewAssetHistory()->create();

    $fieldEntry = findFieldEntry(getUpdateRows($asset, $viewer), $field->db_column);

    if ($fieldEntry !== null) {
        expect($fieldEntry['new'])->toEqual('************', 'Users without encrypted field permission should see masked value');
        $this->assertStringNotContainsString('<img', $fieldEntry['new']);
    }
});
