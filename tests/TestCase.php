<?php

namespace Tests;

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;
use Tests\Support\AssertsAgainstSlackNotifications;
use Tests\Support\AssertHasActionLogs;
use Tests\Support\CanSkipTests;
use Tests\Support\CustomTestMacros;
use Tests\Support\InteractsWithAuthentication;
use Tests\Support\InitializesSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


abstract class TestCase extends BaseTestCase
{
    use AssertsAgainstSlackNotifications;
    use CanSkipTests;
    use CreatesApplication;
    use CustomTestMacros;
    use InteractsWithAuthentication;
    use InitializesSettings;
    use LazilyRefreshDatabase;
    use AssertHasActionLogs;


    private array $globallyDisabledMiddleware = [
        SecurityHeaders::class,
    ];

    protected function setUp(): void
    {
        $this->guardAgainstMissingEnv();

        parent::setUp();

        $this->registerCustomMacros();

        $this->withoutMiddleware($this->globallyDisabledMiddleware);

        $this->initializeSettings();

        config(['app.timnezone' => 'UTC']);
        @date_default_timezone_set('UTC');
        \Carbon::setLocale('en');

        try {
            \DB::statement("SET time_zone = '+00:00'");
            
        } catch (\Throwable $e) {
            
        }
    }

    private function guardAgainstMissingEnv(): void
    {
        if (!file_exists(realpath(__DIR__ . '/../') . '/.env.testing')) {
            throw new RuntimeException(
                '.env.testing file does not exist. Aborting to avoid wiping your local database.'
            );
        }
    }
}
