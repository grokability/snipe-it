<?php

namespace Tests\Support;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\MySqlSchemaState;
use Symfony\Component\Process\ExecutableFinder;

/**
 * Preserve migration-created data as well as the final schema. Laravel's
 * schema-only dump otherwise omits baseline rows such as maintenance types.
 */
class MySqlDatabaseSnapshot extends MySqlSchemaState
{
    public static function isSupported(Connection $connection): bool
    {
        if (! in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)) {
            return false;
        }

        $finder = new ExecutableFinder;

        return $finder->find('mysql') !== null && $finder->find('mysqldump') !== null;
    }

    public function dump(Connection $connection, mixed $path): void
    {
        $this->executeDumpProcess($this->makeProcess(
            $this->baseDumpCommand().' --routines --result-file="${:LARAVEL_LOAD_PATH}"'
        ), $this->output, array_merge($this->baseVariables($connection->getConfig()), [
            'LARAVEL_LOAD_PATH' => $path,
        ]));
    }
}
