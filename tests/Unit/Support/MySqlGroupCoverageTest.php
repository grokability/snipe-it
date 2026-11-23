<?php

namespace Tests\Unit\Support;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionAttribute;
use ReflectionClass;

class MySqlGroupCoverageTest extends TestCase
{
    public function test_every_explicit_sqlite_exclusion_is_selected_by_the_mysql_group(): void
    {
        $root = dirname(__DIR__, 2);
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
        $checked = 0;

        foreach ($files as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), 'Test.php')) {
                continue;
            }

            $source = file_get_contents($file->getPathname());
            if (! str_contains($source, '->markIncompleteIfSqlite(')) {
                continue;
            }

            $relativePath = substr($file->getPathname(), strlen($root) + 1, -4);
            $class = new ReflectionClass('Tests\\'.str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath));
            if (! $class->isSubclassOf(\Tests\TestCase::class)) {
                continue;
            }

            $lines = file($file->getPathname());
            foreach ($class->getMethods() as $method) {
                if ($method->getDeclaringClass()->getName() !== $class->getName()) {
                    continue;
                }

                $body = implode('', array_slice($lines, $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1));
                if (! str_contains($body, '->markIncompleteIfSqlite(')) {
                    continue;
                }

                $attributes = array_merge($class->getAttributes(Group::class), $method->getAttributes(Group::class));
                $groups = array_map(fn (ReflectionAttribute $attribute): string => $attribute->getArguments()[0], $attributes);
                $this->assertContains('mysql', $groups, $class->getName().'::'.$method->getName().' must run in the MySQL leg.');
                $checked++;
            }
        }

        $this->assertGreaterThan(0, $checked);
    }
}
