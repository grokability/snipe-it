<?php

namespace Tests\Unit\Codebase;

use PHPUnit\Framework\TestCase;

/**
 * Codebase-shape guardrail for the CSV escape argument.
 *
 * PHP 8.4 deprecated relying on the implicit default for the `$escape`
 * parameter of `fputcsv()` and `fgetcsv()`:
 *
 *     Deprecated: fputcsv(): the $escape parameter must be provided as its
 *     default value will change
 *
 * The default itself did not change (it is still a single backslash), so
 * passing `escape: '\\'` explicitly is a byte-for-byte identical, zero
 * behaviour-change fix. But it has to be passed *explicitly* everywhere,
 * otherwise every CSV export and every CSV import emits a deprecation notice
 * on PHP 8.4, 8.5 and 8.6.
 *
 * This test fails if a new `fputcsv()` / `fgetcsv()` call is added without an
 * explicit `escape:` argument, so the deprecation cannot silently come back.
 */
class CsvEscapeArgumentTest extends TestCase
{
    private const SCAN_DIRS = ['app', 'tests'];
    private const CSV_FUNCTIONS = ['fputcsv', 'fgetcsv'];

    public function test_every_fputcsv_and_fgetcsv_call_passes_an_explicit_escape_argument(): void
    {
        $projectRoot = dirname(__DIR__, 3);
        $hits = [];

        foreach (self::SCAN_DIRS as $dir) {
            foreach ($this->scanPhpFiles($projectRoot.'/'.$dir) as $absolutePath) {
                $relative = ltrim(substr($absolutePath, strlen($projectRoot) + 1), '/');
                $contents = file_get_contents($absolutePath);
                if ($contents === false) continue;

                foreach ($this->findCsvCalls($contents) as $call) {
                    if ($call['has_escape']) continue;
                    $hits[] = $relative.':'.$call['line'].'  '.$call['source'];
                }
            }
        }

        $this->assertEmpty(
            $hits,
            "Found fputcsv()/fgetcsv() call(s) without an explicit \$escape argument.\n".
            "PHP 8.4 deprecated the implicit default for \$escape, so PHP 8.4+ emits a deprecation notice for every one of these calls.\n".
            "Pass it explicitly - the default is a single backslash, so this changes nothing:\n".
            "    fputcsv(\$handle, \$row, escape: '\\\\');\n\n".
            "Hits:\n".implode("\n", $hits)
        );
    }

    private function findCsvCalls(string $contents): array
    {
        $calls = [];
        $tokens = token_get_all($contents);
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];
            if (! is_array($token) || $token[0] !== T_STRING) continue;
            if (! in_array(strtolower($token[1]), self::CSV_FUNCTIONS, true)) continue;
            $next = $tokens[$i + 1] ?? null;
            if ($next !== '(') continue;
            $closeIndex = $this->findClosingParenthesis($tokens, $i + 1);
            if ($closeIndex === null) continue;

            $calls[] = [
                'line' => $token[2],
                'source' => $this->sourceLine($contents, $token[2]),
                'has_escape' => $this->hasEscapeArgument($tokens, $i + 2, $closeIndex),
            ];
        }
        return $calls;
    }

    private function findClosingParenthesis(array $tokens, int $openIndex): ?int
    {
        $depth = 0; $count = count($tokens);
        for ($i = $openIndex; $i < $count; $i++) {
            $token = $tokens[$i];
            if ($token === '(') $depth++;
            elseif ($token === ')') {
                $depth--;
                if ($depth === 0) return $i;
            }
        }
        return null;
    }

    private function hasEscapeArgument(array $tokens, int $from, int $to): bool
    {
        for ($i = $from; $i < $to; $i++) {
            $token = $tokens[$i];
            if (is_array($token) && $token[0] === T_STRING && strtolower($token[1]) === 'escape') {
                $next = $tokens[$i + 1] ?? null;
                if ($next === ':') return true;
            }
        }
        return false;
    }

    private function sourceLine(string $contents, int $line): string
    {
        $lines = explode("\n", $contents);
        $index = $line - 1;
        return isset($lines[$index]) ? trim($lines[$index]) : '';
    }

    private function scanPhpFiles(string $dir): \Generator
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                yield $file->getPathname();
            }
        }
    }
}
