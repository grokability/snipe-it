<?php

namespace App\Console\Commands;

use App\Services\RemoteUploadsException;
use App\Services\RemoteUploadsStager;
use Illuminate\Console\Command;
use Spatie\Backup\Commands\BackupCommand;
use Spatie\Backup\Config\Config;

class SystemBackup extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    // --force is accepted for parity with other Laravel commands that require
    // it (migrate, db:seed, etc.); it doesn't gate anything here but external
    // automations were passing it and crashing on the "option does not exist"
    // error.
    protected $signature = 'snipeit:backup {--filename=} {--force} {--with-remote-files : Also copy files that are stored on S3 into the backup} {--without-remote-files : Leave files that are stored on S3 out of the backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command creates a database dump and zips up all of the uploaded files in the upload directories.';

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ini_set('max_execution_time', config('backup.time_limit')); // 600 seconds = 10 minutes

        if ($this->option('with-remote-files') && $this->option('without-remote-files')) {
            $this->error('Use either --with-remote-files or --without-remote-files, not both.');

            return self::FAILURE;
        }

        $arguments = [];
        if ($this->option('filename')) {
            $filename = $this->option('filename');

            // Make sure the filename ends in .zip
            if (! ends_with($filename, '.zip')) {
                $filename = $filename.'.zip';
            }

            $arguments['--filename'] = $filename;
        }

        // The flags win over BACKUP_INCLUDE_REMOTE_FILES, which is what scheduled backups use.
        $includeRemote = $this->option('with-remote-files')
            ? true
            : ($this->option('without-remote-files') ? false : (bool) config('backup.include_remote_files'));

        $stager = new RemoteUploadsStager(RemoteUploadsStager::defaultBase());
        $include = config('backup.backup.source.files.include');

        try {
            if ($stager->needsStaging()) {
                if ($includeRemote) {
                    $this->info('Copying the files stored on S3 for the backup...');
                    $extra = $stager->stage()['root'];
                } else {
                    $this->warn('S3 files are not part of this backup. Use --with-remote-files to include them.');
                    $extra = $stager->markExcluded();
                }

                // Spatie reads this directory like any other include, so the copies end up in the zip.
                $this->useIncludes(array_merge($include, [$extra]));
            }

            // Pass the exit code on, so a failed backup is not reported as a success.
            return $this->call('backup:run', $arguments);
        } catch (RemoteUploadsException $e) {
            $this->error($e->getMessage());
            $this->error('The backup was not created.');

            return self::FAILURE;
        } finally {
            $this->useIncludes($include);
            $stager->cleanup();
        }
    }

    /**
     * Spatie builds its settings object once from the config and hands it to the backup:run command when
     * the console starts, so a changed include list only counts after that object is rebuilt and the
     * command is created again.
     *
     * @param  array<int, string>  $include
     */
    private function useIncludes(array $include): void
    {
        config(['backup.backup.source.files.include' => $include]);
        app()->forgetInstance(Config::class);

        $application = $this->getApplication();
        if ($application !== null && $application->has('backup:run') && $application->find('backup:run') instanceof BackupCommand) {
            $application->add(app(BackupCommand::class));
        }
    }
}
