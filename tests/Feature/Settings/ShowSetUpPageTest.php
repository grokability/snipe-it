<?php

namespace Tests\Feature\Settings;

use PHPUnit\Framework\Attributes\DataProvider;
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
use Tests\TestCase;

class ShowSetUpPageTest extends TestCase
{

    static ?TestResponse $latestResponse;
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

    public function testView(): void
    {
        $this->getSetUpPageResponse()->assertOk()->assertViewIs('setup.index');
    }

    public function testWillShowErrorMessageWhenDatabaseConnectionCannotBeEstablished(): void
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

    public function testWillNotShowErrorMessageWhenDatabaseIsConnected(): void
    {
        $this->getSetUpPageResponse()->assertOk();

        $this->assertSeeDatabaseConnectionErrorMessage(false);
    }

    public function testWillShowErrorMessageWhenDebugModeIsEnabledAndAppEnvironmentIsSetToProduction(): void
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

    public function testWillNotShowErrorWhenDebugModeIsEnabledAndAppEnvironmentIsSetToLocal(): void
    {
        config(['app.debug' => true]);

        $this->app->bind('env', fn () => 'local');

        $this->getSetUpPageResponse()->assertOk();

        $this->assertSeeDebugModeMisconfigurationErrorMessage(false);
    }

    public function testWillNotShowErrorWhenDebugModeIsDisabledAndAppEnvironmentIsSetToProduction(): void
    {
        config(['app.debug' => false]);

        $this->app->bind('env', fn () => 'production');

        $this->getSetUpPageResponse()->assertOk();

        $this->assertSeeDebugModeMisconfigurationErrorMessage(false);
    }

    public function testWillShowErrorWhenEnvironmentIsLocal(): void
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

    public function testWillNotShowErrorWhenEnvironmentIsProduction(): void
    {
        $this->app->bind('env', fn () => 'production');

        $this->getSetUpPageResponse()->assertOk();

        $this->assertSeeEnvironmentMisconfigurationErrorMessage(false);
    }

    public function testWillCheckDotEnvFileVisibility(): void
    {
        $this->getSetUpPageResponse()->assertOk();

        Http::assertSent(function (Request $request) {
            $this->assertEquals('GET', $request->method());
            $this->assertEquals(URL::to('.env'), $request->url());
            return true;
        });
    }

    #[DataProvider('willShowErrorWhenDotEnvFileIsAccessibleViaHttpData')]
    public function testWillShowErrorWhenDotEnvFileIsAccessibleViaHttp(int $statusCode): void
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

    public function testWillNotShowErrorWhenDotEnvFileIsNotAccessibleViaHttp(): void
    {
        $this->getSetUpPageResponse()->assertOk();

        $this->assertSeeDotEnvFileExposedErrorMessage(false);
    }

    public function testWillShowErrorWhenDotEnvFileVisibilityCheckRequestFails(): void
    {
        $this->preventStrayRequest = false;

        Http::fake([URL::to('.env') => fn () => throw new ConnectionException('Some curl error message.')]);

        Log::setEventDispatcher(Event::fake());

        $this->getSetUpPageResponse()->assertOk();

        $this->assertSeeDotEnvFileExposedErrorMessage();

        Event::assertDispatched(function (MessageLogged $event) {
            $this->assertEquals('debug', $event->level);
            $this->assertEquals('Some curl error message.', $event->message);

            return true;
        });
    }

    public function testWillShowErrorMessageWhenAppUrlIsNotSameWithPageUrl(): void
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

    public function testWillNotShowErrorMessageWhenAppUrlIsSameWithPageUrl(): void
    {
        $this->getSetUpPageResponse()->assertOk();

        $this->assertSeeAppUrlMisconfigurationErrorMessage(false);
    }

    public function testWhenAppUrlContainsTrailingSlash(): void
    {
        config(['app.url' => 'http://www.github.com/']);

        $this->getSetUpPageResponse()->assertOk();

        $this->assertSeeAppUrlMisconfigurationErrorMessage();
    }

    public function testWillSeeDirectoryPermissionErrorWhenStoragePathIsNotWritable(): void
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

        self::$latestResponse->assertSee($successMessage, false)->assertDontSee($errorMessage,false);
    }

    public function testWillNotSeeDirectoryPermissionErrorWhenStoragePathIsWritable(): void
    {
        File::shouldReceive('isWritable')->andReturn(true);

        $this->getSetUpPageResponse()->assertOk();

        $this->assertSeeDirectoryPermissionError(false);
    }

    public function testInvalidTLSCertsOkWhenCheckingForEnvFile()
    {
        //set the weird bad SSL cert place - https://self-signed.badssl.com
        $this->markTestIncomplete("Not yet sure how to write this test, it requires messing with .env ...");
        $this->assertTrue((new SettingsController())->dotEnvFileIsExposed());
    }
}
