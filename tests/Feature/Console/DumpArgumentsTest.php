<?php

namespace Tests\Feature\Console;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/** Exercise the patched installed vendor class with actual fake-client children. */
class DumpArgumentsTest extends TestCase
{
    public static function dumpCases(): array
    {
        return array_map(static fn ($case) => [$case], ['exact', 'structured', 'legacy', 'failure', 'append', 'exclusions']);
    }

    #[DataProvider('dumpCases')]
    public function test_dump_child_behavior(string $case): void
    {
        $probe = getenv('DUMP_ARGV_PROBE') ?: dirname(__DIR__, 4).'/verification/dump-argv/probe.php';
        $this->assertFileExists($probe, 'Run with the repository verification fixture or set DUMP_ARGV_PROBE.');
        $process = new Process([PHP_BINARY, $probe, $case]);
        $process->run();
        $this->assertSame(0, $process->getExitCode(), $process->getOutput().$process->getErrorOutput());
        $this->assertStringContainsString('PASS '.$case, $process->getOutput());
    }
}
