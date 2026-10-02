#!/usr/bin/env python3
"""Patch the locked Spatie MySQL dumper to preserve structured argv without a shell."""
from pathlib import Path
import sys

path = Path(sys.argv[1])
source = path.read_text()
old_dump = '''        $process = $this->getProcess($dumpFile);

        $process->run();

        $this->checkIfDumpWasSuccessFul($process, $dumpFile);'''
new_dump = '''        $outputFile = $dumpFile;
        try {
            if ($this->appendMode) {
                $outputFile = tempnam(dirname($dumpFile), 'snipeit-dump-');
                if ($outputFile === false) {
                    throw new \\RuntimeException('Could not create a temporary dump file');
                }
            }
            $process = $this->getProcess($outputFile);
            $process->run();
            $this->checkIfDumpWasSuccessFul($process, $outputFile);
            if ($this->appendMode) {
                $input = null;
                $output = null;
                try {
                    $input = fopen($outputFile, 'rb');
                    $output = fopen($dumpFile, 'ab');
                    if ($input === false || $output === false) {
                        throw new \\RuntimeException('Could not append database dump');
                    }
                    if (stream_copy_to_stream($input, $output) === false || !fflush($output)) {
                        throw new \\RuntimeException('Could not append database dump');
                    }
                } finally {
                    if (is_resource($input)) { fclose($input); }
                    if (is_resource($output)) { fclose($output); }
                }
            }
        } finally {
            if ($this->appendMode && is_string($outputFile) && $outputFile !== $dumpFile) {
                @unlink($outputFile);
            }
            if (is_resource($tempFileHandle)) { fclose($tempFileHandle); }
            $this->setTempFileHandle(false);
        }'''
old_process = '''        $command = $this->getDumpCommand($dumpFile, $temporaryCredentialsFile);

        return Process::fromShellCommandline($command, null, null, null, $this->timeout);'''
new_process = '''        return new Process($this->getDumpArguments($dumpFile, $temporaryCredentialsFile), null, null, null, $this->timeout);'''
helpers = r'''    /** Structured properties remain data; only legacy extra-option fragments are tokenized. */
    private function getDumpArguments(string $dumpFile, string $credentialsFile): array
    {
        if ($this->compressor !== null || $this->skipAutoIncrement
            || ($this instanceof MariaDb && $this->determineSandboxMode() !== '')) {
            throw new \RuntimeException('This database dump pipeline requires a shell');
        }
        $binary = $this instanceof MariaDb ? 'mariadb-dump' : 'mysqldump';
        $arguments = [$this->dumpBinaryPath.$binary, '--defaults-extra-file='.$credentialsFile];
        if (!$this->createTables) { $arguments[] = '--no-create-info'; }
        if (!$this->includeData) { $arguments[] = '--no-data'; }
        if ($this->skipComments) { $arguments[] = '--skip-comments'; }
        $arguments[] = $this->useExtendedInserts ? '--extended-insert' : '--skip-extended-insert';
        if ($this->useSingleTransaction) { $arguments[] = '--single-transaction'; }
        if ($this->skipLockTables) { $arguments[] = '--skip-lock-tables'; }
        if ($this->doNotUseColumnStatistics) { $arguments[] = '--column-statistics=0'; }
        if ($this->useQuick) { $arguments[] = '--quick'; }
        if ($this->socket !== '') { $arguments[] = '--socket='.$this->socket; }
        foreach ($this->excludeTables as $table) { $arguments[] = '--ignore-table='.$this->dbName.'.'.$table; }
        if ($this->defaultCharacterSet !== '') { $arguments[] = '--default-character-set='.$this->defaultCharacterSet; }
        foreach ($this->extraOptions as $fragment) {
            array_push($arguments, ...$this->getLegacyOptionTokens($fragment));
        }
        if ($this->setGtidPurged !== 'AUTO') { $arguments[] = '--set-gtid-purged='.$this->setGtidPurged; }
        if (!$this->dbNameWasSetAsExtraOption) { $arguments[] = $this->dbName; }
        if ($this->includeTables !== []) {
            $arguments[] = '--tables';
            foreach ($this->includeTables as $table) { $arguments[] = (string) $table; }
        }
        foreach ($this->extraOptionsAfterDbName as $fragment) {
            array_push($arguments, ...$this->getLegacyOptionTokens($fragment));
        }
        $arguments[] = '--result-file='.$dumpFile;
        foreach ($arguments as $argument) {
            if (str_contains($argument, "\0")) { throw new \InvalidArgumentException('NUL in dump argument'); }
        }
        return $arguments;
    }

    private function getLegacyOptionTokens(string $fragment): array
    {
        // Validate complete quote/escape groups; StringInput remains the sole
        // tokenizer. No shell syntax, substitution or expansion is evaluated.
        $pattern = '~\A(?:[^\x22\x27\x5c]|\x5c.|QUOTED)*\z~s';
        $pattern = str_replace('QUOTED', \Symfony\Component\Console\Input\StringInput::REGEX_QUOTED_STRING, $pattern);
        if (preg_match($pattern, $fragment) !== 1) {
            throw new \InvalidArgumentException('Unclosed quote or escape in legacy dump option');
        }
        return (new \Symfony\Component\Console\Input\StringInput($fragment))->getRawTokens();
    }

'''
marker = '''    /**
     * @return false|resource
     */
    public function getTempFileHandle'''
if source.count(old_dump) != 1 or source.count(old_process) != 1 or source.count(marker) != 1:
    raise SystemExit('Installed Spatie dumper does not match the audited version')
path.write_text(source.replace(old_dump, new_dump).replace(old_process, new_process).replace(marker, helpers + marker))
