<?php

namespace App\Console\Commands;

use App\Enums\FileStorage;
use App\Services\RemoteUploadsStager;
use enshrined\svgSanitize\Sanitizer;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;
use ZipArchive;

class SQLStreamer
{
    private $input;

    private $output;

    // embed the prefix here?
    public ?string $prefix;

    private bool $reading_beginning_of_line = true;

    public static $buffer_size = 1024 * 1024;  // use a 1MB buffer, ought to work fine for most cases?

    public array $tablenames = [];

    private bool $should_guess = false;

    private bool $statement_is_permitted = false;

    public function __construct($input, $output, ?string $prefix = null)
    {
        $this->input = $input;
        $this->output = $output;
        $this->prefix = $prefix;
    }

    public function parse_sql(string $line): string
    {
        // take into account the 'start of line or not' setting as an instance variable?
        // 'continuation' lines for a permitted statement are PERMITTED.
        // remove *only* line-feeds & carriage-returns; helpful for regexes against lines from
        // Windows dumps
        $line = trim($line, "\r\n");
        if ($this->statement_is_permitted && $line[0] === ' ') {
            return $line."\n"; // re-add the newline
        }

        $table_regex = '`?([a-zA-Z0-9_]+)`?';
        $allowed_statements = [
            "/^(DROP TABLE (?:IF EXISTS )?)`$table_regex(.*)$/" => false,
            "/^(CREATE TABLE )$table_regex(.*)$/" => true, // sets up 'continuation'
            "/^(LOCK TABLES )$table_regex(.*)$/" => false,
            "/^(INSERT INTO )$table_regex(.*)$/" => false,
            '/^UNLOCK TABLES/' => false,
            // "/^\\) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;/" => false, // FIXME not sure what to do here?
            '/^\\)[a-zA-Z0-9_= ]*;$/' => false,
            // ^^^^^^ that bit should *exit* the 'permitted' block
            '/^\\(.*\\)[,;]$/' => false, // older MySQL dump style with one set of values per line
            /* we *could* have made the ^INSERT INTO blah VALUES$ turn on the capturing state, and closed it with
               a ^(blahblah);$ but it's cleaner to not have to manage the state machine. We're just going to
               assume that (blahblah), or (blahblah); are values for INSERT and are always acceptable. */
            "<^/\*![0-9]{5} SET NAMES '?[a-zA-Z0-9_-]+'? \*/;$>" => false, // using weird delimiters (<,>) for readability. allow quoted or unquoted charsets
            "<^/\*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' \*/;$>" => false, // same, now handle zero-values
        ];

        foreach ($allowed_statements as $statement => $statechange) {
            //            $this->info("Checking regex: $statement...\n");
            $matches = [];
            if (preg_match($statement, $line, $matches)) {
                $this->statement_is_permitted = $statechange;
                // matches are: 1 => first part of the statement, 2 => tablename, 3 => rest of statement
                // (with of course 0 being "the whole match")
                if (@$matches[2]) {
                    //                    print "Found a tablename! It's: ".$matches[2]."\n";
                    if ($this->should_guess) {
                        @$this->tablenames[$matches[2]] += 1;

                        continue; // oh? FIXME
                    } else {
                        $cleaned_tablename = \DB::getTablePrefix().preg_replace('/^'.$this->prefix.'/', '', $matches[2]);
                        $line = preg_replace($statement, '$1`'.$cleaned_tablename.'`$3', $line);
                    }
                } else {
                    // no explicit tablename in this one, leave the line alone
                }

                // how do we *replace* the tablename?
                //                print "RETURNING LINE: $line";
                return $line."\n"; // re-add newline
            }
        }

        // all that is not allowed is denied.
        return '';
    }

    // Used in exactly *TWO* places. Both return a prefix:
    //   1. --sanitize-only (mostly for testing/development).
    //   2. When running *without* a guessed prefix, this runs first to figure out the prefix.
    public static function guess_prefix($input): string
    {
        $parser = new self($input, null);
        $parser->should_guess = true;
        $parser->line_aware_piping(); // <----- THIS is doing the heavy lifting!

        $check_tables = ['settings' => null, 'migrations' => null /* 'assets' => null */]; // TODO - move to statics?
        // can't use 'users' because the 'accessories_checkout' table?
        // can't use 'assets' because 'ver1_components_assets'
        foreach ($check_tables as $check_table => $_ignore) {
            foreach ($parser->tablenames as $tablename => $_count) {
                //                print "Comparing $tablename to $check_table\n";
                if (str_ends_with($tablename, $check_table)) {
                    //                    print "Found one!\n";
                    $check_tables[$check_table] = substr($tablename, 0, -strlen($check_table));
                }
            }
        }
        $guessed_prefix = null;
        foreach ($check_tables as $clean_table => $prefix_guess) {
            if (is_null($prefix_guess)) {
                echo "Couldn't find table $clean_table\n";
                exit();
            }
            if (is_null($guessed_prefix)) {
                $guessed_prefix = $prefix_guess;
            } else {
                if ($guessed_prefix != $prefix_guess) {
                    echo "Prefix mismatch! Had guessed $guessed_prefix but got $prefix_guess\n";
                    exit();
                }
            }
        }

        return $guessed_prefix;

    }

    public function line_aware_piping(): int
    {
        $bytes_read = 0;
        if (! $this->input) {
            throw new Exception('No Input available for line_aware_piping');
        }

        while (($buffer = fgets($this->input, SQLStreamer::$buffer_size)) !== false) {
            $bytes_read += strlen($buffer);
            if ($this->reading_beginning_of_line) {
                // Log::debug("Buffer is: '$buffer'");
                $cleaned_buffer = $this->parse_sql($buffer);
                if ($this->output) {
                    $bytes_written = fwrite($this->output, $cleaned_buffer);

                    if ($bytes_written === false) {
                        throw new Exception('Unable to write to pipe');
                    }
                }
            }
            // if we got a newline at the end of this, then the _next_ read is the beginning of a line
            if ($buffer[strlen($buffer) - 1] === "\n") {
                $this->reading_beginning_of_line = true;
            } else {
                $this->reading_beginning_of_line = false;
            }

        }

        return $bytes_read;

    }
}

class RestoreFromBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:restore
                                            {--force : Skip the danger prompt; assuming you enter "y"} 
                                            {filename : The zip file to be migrated}
                                            {--no-progress : Don\'t show a progress bar}
                                            {--sanitize-guess-prefix : Guess and output the table-prefix needed to "sanitize" the SQL}
                                            {--sanitize-with-prefix= : "Sanitize" the SQL, using the passed-in table prefix (can be learned from --sanitize-guess-prefix). Pass as just \'--sanitize-with-prefix=\' to use no prefix}
                                            {--sql-stdout-only : ONLY "Sanitize" the SQL and print it to stdout - useful for debugging - probably requires --sanitize-with-prefix= }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore from a previously created Snipe-IT backup file';

    /**
     * File-path patterns under public/uploads that the restore should extract
     * back to disk. Wildcard entries end in `*`; the classifier trims the
     * wildcard and matches via strrpos. The Setting- and setting- entries
     * catch the branding filename shape produced by ImageUploadRequest
     * (Setting-<field><id>-<random>.<ext>). Both cases are listed because
     * the classifier is case-sensitive and either case can appear depending
     * on when the backup was created and which filesystem it came from.
     * The default_avatar file lives in avatars/ and is caught by the
     * public_dirs list, not here.
     */
    public const PUBLIC_FILES = [
        'public/uploads/Setting-*',
        'public/uploads/setting-*',
        'public/uploads/logo.*',
        'public/uploads/favicon.*',
        'public/uploads/favicon-uploaded.*',
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Pick the DB client binary to invoke for a given driver. Prefers the
     * driver-native name (mariadb for the mariadb driver, mysql otherwise),
     * falls back to the other name if the preferred binary is missing.
     * Returns null if neither exists in the given path.
     */
    public static function pickDbClientBinary(string $driver, string $binaryPath): ?string
    {
        $ext = \DIRECTORY_SEPARATOR === '\\' ? '.exe' : '';
        $preferred = $driver === 'mariadb' ? 'mariadb' : 'mysql';
        $fallback = $preferred === 'mariadb' ? 'mysql' : 'mariadb';

        foreach ([$preferred, $fallback] as $name) {
            $candidate = rtrim($binaryPath, \DIRECTORY_SEPARATOR).\DIRECTORY_SEPARATOR.$name.$ext;
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $dir = getcwd();
        if ($dir != base_path()) { // usually only the case when running via webserver, not via command-line
            Log::debug("Current working directory is: $dir, changing directory to: ".base_path());
            chdir(base_path()); // TODO - is this *safe* to change on a running script?!
        }
        //
        $filename = $this->argument('filename');

        if (! $filename) {
            $this->error('Missing required filename');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->option('sanitize-guess-prefix') && ! $this->confirm('Are you sure you wish to restore from the given backup file? This can lead to MASSIVE DATA LOSS!')) {
            $this->error('Data loss not confirmed');

            return self::FAILURE;
        }

        $connectionName = config('database.default');
        $connectionConfig = config("database.connections.$connectionName");
        $driver = $connectionConfig['driver'] ?? null;

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            $this->error('DB_CONNECTION must be MySQL or MariaDB in order to perform a restore. Detected driver: '.($driver ?? 'unknown')." (connection: $connectionName)");

            return self::FAILURE;
        }

        $za = new ZipArchive;

        $errcode = $za->open($filename/* , ZipArchive::RDONLY */); // that constant only exists in PHP 7.4 and higher
        if ($errcode !== true) {
            $error_msg = match ($errcode) {
                ZipArchive::ER_EXISTS => 'File already exists.',
                ZipArchive::ER_INCONS => 'Zip archive inconsistent.',
                ZipArchive::ER_INVAL => 'Invalid argument.',
                ZipArchive::ER_MEMORY => 'Malloc failure.',
                ZipArchive::ER_NOENT => 'No such file ('.$filename.') in directory '.$dir.'.',
                ZipArchive::ER_NOZIP => 'Not a zip archive.',
                ZipArchive::ER_OPEN => "Can't open file.",
                ZipArchive::ER_READ => 'Read error.',
                ZipArchive::ER_SEEK => 'Seek error.',
                default => "Unknown reason: $errcode",
            };

            $this->error('Could not access file: '.$filename.' - '.$error_msg);

            return self::FAILURE;
        }

        // Upload directory enumeration lives in App\Enums\FileStorage
        // as the single source of truth. The *ForRestore variants omit
        // directories in FileStorage::SKIP_IN_RESTORE_PRUNE, so Spatie's
        // backup archive directory (which may contain the archive we're
        // currently reading from, plus the user's other rollback points)
        // is left untouched by the pruning passes below.
        $private_dirs = FileStorage::privateDirsForRestore();
        $private_files = [
            'storage/oauth-private.key',
            'storage/oauth-public.key',
        ];
        $public_dirs = FileStorage::publicDirsForRestore();

        $public_files = self::PUBLIC_FILES;

        $sqlfiles = [];
        $sqlfile_indices = [];

        $interesting_files = [];
        $boring_files = [];
        $unsafe_files = [];

        $good_extensions = config('filesystems.allowed_upload_extensions_array');

        $private_extensions = array_merge($good_extensions, ['csv', 'key']); // add csv, and 'key'
        $public_extensions = array_diff($good_extensions, ['xml']); // remove xml

        $sanitizer = new Sanitizer;

        /**
         * TODO: I _hate_ the "continue 3" thing we keep doing here
         * I think a better approach might be to have the "each file" stuff be in a method on this class, and the
         * boring_files and interesting_files be properties on it that we fill out. Then, in that method, we could
         * just do a 'return' once the file is actually handled (yay or nay). We could also start to break out some of
         * the _other_ things that we do into their own methods too? But I don't care about that as much.
         */
        $manifest_indices = [];
        $unsafe_paths = [];
        $logical_names = []; // name inside the archive, without the staging directory => zip index

        for ($i = 0; $i < $za->numFiles; $i++) {
            $stat_results = $za->statIndex($i);

            $raw_path = $stat_results['name'];
            if (strpos($raw_path, '\\') !== false) { // found a backslash, swap it to forward-slash
                $raw_path = strtr($raw_path, '\\', '/');
            }

            // skip macOS resource fork files (?!?!?!)
            if (strpos($raw_path, '__MACOSX') !== false && strpos($raw_path, '._') !== false) {
                continue;
            }
            if (self::isUnsafeArchivePath($raw_path)) {
                $unsafe_paths[] = $raw_path;

                continue;
            }
            if (basename($raw_path) === RemoteUploadsStager::MANIFEST) {
                $manifest_indices[] = $i;

                continue;
            }
            if (@pathinfo($raw_path, PATHINFO_EXTENSION) == 'sql') {
                Log::debug('Found a sql file!');
                $sqlfiles[] = $raw_path;
                $sqlfile_indices[] = $i;

                continue;
            }
            if ($raw_path[-1] == '/') {
                // last character is '/' - this is a directory, and we don't need it, and we don't need to warn about it
                continue;
            }
            if (in_array(basename($raw_path), ['.gitkeep', '.gitignore', '.DS_Store'])) {
                // skip these boring files silently without reporting on them; they're stupid
                continue;
            }
            $extension = strtolower(pathinfo($raw_path, PATHINFO_EXTENSION));

            // Copies of the S3 files sit in storage/framework/backup-staging/<id>/ inside the archive.
            // They are classified by the path they have inside that directory.
            $logical = self::stripStagingPrefix($raw_path);
            $staged = $logical !== $raw_path;
            $logical_names[$logical] = $i;
            $class_path = $staged ? $logical : $raw_path;

            foreach (['public' => $public_dirs, 'private' => $private_dirs] as $purpose => $dirs) {
                $allowed_extensions = match ($purpose) {
                    'public' => $public_extensions,
                    'private' => $private_extensions,
                };
                foreach ($dirs as $dir => $destdir) {
                    if (is_int($dir)) {
                        $dir = $destdir;
                    }
                    if ($staged) {
                        $found = str_starts_with($logical, $dir.'/');
                        $relative = $found ? substr($logical, strlen($dir) + 1) : null;
                    } else {
                        $last_pos = strrpos($raw_path, $dir.'/');
                        $found = $last_pos !== false;
                        $relative = $found ? substr($raw_path, $last_pos + strlen($dir) + 1) : null;
                    }
                    if ($found) {
                        // the CSV bit, below, is because we store CSV files as "blahcsv" - without an extension
                        if (! in_array($extension, $allowed_extensions) && ! ($dir == FileStorage::Imports->privateDir() && substr($raw_path, -3) == 'csv' && $extension == '')) {
                            $unsafe_files[] = $raw_path;
                            Log::debug($raw_path.' from directory '.$dir.' is being skipped');
                        } else {
                            if ($dir != $destdir) {
                                Log::debug("Getting ready to save file $raw_path to new directory $destdir");
                            }
                            // The path below the directory is kept, so a/x.pdf and b/x.pdf stay two files.
                            $interesting_files[$raw_path] = ['dest' => $destdir, 'index' => $i, 'rel' => $relative, 'role' => $purpose];
                        }

                        continue 3;
                    }
                }
            }

            foreach (['public' => $public_files, 'private' => $private_files] as $purpose => $files) {
                $allowed_extensions = match ($purpose) {
                    'public' => $public_extensions,
                    'private' => $private_extensions,
                };
                foreach ($files as $file) {
                    $has_wildcard = (strpos($file, '*') !== false);
                    if ($has_wildcard) {
                        $file = substr($file, 0, -1); // trim last character (which should be the wildcard)
                    }
                    $last_pos = strrpos($class_path, $file); // no trailing slash!
                    if ($last_pos !== false) {
                        if (! in_array($extension, $allowed_extensions)) {
                            // gathering potentially unsafe files here to return at exit
                            $unsafe_files[] = $raw_path;
                            Log::debug('Potentially unsafe file '.$raw_path.' is being skipped');
                            $boring_files[] = $raw_path;

                            continue 3;
                        }
                        if ($last_pos + strlen($file) == strlen($class_path) || $has_wildcard) { // again, no trailing slash. or this is a wildcard and we just take it.
                            // The OAuth keys always stay on the local disk; only the public branding files follow the public disk.
                            $interesting_files[$raw_path] = ['dest' => dirname($file), 'index' => $i, 'rel' => basename($raw_path), 'role' => $purpose === 'public' ? 'public' : 'local'];

                            continue 3;
                        }
                    }
                }
            }
            $boring_files[] = $raw_path; // if we've gotten to here and haven't continue'ed our way into the next iteration, we don't want this file
        } // end of pre-processing the ZIP file for-loop
        // print_r($interesting_files);exit(-1);

        if (count($sqlfiles) != 1) {
            $this->error('There should be exactly *one* sql backup file found, found: '.(count($sqlfiles) == 0 ? 'None' : implode(', ', $sqlfiles)));

            return self::FAILURE;
        }

        if (strpos($sqlfiles[0], 'db-dumps') === false) {
            // return $this->error("SQL backup file is missing 'db-dumps' component of full pathname: ".$sqlfiles[0]);
            // older Snipe-IT installs don't have the db-dumps subdirectory component
            $this->warn("Did not find the 'db-dumps' directory - is this really a Snipe-IT backup file? Continuing anyways...");
        }

        $sql_stat = $za->statIndex($sqlfile_indices[0]);
        // $this->info("SQL Stat is: ".print_r($sql_stat,true));
        $sql_contents = $za->getStream($sql_stat['name']);

        if ($sql_contents === false) {
            $this->error('Unable to open SQL file: '.$sql_stat['name']);

            return -1;
        }

        // Now that we *found* the sql file, if we're doing just the guess-prefix thing we can do that *HERE*.
        if ($this->option('sanitize-guess-prefix')) {
            $prefix = SQLStreamer::guess_prefix($sql_contents);
            $this->line($prefix);

            return $this->info("Re-run this command with '--sanitize-with-prefix=".$prefix."' to see an attempt to sanitize your SQL.");
        }

        // If we're doing --sql-stdout-only, handle that now so we don't have to open pipes to mysql and all of that silliness
        if ($this->option('sql-stdout-only')) {
            $sql_importer = new SQLStreamer($sql_contents, STDOUT, $this->option('sanitize-with-prefix'));
            $bytes_read = $sql_importer->line_aware_piping();

            // STDERR so stdout stays pure SQL for `--sql-stdout-only >
            // out.sql` redirection and the operator does not have to
            // trim the trailing status line off the dump.
            $this->getOutput()->getErrorStyle()->writeln('<comment>'.$bytes_read.' total bytes read</comment>');

            return 0;
        }

        // Everything below changes the install, so the archive is checked completely first.
        $problems = [];
        $manifest = $this->readManifest($za, $manifest_indices, $problems);
        $expected_sha = $this->verifyArchive($za, $manifest, $logical_names, $unsafe_paths, $problems);
        if (count($problems) > 0) {
            foreach ($problems as $problem) {
                $this->error($problem);
            }
            $this->error('The backup file did not pass validation. Nothing was changed.');

            return self::FAILURE;
        }
        $this->announceManifest($manifest);

        // how to invoke the restore?
        $pipes = [];

        $env_vars = getenv();
        $env_vars['MYSQL_PWD'] = $connectionConfig['password'];

        $binaryPath = $connectionConfig['dump']['dump_binary_path'] ?? '';
        $client_binary = static::pickDbClientBinary($driver, $binaryPath);
        if ($client_binary === null) {
            $preferred = $driver === 'mariadb' ? 'mariadb' : 'mysql';

            $this->error("DB client binary '$preferred' not found in DB_DUMP_PATH ('$binaryPath'). Please edit DB_DUMP_PATH in your .env to point to a directory that contains the mysql/mariadb client binary.");

            return self::FAILURE;
        }

        $client_command = [
            $client_binary,
            '-h',
            (string) $connectionConfig['host'],
            '--batch',
            '--binary-mode',
            '-u',
            (string) $connectionConfig['username'],
        ];

        if (empty($connectionConfig['unix_socket'])) {
            $client_command[] = '-P';
            $client_command[] = (string) $connectionConfig['port'];
        } else {
            $client_command[] = '-S';
            $client_command[] = (string) $connectionConfig['unix_socket'];
        }

        // Passing argv directly avoids requiring /bin/sh and keeps each
        // connection value a single argument. The password stays in the
        // environment rather than appearing in the process list.
        $client_command[] = (string) $connectionConfig['database'];

        $proc_results = proc_open($client_command, // yanked -p since we pass via ENV
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            null,
            $env_vars); // this is not super-duper awesome-secure, but definitely more secure than showing it on the CLI, or dropping temporary files with passwords in them.
        if ($proc_results === false) {
            $this->error('Unable to invoke DB client via CLI');

            return self::FAILURE;
        }

        try {
            if ($this->option('sanitize-with-prefix') === null) {
                // "Legacy" direct-piping
                $bytes_read = 0;
                while (($buffer = fgets($sql_contents, SQLStreamer::$buffer_size)) !== false) {
                    $bytes_read += strlen($buffer);
                    // Log::debug("Buffer is: '$buffer'");
                    $bytes_written = fwrite($pipes[0], $buffer);

                    if ($bytes_written === false) {
                        throw new Exception('Unable to write to pipe');
                    }
                }
            } else {
                $sql_importer = new SQLStreamer($sql_contents, $pipes[0], $this->option('sanitize-with-prefix'));
                $bytes_read = $sql_importer->line_aware_piping();
            }
        } catch (\Exception $e) {
            Log::error('Error during restore!!!! '.$e->getMessage());
            // Drain both pipes fully so the DB client's own diagnostic ends up in
            // the log instead of just the downstream "broken pipe". Then include
            // the process exit code so the cause is visible without strace.
            // The deprecation warning about maria-db is a red herring, and not the actual problem.
            // "Deprecated program name. It will be removed in a future release, use '/usr/bin/mariadb' instead"
            // was drowning out the actual problem.
            stream_set_blocking($pipes[1], false);
            stream_set_blocking($pipes[2], false);
            $err_out = stream_get_contents($pipes[1]) ?: '';
            $err_err = stream_get_contents($pipes[2]) ?: '';
            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $exit_code = proc_close($proc_results);
            Log::error('Error OUTPUT: '.$err_out);
            $this->info($err_out);
            Log::error('Error ERROR : '.$err_err);
            $this->error($err_err);
            Log::error("DB client exited with code $exit_code");
            $this->error("DB client exited with code $exit_code");
            throw $e;
        }
        if (! feof($sql_contents) || $bytes_read == 0) {
            $this->error('Not at end of file for sql file, or zero bytes read. aborting!');

            return -1;
        }

        fclose($pipes[0]);
        fclose($sql_contents);

        $this->line(stream_get_contents($pipes[1]));
        fclose($pipes[1]);

        $this->error(stream_get_contents($pipes[2]));
        fclose($pipes[2]);

        $close_results = proc_close($proc_results);
        if ($close_results != 0) {
            $this->error('There may have been a problem with the database import: Error number '.$close_results);

            return $close_results;
        }

        // Prune the local upload directories before extracting the backup's files on top. A restore
        // replaces the entire install (the DB was wiped before this command ran), so any image or file
        // that the backup does not carry should not survive the restore. Directories on a non-local disk
        // are never pruned or deleted from, and a backup that left the S3 files out has nothing to
        // replace them with.
        $roles_present = array_unique(array_column($interesting_files, 'role'));
        $excluded = ($manifest['remote_files'] ?? null) === 'excluded';
        $prune_public = ! $this->diskIsRemote('public') && (! $excluded || in_array('public', $roles_present, true));
        $prune_private = ! $this->diskIsRemote(config('filesystems.default')) && (! $excluded || in_array('private', $roles_present, true));
        if ($prune_public || $prune_private) {
            self::pruneUploadDirectories($prune_public ? $public_dirs : [], $prune_private ? $private_dirs : []);
        }
        if ($prune_public) {
            self::pruneUploadFiles(self::PUBLIC_FILES);
        }

        // and now copy the files over too (right?)
        if ($this->option('no-progress')) {
            $bar = null;
        } else {
            $bar = $this->output->createProgressBar(count($interesting_files));
        }
        $failures = [];
        foreach ($interesting_files as $pretty_file_name => $file_details) {
            try {
                $this->restoreFile($za, $file_details, $expected_sha[$file_details['index']] ?? null, $sanitizer);
            } catch (Throwable $e) {
                Log::error('Restore of '.$pretty_file_name.' failed: '.$e->getMessage());
                $failures[] = $e->getMessage();
            }
            if ($bar) {
                $bar->advance();
            }
        }
        if ($bar) {
            $bar->finish();
            $this->line('');
        } else {
            $this->info((count($interesting_files) - count($failures)).' files were succesfully transferred');
        }
        if (count($unsafe_files) > 0) {
            foreach ($unsafe_files as $unsafe_file) {
                $this->warn('Potentially unsafe file '.$unsafe_file.' was skipped');
            }
        }
        foreach ($boring_files as $boring_file) {
            $this->warn($boring_file.' was skipped.');
        }

        if (count($failures) > 0) {
            foreach ($failures as $failure) {
                $this->error($failure);
            }
            // The database was already replaced, so the install is half restored. Keep users out until someone has fixed it.
            try {
                $this->callSilently('down');
            } catch (Throwable $e) {
                Log::error('Could not switch to maintenance mode: '.$e->getMessage());
            }
            $this->error('The restore needs manual recovery: the database was restored but '.count($failures).' upload file(s) were not (see above). The application is in maintenance mode. Fix the cause, then restore again or copy the files by hand, and run "php artisan up".');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function diskIsRemote(string $disk): bool
    {
        return config("filesystems.disks.$disk.driver") !== 'local';
    }

    private static function isUnsafeArchivePath(string $path): bool
    {
        return str_contains($path, "\0") || in_array('..', explode('/', $path), true);
    }

    private static function stripStagingPrefix(string $path): string
    {
        return preg_match('#^(?:.*/)?storage/framework/backup-staging/[^/]+/(.+)$#', $path, $m) ? $m[1] : $path;
    }

    /**
     * @param  array<int, int>  $indices
     * @param  array<int, string>  $problems
     */
    private function readManifest(ZipArchive $za, array $indices, array &$problems): ?array
    {
        if (count($indices) === 0) {
            return null;
        }
        if (count($indices) > 1) {
            $problems[] = 'The archive contains more than one '.RemoteUploadsStager::MANIFEST.'.';

            return null;
        }

        $manifest = json_decode((string) $za->getFromIndex($indices[0]), true);
        if (! is_array($manifest)
            || ($manifest['version'] ?? null) !== 1
            || ! in_array($manifest['remote_files'] ?? null, ['included', 'excluded'], true)
            || ! is_array($manifest['files'] ?? null)) {
            $problems[] = RemoteUploadsStager::MANIFEST.' is not a readable version 1 manifest.';

            return null;
        }

        return $manifest;
    }

    /**
     * Checks the archive against its manifest without writing anything: no unsafe paths, every listed
     * file present with the listed size and SHA-256.
     *
     * @param  array<string, int>  $logical_names
     * @param  array<int, string>  $unsafe_paths
     * @param  array<int, string>  $problems
     * @return array<int, string> zip index => expected sha256
     */
    private function verifyArchive(ZipArchive $za, ?array $manifest, array $logical_names, array $unsafe_paths, array &$problems): array
    {
        foreach ($unsafe_paths as $path) {
            $problems[] = 'The archive contains an unsafe path: '.$path;
        }

        $expected = [];
        foreach ($manifest['files'] ?? [] as $entry) {
            $path = is_array($entry) ? ($entry['archive_path'] ?? null) : null;
            $bytes = is_array($entry) ? ($entry['bytes'] ?? null) : null;
            $sha = is_array($entry) ? ($entry['sha256'] ?? null) : null;
            if (! is_string($path) || ! is_int($bytes) || ! is_string($sha) || ! preg_match('/^[0-9a-f]{64}$/', $sha) || self::isUnsafeArchivePath($path)) {
                $problems[] = 'The manifest has an invalid entry: '.(is_string($path) ? $path : '(no path)');

                continue;
            }
            if (! isset($logical_names[$path])) {
                $problems[] = 'Listed in the manifest but missing from the archive: '.$path;

                continue;
            }

            $index = $logical_names[$path];
            $size = $za->statIndex($index)['size'] ?? null;
            if ($size !== $bytes) {
                $problems[] = "Size of $path differs from the manifest (archive: $size, manifest: $bytes).";

                continue;
            }
            if ($this->entrySha256($za, $index) !== $sha) {
                $problems[] = "SHA-256 of $path differs from the manifest.";

                continue;
            }
            $expected[$index] = $sha;
        }

        return $expected;
    }

    private function announceManifest(?array $manifest): void
    {
        $remote_in_use = $this->diskIsRemote('public') || $this->diskIsRemote(config('filesystems.default'));

        if (($manifest['remote_files'] ?? null) === 'excluded') {
            $this->warn('S3 files are not part of this backup. The files on the S3 disks are left as they are.');
        } elseif ($manifest === null && $remote_in_use) {
            $this->warn('This backup has no manifest, so its upload files cannot be checked against one. They are written to the configured disks as they are.');
        }
    }

    private function entrySha256(ZipArchive $za, int $index): ?string
    {
        $stream = $za->getStream((string) $za->getNameIndex($index));
        if ($stream === false) {
            return null;
        }

        $context = hash_init('sha256');
        while (! feof($stream)) {
            $chunk = fread($stream, SQLStreamer::$buffer_size);
            if ($chunk === false) {
                fclose($stream);

                return null;
            }
            hash_update($context, $chunk);
        }
        fclose($stream);

        return hash_final($context);
    }

    /**
     * Writes one archive entry to the local directory or the remote disk of its role, and for a remote
     * disk reads it back to compare size and SHA-256.
     *
     * @param  array{dest: string, index: int, rel: string, role: string}  $details
     *
     * @throws RuntimeException naming the file that failed
     */
    private function restoreFile(ZipArchive $za, array $details, ?string $expectedSha, Sanitizer $sanitizer): void
    {
        $name = (string) $za->getNameIndex($details['index']);
        $isSvg = strcasecmp(substr($name, -4), '.svg') === 0;
        $remoteDisk = match (true) {
            $details['role'] === 'public' && $this->diskIsRemote('public') => 'public',
            $details['role'] === 'private' && $this->diskIsRemote(config('filesystems.default')) => config('filesystems.default'),
            default => null,
        };

        if ($remoteDisk !== null) {
            $base = $details['role'] === 'public' ? 'public/uploads' : 'storage';
            $key = ltrim(substr($details['dest'], strlen($base)).'/'.$details['rel'], '/');
            $this->writeRemote($za, $details['index'], $remoteDisk, $key, $isSvg ? $sanitizer : null, $expectedSha);

            return;
        }

        $destination = $details['dest'].'/'.$details['rel'];
        if (! is_dir(dirname($destination)) && ! @mkdir(dirname($destination), 0755, true) && ! is_dir(dirname($destination))) {
            throw new RuntimeException('Could not create the directory for '.$destination);
        }

        if ($isSvg) {
            $cleaned = (string) $sanitizer->sanitize((string) $za->getFromIndex($details['index']));
            if (@file_put_contents($destination, $cleaned) !== strlen($cleaned)) {
                throw new RuntimeException('Could not write '.$destination);
            }

            return;
        }

        $source = $za->getStream($name);
        $target = $source === false ? false : @fopen($destination, 'w');
        if ($source === false || $target === false) {
            throw new RuntimeException('Could not open '.$destination.' for the restore');
        }
        $written = stream_copy_to_stream($source, $target);
        $flushed = fflush($target);
        fclose($target);
        fclose($source);
        if ($written !== ($za->statIndex($details['index'])['size'] ?? null) || ! $flushed) {
            throw new RuntimeException('Could not write all of '.$destination.' (disk full?)');
        }
    }

    private function writeRemote(ZipArchive $za, int $index, string $diskName, string $key, ?Sanitizer $sanitizer, ?string $expectedSha): void
    {
        $label = $diskName.':'.$key;
        $disk = Storage::disk($diskName);
        $temp = fopen('php://temp/maxmemory:4194304', 'w+b'); // seekable for the S3 client, spills to disk when large

        try {
            if ($sanitizer !== null) {
                $content = (string) $sanitizer->sanitize((string) $za->getFromIndex($index));
                fwrite($temp, $content);
                $size = strlen($content);
                $sha = hash('sha256', $content);
            } else {
                $source = $za->getStream((string) $za->getNameIndex($index));
                if ($source === false) {
                    throw new RuntimeException('Could not read '.$key.' from the archive');
                }
                $context = hash_init('sha256');
                $size = 0;
                while (! feof($source)) {
                    $chunk = fread($source, SQLStreamer::$buffer_size);
                    if ($chunk === false) {
                        fclose($source);
                        throw new RuntimeException('Could not read '.$key.' from the archive');
                    }
                    hash_update($context, $chunk);
                    $size += strlen($chunk);
                    fwrite($temp, $chunk);
                }
                fclose($source);
                $sha = hash_final($context);
                if ($expectedSha !== null && $sha !== $expectedSha) {
                    throw new RuntimeException('Archive content of '.$key.' changed while it was read');
                }
            }

            rewind($temp);
            try {
                $written = $disk->writeStream($key, $temp);
            } catch (Throwable $e) {
                throw new RuntimeException("Could not write $label: ".$e->getMessage(), 0, $e);
            }
            if ($written === false) {
                throw new RuntimeException("Could not write $label");
            }

            try {
                $stored = $disk->size($key);
                $readBack = $disk->readStream($key);
            } catch (Throwable $e) {
                throw new RuntimeException("Could not read back $label: ".$e->getMessage(), 0, $e);
            }
            if (! is_resource($readBack)) {
                throw new RuntimeException("Could not read back $label");
            }
            $context = hash_init('sha256');
            while (! feof($readBack)) {
                $chunk = fread($readBack, SQLStreamer::$buffer_size);
                if ($chunk === false) {
                    break;
                }
                hash_update($context, $chunk);
            }
            fclose($readBack);

            if ($stored !== $size || hash_final($context) !== $sha) {
                throw new RuntimeException("$label reads back differently from what was written");
            }
        } finally {
            fclose($temp);
        }
    }

    /**
     * Clear every file out of each upload directory the restore is
     * about to extract into. `.gitkeep` is preserved so the empty
     * directory still exists post-restore. Nested subdirectories are
     * left alone (Snipe-IT's upload dirs are flat per-resource today,
     * but this is defensive against future directory/subdir structures).
     *
     * Accepts the directory lists as parameters rather than reading
     * them from UploadDirectories directly so a test can hand in a
     * fake sandbox dir instead of touching the real repo tree.
     *
     * @param  array<int|string, string>  $publicDirs
     * @param  array<int|string, string>  $privateDirs
     */
    protected static function pruneUploadDirectories(array $publicDirs, array $privateDirs): void
    {
        $all = [];
        foreach ([$publicDirs, $privateDirs] as $set) {
            foreach ($set as $src => $dst) {
                $all[] = is_int($src) ? $dst : $dst;
            }
        }

        foreach (array_unique($all) as $dir) {
            if (! is_dir($dir)) {
                continue;
            }
            foreach (scandir($dir) ?: [] as $entry) {
                // Preserve both .gitkeep and .gitignore so the directory
                // still exists after a restore AND still carries its
                // ignore rules for git-installed deployments.
                if ($entry === '.' || $entry === '..' || $entry === '.gitkeep' || $entry === '.gitignore') {
                    continue;
                }
                $path = $dir.'/'.$entry;
                if (is_file($path)) {
                    @unlink($path);
                }
            }
        }
    }

    /**
     * Delete every file matching the given glob patterns before the
     * restore extracts its own copies on top. Covers the root-level
     * Settings branding files (Setting-*, logo.*, favicon.*, etc.)
     * that live outside the enumerated upload subdirectories.
     *
     * @param  list<string>  $patterns
     */
    protected static function pruneUploadFiles(array $patterns): void
    {
        foreach ($patterns as $pattern) {
            foreach (glob($pattern) ?: [] as $match) {
                if (is_file($match)) {
                    @unlink($match);
                }
            }
        }
    }
}
