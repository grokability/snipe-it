<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Helper\ProgressIndicator;


/**
 * Class ObjectImportCommand
 */
class ObjectImportCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'snipeit:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Items from CSV';

    /**
     * The progress indicator instance.
     */
    protected ProgressIndicator $progressIndicator;

    /**
     * Logger instance with configurable log path
     */
    protected $logger;

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
        ini_set('max_execution_time', env('IMPORT_TIME_LIMIT', 600)); //600 seconds = 10 minutes
        ini_set('memory_limit', env('IMPORT_MEMORY_LIMIT', '500M'));

        $this->progressIndicator = new ProgressIndicator($this->output);

        $filename = $this->argument('filename');
        $class = ucfirst($this->option('item-type'));
        $classString = "App\\Importer\\{$class}Importer";
        $importer = new $classString($filename);
        $importer->setCallbacks([$this, 'log'], [$this, 'progress'], [$this, 'errorCallback'])
                 ->setCreatedBy($this->option('user_id'))
                 ->setUpdating($this->option('update'))
                 ->setShouldNotify($this->option('send-welcome'))
                 ->setUsernameFormat($this->option('username_format'));

        $this->logger = Log::build([
            'driver' => 'single',
            'path' => $this->option('logfile'),
          ]);

        $this->progressIndicator->start('======= Importing Items from '.$filename.' =========');

        $importer->import();

        $this->progressIndicator->finish('Import finished.');
    }

    public function errorCallback($item, $field, $error)
    {
        $this->output->write("\x0D\x1B[2K");

        $this->warn('Error: Item: '.$item->name.' failed validation: '.json_encode($error));
    }

    public function progress($importedItemsCount)
    {
        $this->progressIndicator->advance();
    }

    /**
     * Log a message to file, configurable by the --log-file parameter.
     * If a warning message is passed, we'll spit it to the console as well.
     *
     * @author Daniel Melzter
     * @since 3.0
     * @param string $string
     * @param string $level
     */
    public function log($string, $level = 'info')
    {
        if ($level === 'warning') {
            $this->logger->warning($string);
            $this->comment($string);
        } else {
            $this->logger->Info($string);
            if ($this->option('verbose')) {
                $this->comment($string);
            }
        }
    }

    /**
     * Get the console command arguments.
     *
     * @author Daniel Melzter
     * @since 3.0
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['filename', InputArgument::REQUIRED, 'File for the CSV import.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @author Daniel Melzter
     * @since 3.0
     * @return array
     */
    protected function getOptions()
    {
        return [
        ['email_format', null, InputOption::VALUE_REQUIRED, 'The format of the email addresses that should be generated. Options are firstname.lastname, firstname, filastname', null],
        ['username_format', null, InputOption::VALUE_REQUIRED, 'The format of the username that should be generated. Options are firstname.lastname, firstname, filastname, email', null],
        ['logfile', null, InputOption::VALUE_REQUIRED, 'The path to log output to.  storage/logs/importer.log by default', storage_path('logs/importer.log')],
        ['item-type', null, InputOption::VALUE_REQUIRED, 'Item Type To import.  Valid Options are Asset, Consumable, Accessory, License, or User', 'Asset'],
        ['web-importer', null, InputOption::VALUE_NONE, 'Internal: packages output for use with the web importer'],
        ['user_id', null, InputOption::VALUE_REQUIRED, 'ID of user creating items', 1],
        ['update', null, InputOption::VALUE_NONE, 'If a matching item is found, update item information'],
        ['send-welcome', null, InputOption::VALUE_NONE, 'Whether to send a welcome email to any new users that are created.'],
        ];
    }
}
