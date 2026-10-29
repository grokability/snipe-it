<?php

namespace Tests\Unit\Codebase;

use PHPUnit\Framework\TestCase;

/**
 * Codebase-shape guardrail for the CSV record terminator argument.
 *
 * PHP's `fputcsv()` defaults the row terminator to PHP_EOL, which is
 * a bare LF on Linux containers. Excel on Windows reads bare LFs
 * inside a quoted field as a record break, which splits any cell
 * carrying embedded newlines (notes, custom field descriptions,
 * composed templates) across multiple rows on open (see #19982).
 *
 * Fix: pass `eol: "\r\n"` explicitly on every call so the file is
 * RFC 4180 compliant and Excel reliably treats embedded LFs as data.
 *
 * This test fails if a new `fputcsv()` call is added without an
 * explicit `eol:` argument, so the regression cannot silently come
 * back.
 */
class CsvEolArgumentTest extends TestCase
{
    private const SCAN_DIRS = ['app'];

    public function test_every_fputcsv_call_passes_an_explicit_eol_argument(): void
    {
        $projectRoot = dirname(__DIR__, 3);
        $hits = [];

        foreach (self::SCAN_DIRS as $dir) {
            foreach ($this->scanPhpFiles($projectRoot.'/'.$dir) as $absolutePath) {
                $relative = ltrim(substr($absolutePath, strlen($projectRoot) + 1), '/');
                $contents = file_get_contents($absolutePath);
                if ($contents === false) {
                    continue;
                }

                foreach ($this->findFputcsvCalls($contents) as $call) {
                    if ($call['has_eol']) {
                        continue;
                    }
                    $hits[] = $relative.':'.$call['line'].'  '.$call['source'];
                }
            }
        }

        $this->assertEmpty(
            $hits,
            "Found fputcsv() call(s) without an explicit \$eol argument.\n".
            "Without eol: \"\\r\\n\", the row terminator defaults to PHP_EOL (bare LF on Linux),\n".
            "and Excel on Windows splits quoted cells with embedded newlines across multiple rows.\n".
            "Pass eol: \"\\r\\n\" explicitly:\n".
            "    fputcsv(\$handle, \$row, escape: '\\\\', eol: \"\\r\\n\");\n\n".
            "Hits:\n".implode("\n", $hits),
        );
    }

    private function findFputcsvCalls(string $contents): array
    {
        $calls = [];
        $tokens = token_get_all($contents);
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];
            if (! is_array($token) || $token[0] !== T_STRING) {
                continue;
            }
            if (strtolower($token[1]) !== 'fputcsv') {
                continue;
            }
            $next = $tokens[$i + 1] ?? null;
            if ($next !== '(') {
                continue;
            }
            $closeIndex = $this->findClosingParenthesis($tokens, $i + 1);
            if ($closeIndex === null) {
                continue;
            }

            $calls[] = [
                'line' => $token[2],
                'source' => $this->sourceLine($contents, $token[2]),
                'has_eol' => $this->hasEolArgument($tokens, $i + 2, $closeIndex),
            ];
        }

        return $calls;
    }

    private function findClosingParenthesis(array $tokens, int $openIndex): ?int
    {
        $depth = 0;
        $count = count($tokens);
        for ($i = $openIndex; $i < $count; $i++) {
            $token = $tokens[$i];
            if ($token === '(') {
                $depth++;
            } elseif ($token === ')') {
                $depth--;
                if ($depth === 0) {
                    return $i;
                }
            }
        }

        return null;
    }

    private function hasEolArgument(array $tokens, int $from, int $to): bool
    {
        for ($i = $from; $i < $to; $i++) {
            $token = $tokens[$i];
            if (is_array($token) && $token[0] === T_STRING && strtolower($token[1]) === 'eol') {
                $next = $tokens[$i + 1] ?? null;
                if ($next === ':') {
                    return true;
                }
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
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
        );
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                yield $file->getPathname();
            }
        }
    }
}
