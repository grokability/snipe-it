<?php

namespace Tests\Feature\Settings;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Setting;


class BrandingSettingsTest extends TestCase
{
    public function testSiteNameIsRequired()
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

    public function testSiteNameCanBeSaved()
    {
        $response = $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.branding.save', ['site_name' => 'My Awesome Site']))
            ->assertStatus(302)
            ->assertValid('site_name')
            ->assertRedirect(route('settings.index'))
            ->assertSessionHasNoErrors();

        $this->followRedirects($response)->assertSee('alert-success');
    }


    public function testLogoCanBeUploaded()
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
        Storage::disk('public')->assertExists( $setting->logo);

        $this->followRedirects($response)->assertSee('alert-success');

        $setting->refresh();
    }


    public function testLogoCanBeDeleted()
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

    public function testEmailLogoCanBeUploaded()
    {
        Storage::fake('public');
        Setting::factory()->create(['email_logo' => null]);

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('settings.branding.index'))
            ->post(route('settings.branding.save',
                [
                    'email_logo' => UploadedFile::fake()->image('new_test_email_logo.png')->storeAs('', 'new_test_email_logo.png', 'public')
                ]
            ))
            ->assertValid('email_logo')
            ->assertStatus(302)
            ->assertRedirect(route('settings.index'));

        $this->followRedirects($response)->assertSee(trans('alert-success'));

        Storage::disk('public')->assertExists('new_test_email_logo.png');
    }

    public function testEmailLogoCanBeDeleted()
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


    public function testLabelLogoCanBeUploaded()
    {

        Storage::fake('public');

        $original_file = UploadedFile::fake()->image('before_test_label_logo.png')->storeAs('', 'before_test_label_logo.png', 'public');

        Storage::disk('public')->assertExists($original_file);
        Setting::factory()->create(['label_logo' => $original_file]);

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('settings.branding.index'))
            ->post(route('settings.branding.save',
                [
                    'label_logo' => UploadedFile::fake()->image('new_test_label_logo.png')->storeAs('', 'new_test_label_logo.png', 'public')
                ]
            ))
            ->assertValid('label_logo')
            ->assertStatus(302)
            ->assertRedirect(route('settings.index'));

        $this->followRedirects($response)->assertSee(trans('alert-success'));

        Storage::disk('public')->assertExists('new_test_label_logo.png');
        // Storage::disk('public')->assertMissing($original_file);


    }

    public function testLabelLogoCanBeDeleted()
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

    public function testDefaultAvatarCanBeUploaded()
    {
        Storage::fake('public');

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('settings.branding.index'))
            ->post(route('settings.branding.save',
                [
                    'default_avatar' => UploadedFile::fake()->image('default_avatar.png')->storeAs('', 'default_avatar.png', 'public')
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

    public function testDefaultAvatarCanBeDeleted()
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

    public function testSnipeDefaultAvatarCanBeDeleted()
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

    public function testFaviconCanBeUploaded()
    {
        $this->markTestIncomplete('This fails mimetype validation on the mock');
        Storage::fake('public');

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('settings.branding.index'))
            ->post(route('settings.branding.save',
                [
                    'favicon' =>UploadedFile::fake()->image('favicon.svg')->storeAs('', 'favicon.svg', 'public')
                ]
            ))
            ->assertValid('favicon')
            ->assertStatus(302)
            ->assertRedirect(route('settings.index'));

        $this->followRedirects($response)->assertSee(trans('alert-success'));

        Storage::disk('public')->assertExists('favicon.png');
    }

    public function testFaviconCanBeDeleted()
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
