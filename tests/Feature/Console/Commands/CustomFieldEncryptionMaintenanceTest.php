<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Asset;
use App\Models\CustomField;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CustomFieldEncryptionMaintenanceTest extends TestCase
{
    public function test_key_rotation_reads_old_ciphertext_and_writes_one_layer_with_the_current_key(): void
    {
        $field = CustomField::factory()->encrypt()->create();
        $asset = Asset::factory()->hasEncryptedCustomField($field)->create();
        $oldKey = Encrypter::generateKey(config('app.cipher'));
        $oldEncrypter = new Encrypter($oldKey, config('app.cipher'));
        DB::table('assets')->where('id', $asset->id)->update([$field->db_column => $oldEncrypter->encrypt('secret')]);

        $this->artisan('snipeit:rotate-key', ['previous_key' => 'base64:'.base64_encode($oldKey), '--force' => true])->assertExitCode(0);

        $asset->refresh();
        $this->assertSame('secret', Crypt::decrypt($asset->getRawOriginal($field->db_column)));
        $this->assertSame('secret', $asset->{$field->db_column});
    }

    public function test_key_rotation_preserves_values_that_cannot_be_decrypted_with_the_old_key(): void
    {
        $field = CustomField::factory()->encrypt()->create();
        $asset = Asset::factory()->hasEncryptedCustomField($field)->create();
        DB::table('assets')->where('id', $asset->id)->update([$field->db_column => 'unreadable']);
        $oldKey = Encrypter::generateKey(config('app.cipher'));

        $this->artisan('snipeit:rotate-key', ['previous_key' => 'base64:'.base64_encode($oldKey), '--force' => true])->assertExitCode(0);

        $this->assertSame('unreadable', $asset->fresh()->getRawOriginal($field->db_column));
    }

    public function test_enabling_encryption_converts_plaintext_once_and_keeps_empty_values_empty(): void
    {
        $field = CustomField::factory()->create();
        $asset = Asset::factory()->hasMultipleCustomFields([$field])->create([$field->db_column => 'secret']);
        $empty = Asset::factory()->hasMultipleCustomFields([$field])->create();

        $this->artisan('snipeit:customfield-encryption', ['fieldname' => $field->db_column])->assertExitCode(0);

        $this->assertTrue((bool) $field->fresh()->field_encrypted);
        $this->assertSame('secret', Crypt::decrypt($asset->fresh()->getRawOriginal($field->db_column)));
        $this->assertNull($empty->fresh()->getRawOriginal($field->db_column));
    }
}
