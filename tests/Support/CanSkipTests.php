<?php

namespace Tests\Support;

trait CanSkipTests
{
    public function markIncompleteIfMySQL($message = 'Test skipped due to database driver being MySQL.')
    {
        if ($this->currentDatabaseDriver() === 'mysql') {
            $this->markTestIncomplete($message);
        }
    }

    public function markIncompleteIfSqlite($message = 'Test skipped due to database driver being sqlite.')
    {
        if ($this->currentDatabaseDriver() === 'sqlite') {
            $this->markTestIncomplete($message);
        }
    }

    /**
     * Resolve the driver of the current default database connection.
     * The default connection can be named anything (sqlite, sqlite_testing,
     * mysql, mysql_ci, etc.), so keying off the connection NAME misses
     * anything that isn't the canonical name. Reading the driver via the
     * connections config makes the skip helpers work regardless of what
     * .env.testing calls the connection.
     */
    private function currentDatabaseDriver(): ?string
    {
        $default = config('database.default');

        return config("database.connections.$default.driver");
    }
}
