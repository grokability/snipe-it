<?php

namespace Tests\Feature\Console;

use App\Console\Scheduling\ArtisanCommandArguments;
use App\Console\Scheduling\ShelllessArtisanEvent;
use App\Console\Scheduling\ShelllessSchedule;
use App\Console\Scheduling\ShelllessScheduleRunCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel;
use PHPUnit\Framework\TestCase;

/** No database writes: bootstrap real application, use isolated process fixtures. */
class ShelllessSchedulerTest extends TestCase
{
    private $app;

    private $kernel;

    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = require '/var/www/html/bootstrap/app.php';
        $this->kernel = $this->app->make(Kernel::class);
        $this->kernel->bootstrap();
        $this->app['config']->set('cache.default', 'array');
        $this->directory = sys_get_temp_dir().'/scheduler-regression-'.uniqid();
        mkdir($this->directory);
        putenv('SCHEDULER_TEST_MARKER=inherited-marker');
        putenv('SCHEDULER_TEST_EXIT=0');
        $_SERVER['SCHEDULER_TEST_MARKER'] = $_ENV['SCHEDULER_TEST_MARKER'] = 'inherited-marker';
        $_SERVER['SCHEDULER_TEST_EXIT'] = $_ENV['SCHEDULER_TEST_EXIT'] = '0';
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory.'/*') ?: [] as $path) {
            unlink($path);
        }
        rmdir($this->directory);
        putenv('SCHEDULER_TEST_MARKER');
        putenv('SCHEDULER_TEST_EXIT');
        unset($_SERVER['SCHEDULER_TEST_MARKER'], $_ENV['SCHEDULER_TEST_MARKER'], $_SERVER['SCHEDULER_TEST_EXIT'], $_ENV['SCHEDULER_TEST_EXIT']);
        restore_error_handler();
        restore_exception_handler();
        parent::tearDown();
    }

    private function fixtureEvent(array $arguments = [], bool $missing = false): ShelllessArtisanEvent
    {
        $event = (new ShelllessSchedule)->command('list', ['--raw']);
        $reflection = new \ReflectionProperty(ShelllessArtisanEvent::class, 'arguments');
        $reflection->setValue($event, array_merge([$missing ? '/no-such-scheduler-child' : PHP_BINARY, '/verification/child.php'], $arguments));

        return $event;
    }

    public function test_arguments_are_console_tokens_not_shell_instructions(): void
    {
        $tokens = ArtisanCommandArguments::compile('list "inline space"', [
            'positional space', '', '--flag', '--opt' => 'a "quote"; $(literal)',
            '--many' => ['one space', 'two'], '-v' => ['x', 'y'],
        ]);
        $this->assertSame(['list', 'inline space', 'positional space', '', '--flag', '--opt=a "quote"; $(literal)', '--many=one space', '--many=two', '-v', 'x', '-v', 'y'], array_slice($tokens, 2));
    }

    public function test_mutex_identity_and_registration_match_laravel_exactly(): void
    {
        $parameters = ['--opt' => 'space value'];
        $actual = (new ShelllessSchedule('UTC'))->command('list', $parameters)->daily()->withoutOverlapping();
        $baseline = (new Schedule('UTC'))->command('list', $parameters)->daily()->withoutOverlapping();
        $this->assertSame($baseline->command, $actual->command);
        $this->assertSame($baseline->mutexName(), $actual->mutexName());
        $this->assertSame($baseline->expression, $actual->expression);
        $this->assertSame($baseline->timezone, $actual->timezone);
        $this->assertInstanceOf(ShelllessScheduleRunCommand::class, $this->app->make(\Illuminate\Console\Scheduling\ScheduleRunCommand::class));
        $this->assertInstanceOf(ShelllessSchedule::class, $this->kernel->resolveConsoleSchedule());
    }

    public function test_real_child_argv_environment_and_merged_output_overwrite_append(): void
    {
        $path = $this->directory.'/output';
        file_put_contents($path, 'old-output');
        $event = $this->fixtureEvent(['space value', '', '$(literal)'])->sendOutputTo($path);
        $event->run($this->app);
        $output = file_get_contents($path);
        $this->assertSame(0, $event->exitCode);
        $this->assertStringNotContainsString('old-output', $output);
        $this->assertStringContainsString('child-stdout', $output);
        $this->assertStringContainsString('child-stderr', $output);
        $this->assertStringContainsString('inherited-marker', $output);
        $this->assertStringContainsString('space value', $output);
        $this->assertStringContainsString('context', $output);
        $event->appendOutputTo($path)->run($this->app);
        $this->assertSame(2, substr_count(file_get_contents($path), 'child-stdout'));
    }

    public function test_without_overlapping_skips_held_lock_and_releases_on_completion_and_failure(): void
    {
        $path = $this->directory.'/output';
        $event = $this->fixtureEvent()->sendOutputTo($path)->withoutOverlapping();
        $this->assertTrue($event->mutex->create($event));
        $event->run($this->app);
        $this->assertFileDoesNotExist($path);
        $event->mutex->forget($event);
        $event->run($this->app);
        $this->assertFileExists($path);
        $this->assertFalse($event->mutex->exists($event));
        $failed = $this->fixtureEvent([], true)->withoutOverlapping();
        try {
            $failed->run($this->app);
            // Fork-based proc_open builds expose launch ENOENT as child 127;
            // the production posix_spawn runtime is checked separately.
            $this->assertSame(127, $failed->exitCode);
        } catch (\Symfony\Component\Process\Exception\ProcessStartFailedException $error) {
            $this->assertStringContainsString('No such file', $error->getMessage());
        }
        $this->assertFalse($failed->mutex->exists($failed));
    }

    public function test_nonzero_child_status_is_preserved_and_default_output_suppressed(): void
    {
        putenv('SCHEDULER_TEST_EXIT=23');
        $_SERVER['SCHEDULER_TEST_EXIT'] = $_ENV['SCHEDULER_TEST_EXIT'] = '23';
        $event = $this->fixtureEvent();
        ob_start();
        $event->run($this->app);
        $captured = ob_get_clean();
        $this->assertSame('', $captured);
        $this->assertSame(23, $event->exitCode);
    }

    public function test_natural_launch_failure_reports_failed_event_and_fail_not_done(): void
    {
        $schedule = new ShelllessSchedule;
        $event = $this->fixtureEvent([], true)->everyMinute();
        (new \ReflectionProperty(Schedule::class, 'events'))->setValue($schedule, [$event]);
        $this->app->instance(Schedule::class, $schedule);
        $observed = [];
        foreach (['Starting', 'Finished', 'Failed'] as $type) {
            $this->app['events']->listen('Illuminate\\Console\\Events\\ScheduledTask'.$type, function () use (&$observed, $type) {
                $observed[] = $type;
            });
        }
        $schedulerStatus = $this->kernel->call('schedule:run');
        $output = $this->kernel->output();
        $this->assertSame(0, $schedulerStatus);
        if ($event->exitCode === null) {
            $this->assertSame(['Starting', 'Failed'], $observed);
        } else {
            $this->assertSame(127, $event->exitCode);
            $this->assertSame(['Starting', 'Finished', 'Failed'], $observed);
        }
        $this->assertStringContainsString('FAIL', $output);
        $this->assertStringContainsString('Scheduled task failed', $output);
        $this->assertStringNotContainsString('DONE', $output);
    }

    public function test_non_due_event_does_not_execute(): void
    {
        $event = $this->fixtureEvent()->cron('0 0 1 1 *');
        \Illuminate\Support\Carbon::setTestNow('2026-09-30 12:34:00');
        try {
            $this->assertFalse($event->isDue($this->app));
        } finally {
            \Illuminate\Support\Carbon::setTestNow();
        }
    }

    public function test_unwritable_output_and_changed_command_fail_visibly(): void
    {
        $event = $this->fixtureEvent()->sendOutputTo('/no-such-scheduler-directory/output');
        try {
            $event->run($this->app);
            $this->fail('unwritable output must fail');
        } catch (\Throwable $error) {
            $this->assertStringContainsString('No such file', $error->getMessage());
        }
        $event = $this->fixtureEvent();
        $event->command .= ' changed';
        $this->expectException(\RuntimeException::class);
        $event->run($this->app);
    }
}
