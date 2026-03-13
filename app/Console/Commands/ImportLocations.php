<?php

namespace App\Console\Commands;

use App\Models\Location;
use Illuminate\Console\Command;
use League\Csv\Reader;

class ImportLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:import-locations {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import locations and their parents';

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
        if (! ini_get('auto_detect_line_endings')) {
            ini_set('auto_detect_line_endings', '1');
        }

        $filename = $this->argument('filename');
        $csv = Reader::createFromPath(storage_path('private_uploads/imports/').$filename, 'r');
        $this->info('Attempting to process: '.storage_path('private_uploads/imports/').$filename);
        $csv->setHeaderOffset(0); //because we don't want to insert the header
        $results = $csv->getRecords();

        // Import parent location names first if they don't exist
        foreach ($results as $parent_index => $parent_row) {
            if (array_key_exists('Parent Name', $parent_row)) {
                $parent_name = trim($parent_row['Parent Name']);
                if (array_key_exists('Name', $parent_row)) {
                    $this->info('- Parent: '.$parent_name.' in row as: '.trim($parent_row['Parent Name']));
                }

                // Save parent location name
                // This creates a sort of name-stub that we'll update later on in this script
                $parent_location = Location::firstOrCreate(['name' => $parent_name]);
                if (array_key_exists('Name', $parent_row)) {
                    $this->info('Parent for '.$parent_row['Name'].' is '.$parent_name.'. Attempting to save '.$parent_name.'.');
                }

                // Check if the record was updated or created.
                // This is mostly for clearer debugging.
                if ($parent_location->exists) {
                    $this->info('- Parent location '.$parent_name.' already exists.');
                } else {
                    $this->info('- Parent location '.$parent_name.' was created.');
                }
            } else {
                $this->info('- No Parent Name provided, so no parent location will be created.');
            }
        }

        $this->info('----- Parents Created.... backfilling additional details... --------');
        // Loop through ALL records and add/update them if there are additional fields
        // besides name
        foreach ($results as $index => $row) {
            if (array_key_exists('Parent Name', $row)) {
                $parent_name = trim($row['Parent Name']);
            } else {
                $parent_name = null;
            }

            // Set the location attributes to save
            if (array_key_exists('Name', $row)) {
                $location = Location::firstOrCreate(['name' => trim($row['Name'])]);
                $location->name = trim($row['Name']);
                $this->info('Checking location: '.$location->name);
            } else {
                $this->error('Location name is required and is missing from at least one row in this dataset. Check your CSV for extra trailing rows and try again.');

                return false;
            }
            if (array_key_exists('Currency', $row)) {
                $location->currency = trim($row['Currency']);
            }
            if (array_key_exists('Address 1', $row)) {
                $location->address = trim($row['Address 1']);
            }
            if (array_key_exists('Address 2', $row)) {
                $location->address2 = trim($row['Address 2']);
            }
            if (array_key_exists('City', $row)) {
                $location->city = trim($row['City']);
            }
            if (array_key_exists('State', $row)) {
                $location->state = trim($row['State']);
            }
            if (array_key_exists('Zip', $row)) {
                $location->zip = trim($row['Zip']);
            }
            if (array_key_exists('Country', $row)) {
                $location->country = trim($row['Country']);
            }
            if (array_key_exists('OU', $row)) {
                $location->ldap_ou = trim($row['OU']);
            }

            // If a parent name is provided, we created it earlier in the script,
            // so let's grab that ID
            if ($parent_name) {
                $this->info('-- Searching for Parent Name: '.$parent_name);
                $parent = Location::where('name', '=', $parent_name)->first();
                $location->parent_id = $parent->id;
                $this->info('Parent: '.$parent_name.' - ID: '.$parent->id);
            }

            // Make sure the more advanced (non-name) fields pass validation
            if (($location->isValid()) && ($location->save())) {

                // Check if the record was updated or created.
                // This is mostly for clearer debugging.
                if ($location->exists) {
                    $this->info('Location '.$location->name.' already exists. Updating...');
                } else {
                    $this->info('- Location '.$location->name.' was created. ');
                }

                // If there's a validation error, display that
            } else {
                $this->error('- Non-parent Location '.$location->name.' could not be created: '.$location->getErrors());
            }
        }
    }
}
