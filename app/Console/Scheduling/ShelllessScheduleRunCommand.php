<?php

namespace App\Console\Scheduling;

use Exception;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\ScheduleRunCommand;
use Illuminate\Console\View\TaskResult;
use Illuminate\Support\Carbon;
use Throwable;

class ShelllessScheduleRunCommand extends ScheduleRunCommand
{
    protected function runEvent($event)
    {
        $command = $event instanceof CallbackEvent
            ? $event->getSummaryForDisplay()
            : trim(str_replace($this->phpBinary, '', $event->command));
        $description = sprintf('<fg=gray>%s</> Running [%s]%s', Carbon::now()->format('Y-m-d H:i:s'), $command, $event->runInBackground ? ' in background' : '');

        $this->components->task($description, function () use ($event, $command) {
            $this->dispatcher->dispatch(new ScheduledTaskStarting($event));
            $start = microtime(true);
            try {
                $event->run($this->laravel);
                $this->dispatcher->dispatch(new ScheduledTaskFinished($event, round(microtime(true) - $start, 2)));
                $this->eventsRan = true;
                if ($event->exitCode != 0 && ! $event->runInBackground) {
                    throw new Exception("Scheduled command [{$event->command}] failed with exit code [{$event->exitCode}].");
                }

                return $event->exitCode === 0 ? TaskResult::Success->value : TaskResult::Failure->value;
            } catch (Throwable $error) {
                $this->dispatcher->dispatch(new ScheduledTaskFailed($event, $error));
                $this->handler->report($error);
                $this->error('Scheduled task failed: '.$command.' — '.$error->getMessage());

                return TaskResult::Failure->value;
            }
        });

        if (! $event instanceof CallbackEvent) {
            $this->components->bulletList([$event->getSummaryForDisplay()]);
        }
    }
}
