<?php

use App\Models\Asset;
use App\Models\CustomField;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

test('key rotation reads old ciphertext and writes one layer with the current key', function () {
    $field = CustomField::factory()->encrypt()->create();
    $asset = Asset::factory()->hasEncryptedCustomField($field)->create();
    $oldKey = Encrypter::generateKey(config('app.cipher'));
    $oldEncrypter = new Encrypter($oldKey, config('app.cipher'));
    DB::table('assets')->where('id', $asset->id)->update([$field->db_column => $oldEncrypter->encrypt('secret')]);

    $this->artisan('snipeit:rotate-key', ['previous_key' => 'base64:'.base64_encode($oldKey), '--force' => true])->assertExitCode(0);

    $asset->refresh();
    expect(Crypt::decrypt($asset->getRawOriginal($field->db_column)))->toBe('secret');
    expect($asset->{$field->db_column})->toBe('secret');
});

test('key rotation preserves values that cannot be decrypted with the old key', function () {
    $field = CustomField::factory()->encrypt()->create();
    $asset = Asset::factory()->hasEncryptedCustomField($field)->create();
    DB::table('assets')->where('id', $asset->id)->update([$field->db_column => 'unreadable']);
    $oldKey = Encrypter::generateKey(config('app.cipher'));

    $this->artisan('snipeit:rotate-key', ['previous_key' => 'base64:'.base64_encode($oldKey), '--force' => true])->assertExitCode(0);

    expect($asset->fresh()->getRawOriginal($field->db_column))->toBe('unreadable');
});

test('enabling encryption converts plaintext once and keeps empty values empty', function () {
    $field = CustomField::factory()->create();
    $asset = Asset::factory()->hasMultipleCustomFields([$field])->create([$field->db_column => 'secret']);
    $empty = Asset::factory()->hasMultipleCustomFields([$field])->create();

    $this->artisan('snipeit:customfield-encryption', ['fieldname' => $field->db_column])->assertExitCode(0);

    expect((bool) $field->fresh()->field_encrypted)->toBeTrue();
    expect(Crypt::decrypt($asset->fresh()->getRawOriginal($field->db_column)))->toBe('secret');
    expect($empty->fresh()->getRawOriginal($field->db_column))->toBeNull();
});
