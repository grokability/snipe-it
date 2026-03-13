<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Version extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'version:update {--branch=master} {--type=patch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $use_branch = $this->option('branch');
        $use_type = $this->option('type');
        $git_branch = trim(shell_exec('git rev-parse --abbrev-ref HEAD'));
        $build_version = trim(shell_exec('git rev-list --count '.$use_branch));
        $versionFile = 'config/version.php';
        $full_hash_version = str_replace("\n", '', shell_exec('git describe master --tags'));

        $version = explode('-', $full_hash_version);
        $app_version = $current_app_version = $version[0];
        $hash_version = (array_key_exists('2', $version)) ? $version[2] : '';
        $prerelease_version = '';

        $this->line('Branch is: '.$use_branch);
        $this->line('Type is: '.$use_type);
        $this->line('Current version is: '.$full_hash_version);

        if (count($version) == 3) {
            $this->line('This does not look like an alpha/beta release.');
        } else {
            if (array_key_exists('3', $version)) {
                $this->line('The current version looks like a beta release.');
                $prerelease_version = $version[1];
                $hash_version = $version[3];
            }
        }

        $app_version_raw = explode('.', $app_version);

        $maj = str_replace('v', '', $app_version_raw[0]);
        $min = $app_version_raw[1];
        $patch = '';

        // This is a major release that might not have a third .0
        if (array_key_exists(2, $app_version_raw)) {
            $patch = $app_version_raw[2];
        }

        if ($use_type == 'major') {
            $app_version = 'v'.($maj + 1).".$min.$patch";
        } elseif ($use_type == 'minor') {
            $app_version = 'v'."$maj.".($min + 1).".$patch";
        } elseif ($use_type == 'pre') {
            $pre_raw = str_replace('beta', '', $prerelease_version);
            $pre_raw = str_replace('alpha', '', $pre_raw);
            $pre_raw = str_ireplace('rc', '', $pre_raw);
            $pre_raw = $pre_raw++;
            $this->line('Setting the pre-release to '.$prerelease_version.'-'.$pre_raw);
            $app_version = 'v'."$maj.".($min + 1).".$patch";
        } elseif ($use_type == 'patch') {
            $app_version = 'v'."$maj.$min.".($patch + 1);
        // If nothing is passed, leave the version as it is, just increment the build
        } else {
            $app_version = 'v'."$maj.$min.".$patch;
        }

        // Determine if this tag already exists, or if this prior to a release
        $this->line('Running: git rev-parse master '.$current_app_version);
        // $pre_release = trim(shell_exec('git rev-parse '.$use_branch.' '.$current_app_version.' 2>&1 1> /dev/null'));

        if ($use_branch == 'develop') {
            $app_version = $app_version.'-pre';
        }

        $full_app_version = $app_version.' - build '.$build_version.'-'.$hash_version;

        $array = var_export(
            [
                'app_version' => $app_version,
                'full_app_version' => $full_app_version,
                'build_version' => $build_version,
                'prerelease_version' => $prerelease_version,
                'hash_version' => $hash_version,
                'full_hash' => $full_hash_version,
                'branch' => $git_branch, ],
            true
        );

        // Construct our file content
        $content = <<<CON
<?php
return $array;
CON;

        // And finally write the file and output the current version
        \File::put($versionFile, $content);
        $this->info('Setting NEW version: '.$full_app_version.' ('.$git_branch.')');
    }
}
