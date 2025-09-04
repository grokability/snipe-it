<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\CustomField;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class PaveIt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:pave  {--force : Skip the interactive yes/no prompt for confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the database tables, leaving all migrations, table structure, and the first user in place. (It is primarily a quick tool for developers.) If you want to destroy all tables as well, use php artisan db:wipe.';

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

        if (!$this->option('force')) {
            $confirmation = $this->confirm("\n****************************************************\nTHIS WILL DELETE ALL OF THE DATA IN YOUR DATABASE. \nThere is NO undo. This WILL destroy ALL of your data, \nINCLUDING ANY non-Snipe-IT tables you have in this database. \n****************************************************\n\nDo you wish to continue? No backsies! ");
            if (!$confirmation) {
                $this->error('ABORTING');
                exit(-1);
            }
        }

        // List all the tables in the database so we don't have to worry about missing some as the app grows
        $tables = Schema::getTables();
        $except_tables = [
            'oauth_access_tokens',
            'oauth_clients',
            'oauth_personal_access_clients',
            'migrations',
            'settings',
            'users',
            'telescope_entries',
            'telescope_entries_tags',
            'telescope_monitoring',
        ];

        // We only need to find out what these are so we can nuke these columns on the assets table.
        $custom_fields = CustomField::get();
        foreach ($custom_fields as $custom_field) {
            $this->info('DROP the '.$custom_field->db_column.' column from assets as well.');

            if (Schema::hasColumn('assets', $custom_field->db_column)) {
                Schema::table('assets', function ($table) use ($custom_field) {
                    $table->dropColumn($custom_field->db_column);
                });
            }
        }

        foreach ($tables as $table_obj) {
            $table = $table_obj['name'];
            if (in_array($table, $except_tables)) {
                $this->info($table. ' is SKIPPED.');
            } else {
                \DB::statement('truncate '.$table);
                $this->info($table. ' is TRUNCATED.');
            }
        }

        // Leave in the demo oauth keys so we don't have to reset them every day in the demos
        DB::statement('delete from oauth_clients WHERE id > 2');
        DB::statement('delete from oauth_access_tokens WHERE user_id > 2');
    
    }
}