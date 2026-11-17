<?php

namespace Tests\Feature\Uploads;

use App\Models\Manufacturer;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * ImageUploadRequest's rules() previously only applied `mimes:`, so an
 * authenticated user could submit a PNG with large declared dimensions
 * but a tiny on-disk footprint. Image::make() fully decoded the raster
 * (width * height * 4 bytes for RGBA) before resize() ran, which is the
 * attack surface for the compressed-image-to-huge-pixel-buffer amplification.
 *
 * The fix adds dimensions:max_width/max_height, which runs via
 * getimagesize() - header-read only, so the validator itself does not
 * allocate the pixel buffer. SVGs are hard-skipped by Laravel's dimensions
 * validator and continue to flow through the sanitizer path.
 */
class ImageUploadRequestDimensionsLimitsTest extends TestCase
{
    public function test_avatar_with_oversized_declared_dimensions_is_rejected_before_decode(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['avatar' => null]);

        $path = $this->trackTempFile(tempnam(sys_get_temp_dir(), 'png'));
        file_put_contents($path, $this->craftPngWithIhdrDimensions(50000, 50000));
        $upload = new UploadedFile($path, 'bomb.png', 'image/png', null, true);

        $response = $this->actingAs($user)
            ->post(route('profile.update'), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'avatar' => $upload,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('avatar');

        $user->refresh();
        $this->assertNull(
            $user->avatar,
            'A rejected avatar upload must not touch the stored avatar reference.',
        );
    }

    public function test_favicon_dimensions_are_capped_tighter_than_image_and_avatar(): void
    {
        Storage::fake('public');

        $path = $this->trackTempFile(tempnam(sys_get_temp_dir(), 'png'));
        file_put_contents($path, $this->craftPngWithIhdrDimensions(2048, 2048));
        $upload = new UploadedFile($path, 'big-favicon.png', 'image/png', null, true);

        $request = new \App\Http\Requests\ImageUploadRequest;
        $validator = \Illuminate\Support\Facades\Validator::make(
            ['favicon' => $upload, 'image' => $upload, 'avatar' => $upload],
            $request->rules(),
        );

        $errors = $validator->errors();
        $this->assertTrue($errors->has('favicon'), 'A 2048x2048 image must fail the favicon dimensions cap (1024).');
        $this->assertFalse($errors->has('image'), 'A 2048x2048 image must pass the image dimensions cap (4096).');
        $this->assertFalse($errors->has('avatar'), 'A 2048x2048 image must pass the avatar dimensions cap (4096).');
    }

    /**
     * Reporter-identified boundary (post-ed43b8bdcc fix, Wojciech
     * Ciemski): a 10000x10000 PNG compressed well under the file-size
     * cap passed the earlier 10000x10000 dimension cap, decoded to
     * 100MP (~400MB raw RGBA) and overran worker memory. Current cap
     * is 4096x4096 (~64MB raw RGBA). Verify a 4097-high image is
     * rejected on the dimension validator before any decoder sees it.
     */
    public function test_image_above_pixel_cap_is_rejected_at_the_boundary(): void
    {
        Storage::fake('public');

        $path = $this->trackTempFile(tempnam(sys_get_temp_dir(), 'png'));
        file_put_contents($path, $this->craftPngWithIhdrDimensions(4097, 4097));
        $upload = new UploadedFile($path, 'just-over.png', 'image/png', null, true);

        $request = new \App\Http\Requests\ImageUploadRequest;
        $validator = \Illuminate\Support\Facades\Validator::make(
            ['avatar' => $upload, 'image' => $upload],
            $request->rules(),
        );

        $this->assertTrue($validator->errors()->has('avatar'), '4097x4097 must fail the avatar dimension cap.');
        $this->assertTrue($validator->errors()->has('image'), '4097x4097 must fail the image dimension cap.');
    }

    /**
     * Reporter-identified unit mismatch (post-ed43b8bdcc fix, Wojciech
     * Ciemski): Laravel's `max:` rule on file validators treats its
     * argument as KIBIBYTES, not bytes. The earlier rule interpolated
     * Helper::file_upload_max_size()'s byte value directly, so with
     * upload_max_filesize=2M the rule read `max:2097152` and silently
     * permitted ~2 GiB uploads at the application layer. Verify that a
     * file over the real PHP upload limit now trips the `max:` rule.
     */
    public function test_file_size_max_rule_uses_kibibytes_not_bytes(): void
    {
        Storage::fake('public');

        // Craft a legitimate-dimension PNG padded to just over the PHP
        // upload limit so dimensions + mime pass, and only the `max:`
        // rule can be the one that rejects it.
        $path = $this->trackTempFile(tempnam(sys_get_temp_dir(), 'png'));
        $header = $this->craftPngWithIhdrDimensions(400, 400);
        file_put_contents($path, $header.str_repeat("\0", \App\Helpers\Helper::file_upload_max_size() + 1024 - strlen($header)));
        $upload = new UploadedFile($path, 'oversized.png', 'image/png', null, true);

        $request = new \App\Http\Requests\ImageUploadRequest;
        $validator = \Illuminate\Support\Facades\Validator::make(
            ['avatar' => $upload],
            $request->rules(),
        );

        $this->assertTrue(
            $validator->errors()->has('avatar'),
            'A file larger than file_upload_max_size() must fail the `max:` rule. If this fails, the rule is interpreting bytes as kilobytes and permitting ~1024x the intended cap.',
        );
    }

    public function test_normal_sized_avatar_still_passes_validation(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['avatar' => null]);

        $this->actingAs($user)
            ->post(route('profile.update'), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'avatar' => UploadedFile::fake()->image('normal.png', 400, 400),
            ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertNotNull(
            $user->avatar,
            'A normal-sized avatar must still be accepted and stored.',
        );
    }

    public function test_manufacturer_image_with_oversized_declared_dimensions_is_rejected(): void
    {
        Storage::fake('public');

        $manufacturer = Manufacturer::factory()->create(['image' => null]);

        $path = $this->trackTempFile(tempnam(sys_get_temp_dir(), 'png'));
        file_put_contents($path, $this->craftPngWithIhdrDimensions(50000, 50000));
        $upload = new UploadedFile($path, 'bomb.png', 'image/png', null, true);

        $this->actingAs(User::factory()->superuser()->create())
            ->put(route('manufacturers.update', $manufacturer), [
                'name' => $manufacturer->name,
                'image' => $upload,
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors('image');

        $manufacturer->refresh();
        $this->assertNull(
            $manufacturer->image,
            'A rejected image upload must not touch the stored image reference.',
        );
    }

    /**
     * Produces a minimal PNG that declares the given dimensions in its IHDR
     * chunk. getimagesize() reads the header and reports those dimensions
     * without decoding any raster data, so Laravel's dimensions validator
     * rejects the file before any image decoder runs. The file itself is
     * tens of bytes - no pixel buffer is ever allocated in the test.
     */
    private function craftPngWithIhdrDimensions(int $width, int $height): string
    {
        $signature = "\x89PNG\r\n\x1a\n";

        $ihdrData = pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0);
        $ihdrChunk = pack('N', 13).'IHDR'.$ihdrData.pack('N', crc32('IHDR'.$ihdrData));

        $iendChunk = pack('N', 0).'IEND'.pack('N', crc32('IEND'));

        return $signature.$ihdrChunk.$iendChunk;
    }
}
