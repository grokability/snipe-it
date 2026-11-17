<?php

namespace App\Listeners;

use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Support\Facades\Artisan;

/**
 * Regenerate the ide-helper model stub after each `php artisan migrate`
 * run so Larastan and the IDE see newly-added / renamed columns the
 * same day the migration lands. Production upgrades run
 * `migrate` without the dev dependency, so the class_exists guard
 * short-circuits before the artisan call that would otherwise throw
 * CommandNotFoundException and abort the upgrade.
 */
class RegenerateIdeHelperAfterMigrate
{
    public function handle(MigrationsEnded $event): void
    {
        if (! class_exists(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class)) {
            return;
        }

        // Skip during the test suite. CI runs `migrate --force` as
        // part of test setup, and regenerating the stub here is both
        // unnecessary (tests use the committed snapshot) and
        // problematic. The dev-tooling pass has been observed to
        // break PHP 8.2 autoload on CI in ways that didn't reproduce
        // locally. The migrate listener is a developer-ergonomics
        // feature for local work.
        if (app()->environment('testing')) {
            return;
        }

        try {
            Artisan::call('ide-helper:models', [
                '--write-mixin' => true,
                '--no-interaction' => true,
            ]);
        } catch (\Throwable) {
            // Dev tooling only. Never fail a migrate because the stub
            // couldn't be regenerated. Run manually to see errors.
        }
    }
}
