<?php

namespace App\Console\Scheduling;

use Illuminate\Console\Application;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Container\Container;
use Symfony\Component\Console\Command\Command;

class ShelllessSchedule extends Schedule
{
    public function command($command, array $parameters = [])
    {
        $description = null;
        if ($command instanceof Command || class_exists($command)) {
            $instance = Container::getInstance()->make(is_object($command) ? get_class($command) : $command);
            $command = $instance->getName();
            $description = $instance->getDescription();
        }

        $display = Application::formatCommandString($command);
        if (count($parameters)) {
            $display .= ' '.$this->compileParameters($parameters);
        }

        $this->events[] = $event = new ShelllessArtisanEvent(
            $this->eventMutex, $display, $this->timezone,
            ArtisanCommandArguments::compile($command, $parameters),
        );
        $this->mergePendingAttributes($event);
        if ($description !== null) {
            $event->description($description);
        }

        return $event;
    }
}
