<?php

namespace Tests\Feature\Settings;

use App\Http\Controllers\SettingsController;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;
use PDOException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ShowSetUpPageTest extends TestCase
{
    public static ?TestResponse $latestResponse;

    /**
     * We do not want to make actual http request on every test to check .env file
     * visibility because that can be really slow especially in some cases where an
     * actual server is not running.
     */
    protected bool $preventStrayRequest = true;

    protected function getSetUpPageResponse(): TestResponse
    {
        if ($this->preventStrayRequest) {
            Http::fake([URL::to('.env') => Http::response(null, 404)]);
        }

        self::$latestResponse = $this->get('/setup');

        return self::$latestResponse;
    }

    public function test_view(): void
    {
        $this->getSetUpPageResponse()->assertOk()->assertViewIs('setup.index');
    }

    public function test_will_show_error_message_when_database_connection_cannot_be_established(): void
    {
        Event::listen(function (QueryExecuted $query) {
            if ($query->sql === 'select 2 + 2') {
                throw new PDOException("SQLSTATE[HY000] [1045] Access denied for user ''@'localhost' (using password: NO)");
            }
        });

        $this->getSetUpPageResponse()->assertOk();

        $this->assertSeeDatabaseConnectionErrorMessage();
    }

    protected function assertSeeDatabaseConnectionErrorMessage(bool $shouldSee = true): void
    {
        $errorMessage = "D'oh! Looks like we can't connect to your database. Please update your database settings in your  <code>.env</code> file.";
        $successMessage = sprintf('Great work! Connected to <code>%s</code>', DB::connection()->getDatabaseName());

        if ($shouldSee) {
            self::$latestResponse->assertSee($errorMessage, false)->assertDontSee($successMessage, false);

            return;
        }

        self::$latestResponse->assertSee($successMessage, false)->assertDontSee($errorMessage, false);
    }

    public function test_will_not_show_error_message_when_database_is_connected(): void
    {
        $this->getSetUpPageResponse()->assertOk();

        $this->assertSeeDatabaseConnectionErrorMessage(false);
    }

    public function test_will_show_error_message_when_debug_mode_is_enabled_and_app_environment_is_set_to_production(): void
    {
        config(['app.debug' => true]);

        $this->app->bind('env', fn () => 'production');

        $this->getSetUpPageResponse()->assertOk();

        $this->assertSeeDebugModeMisconfigurationErrorMessage();
    }

    protected function assertSeeDebugModeMisconfigurationErrorMessage(bool $shouldSee = true): void
    {
        $errorMessage = 'Yikes! You should turn off debug mode unless you encounter any issues. Please update your <code>APP_DEBUG</code> settings in your  <code>.env</code> file';
        $successMessage = "Awesomesauce. Debug is either turned off, or you're running this in a non-production environment. (Don't forget to turn it off when you're ready to go live.)";

        if ($shouldSee) {
            self::$latestResponse->assertSee($errorMessage, false)->assertDontSee($successMessage, false);

            return;
        }

        self::$latestResponse->assertSee($successMessage, false)->assertDontSee($errorMessage, false);
    }

    public function test_will_not_show_error_when_debug_mode_is_enabled_and_app_environment_is_set_to_local(): void
    {
        config(['app.debug' => true]);

        $this->app->bind('env', fn () => 'local');

        $this->getSetUpPageResponse()->assertOk();

        $this->assertSeeDebugModeMisconfigurationErrorMessage(false);
    }

    public function test_will_not_show_error_when_debug_mode_is_disabled_and_app_environment_is_set_to_production(): void
    {
        config(['app.debug' => false]);

        $this->app->bind('env', fn () => 'production');

        $this->getSetUpPageResponse()->assertOk();

        $this->assertSeeDebugModeMisconfigurationErrorMessage(false);
    }

    public function test_will_show_error_when_environment_is_local(): void
    {
        $this->app->bind('env', fn () => 'local');

        $this->getSetUpPageResponse()->assertOk();

        $this->assertSeeEnvironmentMisconfigurationErrorMessage();
    }

    protected function assertSeeEnvironmentMisconfigurationErrorMessage(bool $shouldSee = true): void
    {
        $errorMessage = 'Your app is set <code>local</code> instead of <code>production</code> mode.';
        $successMessage = 'Your app is set to production mode. Rock on!';

        if ($shouldSee) {
            self::$latestResponse->assertSee($errorMessage, false)->assertDontSee($successMessage, false);

            return;
        }

        self::$latestResponse->assertSee($successMessage, false)->assertDontSee($errorMessage, false);
    }

    public function test_will_not_show_error_when_environment_is_production(): void
    {
        $this->app->bind('env', fn () => 'production');

        $this->getSetUpPageResponse()->assertOk();

        $this->assertSeeEnvironmentMisconfigurationErrorMessage(false);
    }

    public function test_will_check_dot_env_file_visibility(): void
    {
        $this->getSetUpPageResponse()->assertOk();

        Http::assertSent(function (Request $request) {
            $this->assertEquals('GET', $request->method());
            $this->assertEquals(URL::to('.env'), $request->url());

            return true;
        });
    }

    #[DataProvider('willShowErrorWhenDotEnvFileIsAccessibleViaHttpData')]
    public function test_will_show_error_when_dot_env_file_is_accessible_via_http(int $statusCode): void
    {
        $this->preventStrayRequest = false;

        Http::fake([URL::to('.env') => Http::response(null, $statusCode)]);

        $this->getSetUpPageResponse()->assertOk();

        Http::assertSent(function (Request $request, Response $response) use ($statusCode) {
            $this->assertEquals($statusCode, $response->status());

            return true;
        });

        $this->assertSeeDotEnvFileExposedErrorMessage();
    }

    public static function willShowErrorWhenDotEnvFileIsAccessibleViaHttpData(): array
    {
        return collect([200, 202, 204, 206])
            ->mapWithKeys(fn (int $code) => ["StatusCode: {$code}" => [$code]])
            ->all();
    }

    protected function assertSeeDotEnvFileExposedErrorMessage(bool $shouldSee = true): void
    {
        $errorMessage = "We cannot determine if your config file is exposed to the outside world, so you will have to manually verify this. You don't ever want anyone able to see that file. Ever. Ever ever. An exposed <code>.env</code> file can disclose sensitive data about your system and database.";
        $successMessage = "Sweet. It doesn't look like your <code>.env</code> file is exposed to the outside world. (You should double check this in a browser though. You don't ever want anyone able to see that file. Ever. Ever ever.) <a href=\"../../.env\">Click here to check now</a> (This should return a file not found or forbidden error.)";

        if ($shouldSee) {
            self::$latestResponse->assertSee($errorMessage, false)->assertDontSee($successMessage, false);

            return;
        }

        self::$latestResponse->assertSee($successMessage, false)->assertDontSee($errorMessage, false);
    }

    public function test_will_not_show_error_when_dot_env_file_is_not_accessible_via_http(): void
    {
        $this->getSetUpPageResponse()->assertOk();

        $this->assertSeeDotEnvFileExposedErrorMessage(false);
    }

    public function test_will_show_error_when_dot_env_file_visibility_check_request_fails(): void
    {
        $this->preventStrayRequest = false;

        Http::fake([URL::to('.env') => fn () => throw new ConnectionException('Some curl error message.')]);

        $this->getSetUpPageResponse()->assertOk();

        $this->assertSeeDotEnvFileExposedErrorMessage();
    }

    public function test_will_show_error_message_when_app_url_is_not_same_with_page_url(): void
    {
        config(['app.url' => 'http://www.github.com']);

        $this->getSetUpPageResponse()->assertOk();

        $this->assertSeeAppUrlMisconfigurationErrorMessage();
    }

    protected function assertSeeAppUrlMisconfigurationErrorMessage(bool $shouldSee = true): void
    {
        $url = URL::to('setup');

        $errorMessage = "Uh oh! Snipe-IT thinks your URL is http://www.github.com/setup, but your real URL is {$url}";
        $successMessage = 'That URL looks right! Good job!';

        if ($shouldSee) {
            self::$latestResponse->assertSee($errorMessage)->assertDontSee($successMessage);

            return;
        }

        self::$latestResponse->assertSee($successMessage)->assertDontSee($errorMessage);
    }

    public function test_will_not_show_error_message_when_app_url_is_same_with_page_url(): void
    {
        $this->getSetUpPageResponse()->assertOk();

        $this->assertSeeAppUrlMisconfigurationErrorMessage(false);
    }

    public function test_when_app_url_contains_trailing_slash(): void
    {
        config(['app.url' => 'http://www.github.com/']);

        $this->getSetUpPageResponse()->assertOk();

        $this->assertSeeAppUrlMisconfigurationErrorMessage();
    }

    public function test_will_see_directory_permission_error_when_storage_path_is_not_writable(): void
    {
        File::shouldReceive('isWritable')->andReturn(false);

        $this->getSetUpPageResponse()->assertOk();

        $this->assertSeeDirectoryPermissionError();
    }

    protected function assertSeeDirectoryPermissionError(bool $shouldSee = true): void
    {
        $storagePath = storage_path();

        $errorMessage = "Uh-oh. Your <code>{$storagePath}</code> directory (or sub-directories within) are not writable by the web-server. Those directories need to be writable by the web server in order for the app to work.";
        $successMessage = 'Yippee! Your app storage directory seems writable';

        if ($shouldSee) {
            self::$latestResponse->assertSee($errorMessage, false)->assertDontSee($successMessage, false);

            return;
        }

        self::$latestResponse->assertSee($successMessage, false)->assertDontSee($errorMessage, false);
    }

    public function test_will_not_see_directory_permission_error_when_storage_path_is_writable(): void
    {
        File::shouldReceive('isWritable')->andReturn(true);

        $this->getSetUpPageResponse()->assertOk();

        $this->assertSeeDirectoryPermissionError(false);
    }

    public function test_invalid_tls_certs_ok_when_checking_for_env_file()
    {
        // set the weird bad SSL cert place - https://self-signed.badssl.com
        $this->markTestIncomplete('Not yet sure how to write this test, it requires messing with .env ...');
        $this->assertTrue((new SettingsController)->dotEnvFileIsExposed());
    }
}
