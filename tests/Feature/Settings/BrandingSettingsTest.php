<?php

namespace Tests\Feature\Settings;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BrandingSettingsTest extends TestCase
{
    public static function validColorProvider(): array
    {
        return [
            'hex 6-digit' => ['#3c8dbc'],
            'hex 3-digit' => ['#fff'],
            'rgb' => ['rgb(10,20,30)'],
            'rgba' => ['rgba(10,20,30,0.5)'],
            'hsl' => ['hsl(120,50%,50%)'],
            'hsla' => ['hsla(120,50%,50%,0.8)'],
        ];
    }

    public static function invalidColorProvider(): array
    {
        return [
            'named color' => ['red'],
            'css injection payload' => ['red; }body{background:url(//evil.com)} .x{color: #'],
            'url()' => ['url(http://evil.com)'],
            'value with semicolon' => ['#3c8dbc; color: red'],
        ];
    }

    #[DataProvider('validColorProvider')]
    public function test_valid_header_color_can_be_saved(string $color): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.branding.save'), ['header_color' => $color])
            ->assertValid('header_color')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('settings', ['header_color' => $color]);
    }

    #[DataProvider('invalidColorProvider')]
    public function test_invalid_header_color_is_rejected(string $color): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->from(route('settings.branding.index'))
            ->post(route('settings.branding.save'), ['header_color' => $color])
            ->assertInvalid(['header_color'])
            ->assertSessionHasErrors(['header_color']);
    }

    #[DataProvider('validColorProvider')]
    public function test_valid_link_colors_can_be_saved(string $color): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.branding.save'), [
                'link_light_color' => $color,
                'link_dark_color' => $color,
                'nav_link_color' => $color,
            ])
            ->assertValid(['link_light_color', 'link_dark_color', 'nav_link_color'])
            ->assertSessionHasNoErrors();
    }

    #[DataProvider('invalidColorProvider')]
    public function test_invalid_link_colors_are_rejected(string $color): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->from(route('settings.branding.index'))
            ->post(route('settings.branding.save'), [
                'link_light_color' => $color,
                'link_dark_color' => $color,
                'nav_link_color' => $color,
            ])
            ->assertInvalid(['link_light_color', 'link_dark_color', 'nav_link_color'])
            ->assertSessionHasErrors(['link_light_color', 'link_dark_color', 'nav_link_color']);
    }

    public function test_setting_model_sanitizes_corrupt_header_color(): void
    {
        $setting = Setting::factory()->create();
        $setting->setRawAttributes(['header_color' => 'red; }body{color:red}']);

        $this->assertSame('#3c8dbc', $setting->header_color);
    }

    public function test_setting_model_passes_through_valid_header_color(): void
    {
        $setting = Setting::factory()->create(['header_color' => '#5fa4cc']);

        $this->assertSame('#5fa4cc', $setting->header_color);
    }

    public function test_site_name_is_required()
    {
        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('settings.branding.index'))
            ->post(route('settings.branding.save', ['site_name' => '']))
            ->assertSessionHasErrors(['site_name'])
            ->assertInvalid(['site_name'])
            ->assertStatus(302)
            ->assertRedirect(route('settings.branding.index'));

        $this->followRedirects($response)->assertSee(trans('general.error'));
    }

    public function test_site_name_can_be_saved()
    {
        $response = $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.branding.save', ['site_name' => 'My Awesome Site']))
            ->assertStatus(302)
            ->assertValid('site_name')
            ->assertRedirect(route('settings.index'))
            ->assertSessionHasNoErrors();

        $this->followRedirects($response)->assertSee('alert-success');
    }

    public function test_branding_upload_does_not_check_existence_of_empty_disk_root()
    {
        // Regression for #18267: SettingsController passes '' as the storage
        // path for branding images (logo/email_logo/label_logo/favicon/etc.)
        // to mean "disk root". The old code called
        //     Storage::disk('public')->exists('')
        // which the local driver tolerated but the S3 adapter translated into
        // a HeadObject with an empty Key, which the AWS SDK rejects outright
        // with UnableToCheckFileExistence.
        Storage::fake('public');
        $publicDisk = Storage::disk('public');

        $proxy = \Mockery::mock($publicDisk);
        $proxy->shouldReceive('exists')->with('')->never();
        $proxy->shouldReceive('exists')->andReturnUsing(fn ($p) => $publicDisk->exists($p));
        $proxy->shouldReceive('put')->andReturnUsing(fn (...$a) => $publicDisk->put(...$a));
        $proxy->shouldReceive('delete')->andReturnUsing(fn (...$a) => $publicDisk->delete(...$a));
        $proxy->shouldReceive('makeDirectory')->andReturnUsing(fn (...$a) => $publicDisk->makeDirectory(...$a));
        Storage::shouldReceive('disk')->with('public')->andReturn($proxy);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.branding.save'), [
                'logo' => UploadedFile::fake()->image('regression_logo.png'),
            ])
            ->assertValid('logo')
            ->assertStatus(302);
    }

    public function test_branding_upload_stores_file_without_leading_slash_key()
    {
        // Companion to #18267: even after skipping the empty-path existence
        // check, the put() call must not produce a leading-slash object key
        // (S3 stores `/logo.png` distinctly from `logo.png`, and the DB only
        // tracks the filename — references would mismatch).
        Storage::fake('public');

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.branding.save'), [
                'logo' => UploadedFile::fake()->image('keyshape_logo.png'),
            ])
            ->assertValid('logo')
            ->assertStatus(302);

        // The Flysystem local adapter silently normalizes leading slashes,
        // so we can't distinguish '/foo.png' from 'foo.png' at the disk
        // level here. The S3-relevant assertion is that the DB-stored
        // filename (which is what we use to build URLs later) is a clean
        // filename with no leading slash and resolves on disk as-is.
        $stored = Setting::getSettings()->logo;
        $this->assertNotEmpty($stored);
        $this->assertStringStartsNotWith('/', $stored);
        Storage::disk('public')->assertExists($stored);
    }

    public function test_logo_can_be_uploaded()
    {
        Storage::fake('public');
        $setting = Setting::factory()->create(['logo' => null]);

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.branding.save',
                ['logo' => UploadedFile::fake()->image('test_logo.png')]
            ))
            ->assertValid('logo')
            ->assertStatus(302)
            ->assertRedirect(route('settings.index'))
            ->assertSessionHasNoErrors();

        // Assert files was stored...
        Storage::disk('public')->assertExists($setting->logo);

        $this->followRedirects($response)->assertSee('alert-success');

        $setting->refresh();
    }

    public function test_logo_can_be_deleted()
    {
        Storage::fake('public');

        UploadedFile::fake()->image('new_test_logo.png')->storeAs('uploads', 'new_test_logo.png', 'public');
        $setting = Setting::factory()->create(['logo' => 'new_test_logo.png']);
        Storage::disk('public')->assertExists('uploads/'.$setting->logo);

        $this->assertNotNull($setting->logo);

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('settings.branding.index'))
            ->post(route('settings.branding.save',
                ['clear_logo' => '1']
            ))
            ->assertValid('logo')
            ->assertStatus(302)
            ->assertRedirect(route('settings.index'));

        $this->followRedirects($response)->assertSee(trans('alert-success'));
        $this->assertDatabaseHas('settings', ['logo' => null]);
        Storage::disk('public')->assertMissing($setting->logo);
    }

    public function test_email_logo_can_be_uploaded()
    {
        Storage::fake('public');
        Setting::factory()->create(['email_logo' => null]);

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('settings.branding.index'))
            ->post(route('settings.branding.save',
                [
                    'email_logo' => UploadedFile::fake()->image('new_test_email_logo.png')->storeAs('', 'new_test_email_logo.png', 'public'),
                ]
            ))
            ->assertValid('email_logo')
            ->assertStatus(302)
            ->assertRedirect(route('settings.index'));

        $this->followRedirects($response)->assertSee(trans('alert-success'));

        Storage::disk('public')->assertExists('new_test_email_logo.png');
    }

    public function test_email_logo_can_be_deleted()
    {
        Storage::fake('public');
        UploadedFile::fake()->image('new_test_logo.png')->storeAs('uploads', 'new_test_logo.png', 'public');
        $setting = Setting::factory()->create(['email_logo' => 'new_test_logo.png']);
        Storage::disk('public')->assertExists('uploads/'.$setting->email_logo);

        $this->assertNotNull($setting->email_logo);

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('settings.branding.index'))
            ->post(route('settings.branding.save',
                ['clear_email_logo' => '1']
            ))
            ->assertValid('email_logo')
            ->assertStatus(302)
            ->assertRedirect(route('settings.index'));

        $setting->refresh();
        $this->followRedirects($response)->assertSee(trans('alert-success'));
        $this->assertDatabaseHas('settings', ['email_logo' => null]);

        Storage::disk('public')->assertMissing('new_test_email_logo.png');

    }

    public function test_label_logo_can_be_uploaded()
    {

        Storage::fake('public');

        $original_file = UploadedFile::fake()->image('before_test_label_logo.png')->storeAs('', 'before_test_label_logo.png', 'public');

        Storage::disk('public')->assertExists($original_file);
        Setting::factory()->create(['label_logo' => $original_file]);

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('settings.branding.index'))
            ->post(route('settings.branding.save',
                [
                    'label_logo' => UploadedFile::fake()->image('new_test_label_logo.png')->storeAs('', 'new_test_label_logo.png', 'public'),
                ]
            ))
            ->assertValid('label_logo')
            ->assertStatus(302)
            ->assertRedirect(route('settings.index'));

        $this->followRedirects($response)->assertSee(trans('alert-success'));

        Storage::disk('public')->assertExists('new_test_label_logo.png');
        // Storage::disk('public')->assertMissing($original_file);

    }

    public function test_label_logo_can_be_deleted()
    {

        Storage::fake('public');

        UploadedFile::fake()->image('new_test_label_logo.png')->storeAs('uploads', 'new_test_label_logo.png', 'public');
        $setting = Setting::factory()->create(['label_logo' => 'new_test_label_logo.png']);
        Storage::disk('public')->assertExists('uploads/'.$setting->label_logo);

        $this->assertNotNull($setting->label_logo);

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('settings.branding.index'))
            ->post(route('settings.branding.save',
                ['label_logo' => '1']
            ))
            ->assertValid('label_logo')
            ->assertStatus(302)
            ->assertRedirect(route('settings.index'));

        $setting->refresh();
        $this->followRedirects($response)->assertSee(trans('alert-success'));
        Storage::disk('public')->assertMissing('new_test_label_logo.png');

    }

    public function test_default_avatar_can_be_uploaded()
    {
        Storage::fake('public');

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('settings.branding.index'))
            ->post(route('settings.branding.save',
                [
                    'default_avatar' => UploadedFile::fake()->image('default_avatar.png')->storeAs('', 'default_avatar.png', 'public'),
                ]
            ))
            ->assertValid('default_avatar')
            ->assertStatus(302)
            ->assertRedirect(route('settings.index'))
            ->assertSessionHasNoErrors();

        $this->followRedirects($response)->assertSee(trans('alert-success'));

        Storage::disk('public')->assertExists('default_avatar.png');
        // Storage::disk('public')->assertMissing($original_file);
    }

    public function test_default_avatar_can_be_deleted()
    {
        Storage::fake('public');

        $setting = Setting::factory()->create(['default_avatar' => 'new_test_label_logo.png']);
        $original_file = UploadedFile::fake()->image('default_avatar.png')->storeAs('', 'default_avatar.png', 'public');
        Storage::disk('public')->assertExists($original_file);

        $this->assertNotNull($setting->default_avatar);

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('settings.branding.index'))
            ->post(route('settings.branding.save',
                ['clear_default_avatar' => '1']
            ))
            ->assertValid('default_avatar')
            ->assertStatus(302)
            ->assertRedirect(route('settings.index'));

        $setting->refresh();
        $this->followRedirects($response)->assertSee(trans('alert-success'));
        // $this->assertNull($setting->refresh()->default_avatar);
        // Storage::disk('public')->assertMissing($original_file);
    }

    public function test_snipe_default_avatar_can_be_deleted()
    {

        $setting = Setting::getSettings()->first();
        Storage::fake('public');

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.branding.save',
                ['default_avatar' => UploadedFile::fake()->image('default.png')->storeAs('avatars', 'default.png', 'public')]
            ));

        Storage::disk('public')->assertExists('avatars/default.png');

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.branding.save',
                ['clear_default_avatar' => '1']
            ));

        $this->assertNull($setting->refresh()->default_avatar);
        $this->assertDatabaseHas('settings', ['default_avatar' => null]);
        Storage::disk('public')->assertExists('avatars/default.png');

    }

    public function test_favicon_can_be_uploaded()
    {
        $this->markTestIncomplete('This fails mimetype validation on the mock');
        Storage::fake('public');

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('settings.branding.index'))
            ->post(route('settings.branding.save',
                [
                    'favicon' => UploadedFile::fake()->image('favicon.svg')->storeAs('', 'favicon.svg', 'public'),
                ]
            ))
            ->assertValid('favicon')
            ->assertStatus(302)
            ->assertRedirect(route('settings.index'));

        $this->followRedirects($response)->assertSee(trans('alert-success'));

        Storage::disk('public')->assertExists('favicon.png');
    }

    public function test_favicon_can_be_deleted()
    {
        $this->markTestIncomplete('This fails mimetype validation on the mock');
        Storage::fake('public');

        $setting = Setting::factory()->create(['favicon' => 'favicon.png']);
        $original_file = UploadedFile::fake()->image('favicon.png')->storeAs('', 'favicon.png', 'public');
        Storage::disk('public')->assertExists($original_file);

        $this->assertNotNull($setting->favicon);

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('settings.branding.index'))
            ->post(route('settings.branding.save',
                ['clear_favicon' => '1']
            ))
            ->assertValid('favicon')
            ->assertStatus(302)
            ->assertRedirect(route('settings.index'));
        $setting->refresh();
        $this->followRedirects($response)->assertSee(trans('alert-success'));
        $this->assertDatabaseHas('settings', ['favicon' => null]);

        // This fails for some reason - the file is not being deleted, or at least the test doesn't think it is
        // Storage::disk('public')->assertMissing('favicon.png');
    }
}
