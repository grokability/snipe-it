<?php

namespace App\Console\Scheduling;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\EventMutex;
use Illuminate\Log\Context\Repository;
use RuntimeException;
use Symfony\Component\Process\Process;

class ShelllessArtisanEvent extends Event
{
    private string $registeredCommand;

    public function __construct(EventMutex $mutex, $command, $timezone, private array $arguments)
    {
        parent::__construct($mutex, $command, $timezone);
        $this->registeredCommand = $command;
    }

    /** Protected seam lets isolated tests exercise natural launch failures. */
    protected function executionArguments(): array
    {
        return $this->arguments;
    }

    protected function execute($container)
    {
        if ($this->runInBackground || $this->user || $this->command !== $this->registeredCommand) {
            throw new RuntimeException('Unsupported background, user-switch, or changed scheduled Artisan command.');
        }

        $destination = fopen($this->output, $this->shouldAppendOutput ? 'ab' : 'wb');
        if ($destination === false) {
            throw new RuntimeException('Cannot open scheduled command output destination.');
        }

        $context = json_encode($container[Repository::class]->dehydrate());
        $process = new Process($this->executionArguments(), base_path(), ['__LARAVEL_CONTEXT' => $context], null, null);
        try {
            // Stream output and clear delivered buffers instead of retaining it.
            return $process->run(function ($type, $chunk) use ($destination, $process) {
                $remaining = $chunk;
                while ($remaining !== '') {
                    $written = fwrite($destination, $remaining);
                    if ($written === false || $written === 0) {
                        throw new RuntimeException('Cannot write scheduled command output.');
                    }
                    $remaining = substr($remaining, $written);
                }
                if (laravel_cloud()) {
                    fwrite(STDOUT, $chunk);
                }
                $process->clearOutput();
                $process->clearErrorOutput();
            });
        } finally {
            if ($process->isRunning()) {
                $process->stop(0);
            }
            fclose($destination);
        }
    }
}
