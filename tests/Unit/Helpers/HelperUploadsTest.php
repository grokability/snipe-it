<?php

namespace Tests\Unit\Helpers;

use App\Helpers\Helper;
use App\Models\Asset;
use App\Models\CustomField;
use Tests\TestCase;

class HelperUploadsTest extends TestCase
{
    private function tempFile(string $content, string $ext = 'tmp'): string
    {
        $path = sys_get_temp_dir().'/snipe_test_'.uniqid().'.'.$ext;
        file_put_contents($path, $content);

        return $path;
    }

    public function test_check_upload_is_image(): void
    {
        // PNG 1x1 valido (base64).
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M8AAAMCAYAAARRJON8AAAAASUVORK5CYII=');
        $imagePath = $this->tempFile($png, 'png');
        $textPath = $this->tempFile('just text', 'txt');

        $this->assertNotFalse(Helper::checkUploadIsImage($imagePath));
        $this->assertFalse(Helper::checkUploadIsImage($textPath));

        @unlink($imagePath);
        @unlink($textPath);
    }

    public function test_check_upload_is_video_and_audio_false_for_text(): void
    {
        $textPath = $this->tempFile('not media', 'txt');

        $this->assertFalse(Helper::checkUploadIsVideo($textPath));
        $this->assertFalse(Helper::checkUploadIsAudio($textPath));

        @unlink($textPath);
    }

    public function test_check_if_required_returns_bool(): void
    {
        $this->assertIsBool(Helper::checkIfRequired(Asset::class, 'asset_tag'));
        $this->assertIsBool(Helper::checkIfRequired(Asset::class, 'notes'));
    }

    public function test_graceful_decrypt_returns_string_for_non_encrypted_field(): void
    {
        $field = CustomField::factory()->create(['field_encrypted' => false]);

        $this->assertEquals('plain value', Helper::gracefulDecrypt($field, 'plain value'));
    }

    public function test_check_low_inventory_returns_array(): void
    {
        $this->assertIsArray(Helper::checkLowInventory());
    }
}
